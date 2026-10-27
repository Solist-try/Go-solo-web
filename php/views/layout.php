<?php
$title = $title ?? site_text('site_title');
$pageTitle = $pageTitle ?? $title;
$nav = [
    ['/about', site_text('nav_about')],
    ['/seeds', site_text('nav_seeds')],
    ['/out-there', site_text('nav_out_there')],
    ['/campfire', site_text('nav_campfire')],
    ['/waypoints', site_text('nav_waypoints')],
    ['/reading', site_text('nav_reading')],
    ['/contact', site_text('nav_contact')],
];
$path = request_path();
$bg = hex_color(site_text('color_background'), '#f7f5f2');
$ink = hex_color(site_text('color_ink'), '#1a1a1a');
$soft = hex_color(site_text('color_soft'), '#6b6b6b');
$sage = hex_color(site_text('color_sage'), '#dce5de');
$clay = hex_color(site_text('color_clay'), '#eed9d2');
$mist = hex_color(site_text('color_mist'), '#eceeef');
$card = hex_color(site_text('color_card'), '#eceeef');
$logoImage = site_text('logo_image');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e(site_text('tagline')) ?>">
  <link rel="icon" href="<?= e(url('/favicon.ico')) ?>" sizes="any">
  <link rel="icon" type="image/svg+xml" href="<?= e(url('/assets/images/favicon.svg')) ?>">
  <link rel="apple-touch-icon" href="<?= e(url('/assets/images/apple-touch-icon.png')) ?>">
  <link rel="stylesheet" href="<?= e(url('/assets/css/site.css')) ?>">
  <style>
    :root {
      --bg: <?= e($bg) ?>;
      --ink: <?= e($ink) ?>;
      --soft: <?= e($soft) ?>;
      --sage: <?= e($sage) ?>;
      --clay: <?= e($clay) ?>;
      --mist: <?= e($mist) ?>;
      --card: <?= e($card) ?>;
    }
  </style>
</head>
<body>
  <a class="skip" href="#content"><?= e(site_text('skip_content')) ?></a>
  <header class="site-header">
    <a class="logo" href="<?= e(url('/')) ?>"><?php if ($logoImage !== ''): ?><img src="<?= e(media($logoImage)) ?>" alt=""><?php endif; ?><?= e(site_text('logo_text')) ?></a>
    <nav class="nav" aria-label="Primary">
      <?php foreach ($nav as [$href, $label]): ?>
        <a href="<?= e(url($href)) ?>"<?= $path === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
      <?php if ($currentUser): ?>
        <?php $unreadTalk = function_exists('talk_unread_total') ? talk_unread_total((int) $currentUser['id']) : 0; ?>
        <?php $talkHere = $path === '/conversations' || str_starts_with($path, '/conversations/'); ?>
        <a href="<?= e(url('/conversations')) ?>"<?= $talkHere ? ' aria-current="page"' : '' ?>><?= e(site_text('nav_conversations')) ?><?php if ($unreadTalk > 0): ?> <span class="unread-count"><?= e(site_text('talk_unread')) ?> <?= e((string) $unreadTalk) ?></span><?php endif; ?></a>
        <a href="<?= e(url('/profile')) ?>"<?= $path === '/profile' ? ' aria-current="page"' : '' ?>><?= e(site_text('nav_profile')) ?></a>
        <?php if (in_array($currentUser['role'], ['admin', 'moderator'], true)): ?>
          <a href="<?= e(url('/steward')) ?>"><?= e(site_text('nav_steward')) ?></a>
        <?php endif; ?>
        <form method="post" action="<?= e(url('/logout')) ?>" style="display:inline">
          <?= csrf_field() ?>
          <button class="quiet small" type="submit"><?= e(site_text('cta_logout')) ?></button>
        </form>
      <?php else: ?>
        <a href="<?= e(url('/join')) ?>"><?= e(site_text('nav_join')) ?></a>
        <a href="<?= e(url('/login')) ?>"><?= e(site_text('nav_login')) ?></a>
      <?php endif; ?>
    </nav>
  </header>
  <main id="content">
    <?php if ($flashMessage): ?><div class="frame"><p class="flash" role="status"><?= e($flashMessage) ?></p></div><?php endif; ?>
    <?php $quietNote = (!empty($currentUser) && function_exists('quiet_note_take')) ? quiet_note_take((int) $currentUser['id']) : ''; ?>
    <?php if ($quietNote !== ''): ?>
      <?php include __DIR__ . '/partials/quiet-note.php'; ?>
    <?php endif; ?>
    <?php if ($currentUser && !user_is_verified($currentUser)): ?>
      <div class="frame">
        <div class="verify-note">
          <p class="verify-title"><?= e(site_text('verify_notice_title')) ?></p>
          <?php foreach (site_lines('verify_notice') as $line): ?><p><?= e($line) ?></p><?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
    <?= $content ?>
  </main>
  <footer class="site-footer">
    <p><?= e(site_text('footer_line')) ?></p>
    <?php if (site_text('footer_note') !== ''): ?><p><?= e(site_text('footer_note')) ?></p><?php endif; ?>
    <?php if (site_text('policy_privacy_label') !== '' || site_text('policy_terms_label') !== ''): ?>
      <p>
        <?php if (site_text('policy_privacy_label') !== ''): ?><a href="<?= e(url('/privacy')) ?>"><?= e(site_text('policy_privacy_label')) ?></a><?php endif; ?>
        <?php if (site_text('policy_privacy_label') !== '' && site_text('policy_terms_label') !== ''): ?> · <?php endif; ?>
        <?php if (site_text('policy_terms_label') !== ''): ?><a href="<?= e(url('/terms')) ?>"><?= e(site_text('policy_terms_label')) ?></a><?php endif; ?>
      </p>
    <?php endif; ?>
  </footer>
</body>
</html>
