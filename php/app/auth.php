<?php

declare(strict_types=1);

function current_user(): ?array
{
    static $loaded = false;
    static $user = null;
    if ($loaded) {
        return $user;
    }
    $loaded = true;
    $id = (int) ($_SESSION['user_id'] ?? 0);
    if ($id <= 0) {
        return null;
    }
    $user = one(
        'SELECT u.id, u.email, u.role, u.status, u.created_at, u.last_seen_at,
                p.display_name, p.bio, p.location, p.avatar_path, p.contact_frequency,
                p.check_in_style, p.show_location
         FROM users u
         LEFT JOIN profiles p ON p.user_id = u.id
         WHERE u.id = ?',
        [$id]
    );
    if (!$user || $user['status'] !== 'active') {
        unset($_SESSION['user_id']);
        $user = null;
    }
    return $user;
}

function touch_last_seen(): void
{
    $user = current_user();
    if (!$user) {
        return;
    }
    $seen = strtotime((string) ($user['last_seen_at'] ?? '')) ?: 0;
    if ($seen > time() - 900) {
        return;
    }
    exec_sql('UPDATE users SET last_seen_at = NOW() WHERE id = ?', [(int) $user['id']]);
}

function require_user(): array
{
    $user = current_user();
    if (!$user) {
        flash(site_text('msg_come_in'));
        redirect('/login?next=' . rawurlencode(request_path()));
    }
    return $user;
}

function require_steward(): array
{
    $user = require_user();
    if (!in_array($user['role'], ['admin', 'moderator'], true)) {
        http_response_code(403);
        exit('This desk is kept by the steward.');
    }
    return $user;
}

function is_steward(?array $user = null): bool
{
    $user = $user ?? current_user();
    return $user && in_array($user['role'], ['admin', 'moderator'], true);
}

function is_admin(?array $user = null): bool
{
    $user = $user ?? current_user();
    return $user && $user['role'] === 'admin';
}

function require_admin(): array
{
    $user = require_steward();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit('Only the founder can change that.');
    }
    return $user;
}

function attempt_login(string $email, string $password): ?string
{
    $fails = (int) ($_SESSION['login_fails'] ?? 0);
    if ($fails >= 8) {
        return 'Too many tries. Wait a little, then try again.';
    }
    $row = one('SELECT id, password_hash, status FROM users WHERE email = ?', [strtolower(trim($email))]);
    if (!$row || !password_verify($password, (string) $row['password_hash'])) {
        $_SESSION['login_fails'] = $fails + 1;
        return 'That email and password do not match.';
    }
    if ($row['status'] === 'suspended') {
        return 'This account is resting. Write through Contact if that is a surprise.';
    }
    if ($row['status'] === 'banned') {
        return 'This account is closed.';
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $row['id'];
    unset($_SESSION['login_fails']);
    exec_sql('UPDATE users SET last_seen_at = NOW() WHERE id = ?', [(int) $row['id']]);
    return null;
}

function send_reset(string $email): void
{
    $user = one(
        'SELECT u.id, p.display_name FROM users u LEFT JOIN profiles p ON p.user_id = u.id WHERE u.email = ? AND u.status = ?',
        [strtolower(trim($email)), 'active']
    );
    if (!$user) {
        return;
    }
    $token = bin2hex(random_bytes(32));
    exec_sql('DELETE FROM password_resets WHERE user_id = ?', [(int) $user['id']]);
    exec_sql(
        'INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 2 HOUR), NOW())',
        [(int) $user['id'], hash('sha256', $token)]
    );
    $link = absolute_url('/reset?token=' . $token);
    $name = $user['display_name'] ?: 'there';
    $subject = str_replace(["\r", "\n"], '', setting('email_reset_subject', 'A way back into Go Solo'));
    $body = setting(
        'email_reset_body',
        "Hello {name},\n\nHere is a link to choose a new password. It lasts for two hours.\n\n{link}\n\nIf you did not ask for this, you can ignore it."
    );
    $body = str_replace(['{name}', '{link}'], [$name, $link], $body);
    $from = str_replace(["\r", "\n"], '', setting('founder_email', 'marge@gosolo.co.network'));
    $headers = "From: Go Solo <{$from}>\r\nContent-Type: text/plain; charset=UTF-8";
    @mail($email, $subject, $body, $headers);
}

function absolute_url(string $path): string
{
    $configured = str_replace(["\r", "\n"], '', trim((string) ($GLOBALS['config']['site_url'] ?? '')));
    if (preg_match('#^https://#', $configured)) {
        return rtrim($configured, '/') . url($path);
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . url($path);
}

function support_choices(): array
{
    return ['Accountability', 'Medical Buddy', 'Practical Life', 'Connection', 'Starting Over', 'Confidence', 'Similar Path'];
}

function frequency_choices(): array
{
    return ['Daily', 'Several times per week', 'Weekly', 'Bi-weekly', 'Monthly', 'As needed'];
}

function style_choices(): array
{
    return ['Messages', 'Voice notes', 'Video calls', 'Email', 'Any format'];
}

function user_supports(int $userId): array
{
    return array_column(q('SELECT preference FROM support_preferences WHERE user_id = ? ORDER BY preference', [$userId]), 'preference');
}

function user_titles(string $table, int $userId): array
{
    if (!in_array($table, ['help_requests', 'help_offers', 'growing_seeds'], true)) {
        return [];
    }
    if ($table === 'growing_seeds') {
        return q('SELECT id, title, status, looking_for_support, created_at FROM growing_seeds WHERE user_id = ? ORDER BY created_at DESC', [$userId]);
    }
    return array_column(q("SELECT title FROM {$table} WHERE user_id = ? ORDER BY id", [$userId]), 'title');
}

function replace_titles(string $table, int $userId, array $titles): void
{
    if (!in_array($table, ['help_requests', 'help_offers'], true)) {
        return;
    }
    exec_sql("DELETE FROM {$table} WHERE user_id = ?", [$userId]);
    $seen = [];
    foreach ($titles as $title) {
        $title = trim((string) $title);
        $key = strtolower($title);
        if ($title === '' || isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        exec_sql("INSERT INTO {$table} (user_id, title) VALUES (?, ?)", [$userId, clip($title, 120)]);
    }
}

function replace_supports(int $userId, array $choices): void
{
    $allowed = array_flip(support_choices());
    exec_sql('DELETE FROM support_preferences WHERE user_id = ?', [$userId]);
    foreach ($choices as $choice) {
        if (!isset($allowed[$choice])) {
            continue;
        }
        exec_sql('INSERT INTO support_preferences (user_id, preference) VALUES (?, ?)', [$userId, $choice]);
    }
}
