<?php $pageTitle = 'Waypoints · ' . copy('site_title'); ?>
<section class="band mist">
  <div class="frame section">
  <h1>Waypoints</h1>
  <p>Places to sit with people navigating a similar part of life. These are not interest groups.</p>
  <?php if (!$waypoints): ?>
    <p>Waypoints will sit here when they are ready.</p>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($waypoints as $waypoint): ?>
        <a class="card waypoint stretch" href="<?= e(url('/waypoints/' . $waypoint['slug'])) ?>">
          <?php if ($waypoint['cover_path']): ?>
            <img class="photo" src="<?= e(media($waypoint['cover_path'])) ?>" alt="">
          <?php endif; ?>
          <h2><?= e($waypoint['title']) ?></h2>
          <p><?= e(clip((string) $waypoint['description'], 220)) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  </div>
</section>
