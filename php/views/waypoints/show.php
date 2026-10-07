<?php $pageTitle = ($waypoint['title'] ?? 'Waypoint') . ' · ' . site_text('site_title'); ?>
<section class="band mist">
  <div class="frame section">
  <p class="kicker"><a href="<?= e(url('/waypoints')) ?>">Waypoints</a></p>
  <h1><?= e($waypoint['title']) ?></h1>
  <?php $sitting = waypoint_sitting((string) $waypoint['slug']); ?>
  <?php if ($sitting !== ''): ?><p class="kicker">People sit with</p><p><?= e($sitting) ?></p><?php endif; ?>
  <?php if ($waypoint['cover_path']): ?>
    <img class="photo story-photo" src="<?= e(media($waypoint['cover_path'])) ?>" alt="">
  <?php endif; ?>
  <?= paragraphs((string) $waypoint['description']) ?>
  <?php if ($currentUser && empty($waypoint['archived'])): ?>
    <?php if ($joined): ?>
      <form method="post" action="<?= e(url('/waypoints/' . $waypoint['slug'] . '/leave')) ?>">
        <?= csrf_field() ?>
        <button class="quiet" type="submit">Leave this chair</button>
      </form>
    <?php else: ?>
      <form method="post" action="<?= e(url('/waypoints/' . $waypoint['slug'] . '/join')) ?>">
        <?= csrf_field() ?>
        <button type="submit">Sit with this waypoint</button>
      </form>
    <?php endif; ?>
  <?php elseif (!$currentUser): ?>
    <p><a href="<?= e(url('/join')) ?>">Join Go Solo</a> if you want to sit here.</p>
  <?php endif; ?>
  <h2>People sitting here</h2>
  <?php if (!$people): ?>
    <p class="soft">The chairs are empty. You can sit down.</p>
  <?php else: ?>
    <ul class="list">
      <?php foreach ($people as $person): ?>
        <li><a href="<?= e(url('/members/' . $person['id'])) ?>"><?= e($person['display_name'] ?: 'A member') ?></a></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  </div>
</section>
