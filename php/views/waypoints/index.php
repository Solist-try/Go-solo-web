<?php $pageTitle = 'Waypoints · ' . site_text('site_title'); ?>
<section class="band mist">
  <div class="frame section">
  <h1>Waypoints</h1>
  <p>A chair with people in a similar part of life. You can sit down when you want.</p>
  <?php if (!$waypoints): ?>
    <p>The chairs will be here when they are ready.</p>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($waypoints as $waypoint): ?>
        <a class="card waypoint stretch" href="<?= e(url('/waypoints/' . $waypoint['slug'])) ?>">
          <?php if ($waypoint['cover_path']): ?>
            <img class="photo" src="<?= e(media($waypoint['cover_path'])) ?>" alt="">
          <?php endif; ?>
          <h2><?= e($waypoint['title']) ?></h2>
          <?php $sitting = waypoint_sitting((string) $waypoint['slug']); ?>
          <?php if ($sitting !== ''): ?><p class="kicker">People sit with</p><p><?= e($sitting) ?></p><?php endif; ?>
          <p><?= e(clip((string) $waypoint['description'], 220)) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  </div>
</section>
