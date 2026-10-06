<?php
$title = $title ?? copy('site_title');
$pageTitle = $pageTitle ?? $title;
$philosophy = copy('hero_philosophy');
$nav = [
    ['/about', copy('nav_about')],
    ['/seeds', copy('nav_seeds')],
    ['/out-there', copy('nav_out_there')],
    ['/campfire', copy('nav_campfire')],
    ['/waypoints', copy('nav_waypoints')],
    ['/reading', copy('nav_reading')],
    ['/contact', copy('nav_contact')],
];
$path = request_path();
$bg = hex_color(copy('color_background'), '#f7f5f2');
$ink = hex_color(copy('color_ink'), '#1a1a1a');
$soft = hex_color(copy('color_soft'), '#6b6b6b');
$sage = hex_color(copy('color_sage'), '#dce5de');
$clay = hex_color(copy('color_clay'), '#eed9d2');
$mist = hex_color(copy('color_mist'), '#eceeef');
$card = hex_color(copy('color_card'), '#eceeef');
$logoImage = copy('logo_image');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e(copy('tagline')) ?>">
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
  <a class="skip" href="#content">Skip to content</a>
  <header class="site-header">
    <a class="logo" href="<?= e(url('/')) ?>"><?php if ($logoImage !== ''): ?><img src="<?= e(media($logoImage)) ?>" alt=""><?php endif; ?><?= e(copy('logo_text')) ?></a>
    <nav class="nav" aria-label="Primary">
      <?php foreach ($nav as [$href, $label]): ?>
        <a href="<?= e(url($href)) ?>"<?= $path === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
      <?php if ($currentUser): ?>
        <a href="<?= e(url('/profile')) ?>">Profile</a>
        <?php if (in_array($currentUser['role'], ['admin', 'moderator'], true)): ?>
          <a href="<?= e(url('/steward')) ?>">Steward</a>
        <?php endif; ?>
        <form method="post" action="<?= e(url('/logout')) ?>" style="display:inline">
          <?= csrf_field() ?>
          <button class="quiet small" type="submit">Log out</button>
        </form>
      <?php else: ?>
        <a href="<?= e(url('/join')) ?>"><?= e(copy('nav_join')) ?></a>
        <a href="<?= e(url('/login')) ?>"><?= e(copy('nav_login')) ?></a>
      <?php endif; ?>
    </nav>
  </header>
  <main id="content">
    <?php if ($flashMessage): ?><div class="frame"><p class="flash"><?= e($flashMessage) ?></p></div><?php endif; ?>
    <?= $content ?>
  </main>
  <footer class="site-footer">
    <p class="philosophy" style="font-size:1.8rem"><?= e($philosophy) ?></p>
    <p><?= e(copy('footer_line')) ?></p>
    <p><a href="<?= e(url('/contact')) ?>"><?= e(copy('nav_contact')) ?></a> · <a href="mailto:<?= e(copy('founder_email')) ?>"><?= e(copy('founder_email')) ?></a></p>
  </footer>
</body>
</html>
