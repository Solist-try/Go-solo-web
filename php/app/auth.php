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
    $verified = table_has_column('users', 'email_verified_at') ? 'u.email_verified_at,' : '';
    $user = one(
        "SELECT u.id, u.email, u.role, u.status, u.created_at, u.last_seen_at, {$verified}
                p.display_name, p.bio, p.location, p.avatar_path, p.contact_frequency,
                p.check_in_style, p.show_location
         FROM users u
         LEFT JOIN profiles p ON p.user_id = u.id
         WHERE u.id = ?",
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

function email_verify_ready(): bool
{
    static $ready = null;
    if ($ready === null) {
        $ready = table_has_column('users', 'email_verified_at') && table_has_column('email_tokens', 'purpose');
    }
    return $ready;
}

function user_is_verified(?array $user = null): bool
{
    if (!email_verify_ready()) {
        return true;
    }
    $user = $user ?? current_user();
    if (!$user) {
        return false;
    }
    return trim((string) ($user['email_verified_at'] ?? '')) !== '';
}

function require_verified(): array
{
    $user = require_user();
    if (!user_is_verified($user)) {
        flash(site_text('msg_verify_first'));
        redirect('/account');
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

function email_token_ok(string $token): bool
{
    return preg_match('/^[a-f0-9]{64}$/', $token) === 1;
}

function email_letter(string $subject, string $body, string $link): array
{
    $subject = str_replace(["\r", "\n"], '', $subject);
    if ($link !== '' && !str_contains($body, $link)) {
        $body = rtrim($body) . "\n\n" . $link;
    }
    return [$subject, $body];
}

function email_house_when(?DateTimeImmutable $moment = null): array
{
    $zone = new DateTimeZone('Europe/Tallinn');
    $moment = ($moment ?? new DateTimeImmutable('now', $zone))->setTimezone($zone);
    return [
        'date' => $moment->format('j M Y'),
        'time' => $moment->format('H:i') . ' Europe/Tallinn',
    ];
}

function email_from(): string
{
    $from = str_replace(["\r", "\n"], '', trim(site_text('founder_email')));
    if (!valid_email($from)) {
        return 'marge@gosolo.co.network';
    }
    return $from;
}

function email_support_contact(): string
{
    $from = str_replace(["\r", "\n"], '', trim(site_text('founder_email')));
    if (valid_email($from)) {
        return $from;
    }
    return absolute_url('/contact');
}

function email_send(string $to, string $subject, string $body): bool
{
    $to = str_replace(["\r", "\n"], '', $to);
    if (!valid_email($to)) {
        return false;
    }
    $subject = str_replace(["\r", "\n"], '', $subject);
    $from = email_from();
    $headers = "From: Go Solo <{$from}>\r\nContent-Type: text/plain; charset=UTF-8";
    return @mail($to, $subject, $body, $headers);
}

function email_flash_result(string $result, string $sentKey): void
{
    if ($result === 'sent') {
        flash(site_text($sentKey));
        return;
    }
    if ($result === 'wait') {
        flash(site_text('msg_verify_wait'));
        return;
    }
    flash(site_text('msg_verify_mail'));
}

function email_issue(int $userId, string $purpose, string $subjectKey, string $bodyKey, bool $force = false): string
{
    if (!email_verify_ready() || !in_array($purpose, ['verify', 'delete'], true)) {
        return 'unavailable';
    }
    $interval = $purpose === 'delete' ? '24 HOUR' : '7 DAY';
    $path = $purpose === 'delete' ? '/account/delete/confirm?token=' : '/verify?token=';
    try {
        $user = one('SELECT id, email FROM users WHERE id = ? AND status = ?', [$userId, 'active']);
        if (!$user || !valid_email((string) $user['email'])) {
            return 'failed';
        }
        if (!$force) {
            $recent = one(
                'SELECT id FROM email_tokens WHERE user_id = ? AND purpose = ? AND used_at IS NULL AND created_at > DATE_SUB(NOW(), INTERVAL 2 MINUTE)',
                [$userId, $purpose]
            );
            if ($recent) {
                return 'wait';
            }
        }
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $emailHash = hash('sha256', strtolower((string) $user['email']));
        exec_sql('DELETE FROM email_tokens WHERE user_id = ? AND purpose = ? AND used_at IS NULL', [$userId, $purpose]);
        if (table_has_column('email_tokens', 'email_hash')) {
            exec_sql(
                "INSERT INTO email_tokens (user_id, purpose, token_hash, email_hash, expires_at, created_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL {$interval}), NOW())",
                [$userId, $purpose, $hash, $emailHash]
            );
        } else {
            exec_sql(
                "INSERT INTO email_tokens (user_id, purpose, token_hash, expires_at, created_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL {$interval}), NOW())",
                [$userId, $purpose, $hash]
            );
        }
        $link = absolute_url($path . $token);
        $body = site_line($bodyKey, ['link' => $link]);
        [$subject, $letter] = email_letter(site_text($subjectKey), $body, $link);
        if (!email_send((string) $user['email'], $subject, $letter)) {
            exec_sql('DELETE FROM email_tokens WHERE user_id = ? AND purpose = ? AND token_hash = ? AND used_at IS NULL', [$userId, $purpose, $hash]);
            return 'failed';
        }
        return 'sent';
    } catch (Throwable $e) {
        return 'failed';
    }
}

function email_find_open(string $token, string $purpose): ?array
{
    if (!email_verify_ready() || !email_token_ok($token) || !in_array($purpose, ['verify', 'delete'], true)) {
        return null;
    }
    $hashColumn = table_has_column('email_tokens', 'email_hash') ? ', t.email_hash, u.email' : '';
    $row = one(
        "SELECT t.id, t.user_id{$hashColumn}
         FROM email_tokens t
         JOIN users u ON u.id = t.user_id
         WHERE t.token_hash = ? AND t.purpose = ? AND t.used_at IS NULL AND t.expires_at > NOW()",
        [hash('sha256', $token), $purpose]
    );
    if (!$row) {
        return null;
    }
    $stored = (string) ($row['email_hash'] ?? '');
    if ($stored !== '' && !hash_equals($stored, hash('sha256', strtolower((string) $row['email'])))) {
        return null;
    }
    return ['id' => (int) $row['id'], 'user_id' => (int) $row['user_id']];
}

function email_lock_open(string $token, string $purpose): ?array
{
    if (!email_verify_ready() || !email_token_ok($token) || !in_array($purpose, ['verify', 'delete'], true)) {
        return null;
    }
    $hashColumn = table_has_column('email_tokens', 'email_hash') ? ', email_hash' : '';
    $tokenRow = one(
        "SELECT id, user_id{$hashColumn} FROM email_tokens WHERE token_hash = ? AND purpose = ? AND used_at IS NULL AND expires_at > NOW() FOR UPDATE",
        [hash('sha256', $token), $purpose]
    );
    if (!$tokenRow) {
        return null;
    }
    $user = one('SELECT id, email, role FROM users WHERE id = ? FOR UPDATE', [(int) $tokenRow['user_id']]);
    if (!$user) {
        return null;
    }
    $emailHash = (string) ($tokenRow['email_hash'] ?? '');
    $matches = $emailHash === '' || hash_equals($emailHash, hash('sha256', strtolower((string) $user['email'])));
    return [
        'id' => (int) $tokenRow['id'],
        'user_id' => (int) $user['id'],
        'email' => (string) $user['email'],
        'role' => (string) $user['role'],
        'matches' => $matches,
    ];
}

function email_mark_used(int $id): bool
{
    $stmt = db()->prepare('UPDATE email_tokens SET used_at = NOW() WHERE id = ? AND used_at IS NULL');
    $stmt->execute([$id]);
    return $stmt->rowCount() === 1;
}

function email_finish_verify(string $token): ?int
{
    if (!email_verify_ready() || !email_token_ok($token)) {
        return null;
    }
    $db = db();
    try {
        $db->beginTransaction();
        $row = email_lock_open($token, 'verify');
        if (!$row || !email_mark_used((int) $row['id'])) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return null;
        }
        if (!$row['matches']) {
            $db->commit();
            return null;
        }
        exec_sql(
            'UPDATE users SET email_verified_at = COALESCE(email_verified_at, NOW()) WHERE id = ?',
            [(int) $row['user_id']]
        );
        $db->commit();
        return (int) $row['user_id'];
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return null;
    }
}

function email_finish_delete(string $token): array
{
    $fail = ['ok' => false, 'reason' => 'invalid', 'email' => '', 'user_id' => 0];
    if (!email_verify_ready() || !email_token_ok($token)) {
        return $fail;
    }
    $db = db();
    try {
        $db->beginTransaction();
        $row = email_lock_open($token, 'delete');
        if (!$row) {
            $db->rollBack();
            return $fail;
        }
        if (!$row['matches']) {
            if (!email_mark_used((int) $row['id'])) {
                $db->rollBack();
                return $fail;
            }
            $db->commit();
            return $fail;
        }
        if ($row['role'] === 'admin') {
            $others = count_of("SELECT COUNT(*) AS n FROM users WHERE role = 'admin' AND id <> ?", [(int) $row['user_id']]);
            if ($others < 1) {
                $db->rollBack();
                return ['ok' => false, 'reason' => 'steward', 'email' => '', 'user_id' => (int) $row['user_id']];
            }
        }
        if (!email_mark_used((int) $row['id'])) {
            $db->rollBack();
            return $fail;
        }
        $email = $row['email'];
        $userId = (int) $row['user_id'];
        exec_sql('DELETE FROM users WHERE id = ?', [$userId]);
        $db->commit();
        return ['ok' => true, 'reason' => '', 'email' => $email, 'user_id' => $userId];
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return ['ok' => false, 'reason' => 'held', 'email' => '', 'user_id' => 0];
    }
}

function email_send_deleted(string $email): void
{
    $when = email_house_when();
    $body = site_line('email_deleted_body', [
        'date' => $when['date'],
        'time' => $when['time'],
        'contact' => email_support_contact(),
    ]);
    [$subject, $letter] = email_letter(site_text('email_deleted_subject'), $body, '');
    email_send($email, $subject, $letter);
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
        $extra = table_has_column('growing_seeds', 'grown_visibility') ? ', grown_visibility, reflection' : '';
        return q("SELECT id, title, status, looking_for_support, created_at{$extra} FROM growing_seeds WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
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
