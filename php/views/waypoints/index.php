<?php $pageTitle = site_text('waypoints_title') . ' · ' . site_text('site_title'); ?>
<section class="band mist">
  <div class="frame section">
  <h1><?= e(site_text('waypoints_title')) ?></h1>
  <p><?= e(site_text('waypoints_intro')) ?></p>
  <?php if (!$waypoints): ?>
    <p><?= e(site_text('empty_waypoints')) ?></p>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($waypoints as $waypoint): ?>
        <a class="card waypoint stretch" href="<?= e(url('/waypoints/' . $waypoint['slug'])) ?>">
          <?php if ($waypoint['cover_path']): ?>
            <img class="photo" src="<?= e(media($waypoint['cover_path'])) ?>" alt="">
          <?php endif; ?>
          <h2><?= e($waypoint['title']) ?></h2>
          <?php $sitting = waypoint_sitting((string) $waypoint['slug'], $waypoint); ?>
          <?php if ($sitting !== ''): ?><p class="kicker"><?= e(site_text('waypoints_sit_label')) ?></p><p><?= e($sitting) ?></p><?php endif; ?>
          <p><?= e(clip((string) $waypoint['description'], 220)) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  </div>
</section>
