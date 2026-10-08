<?php $pageTitle = 'Waypoints · Steward Desk'; $desk = 'waypoints'; include __DIR__ . '/open.php'; ?>
<h1>Waypoints</h1>
<p><a class="button small" href="<?= e(url('/steward/waypoints/new')) ?>">Create Waypoint</a></p>
<ul class="list">
  <?php foreach ($waypoints as $waypoint): ?>
    <li>
      <a href="<?= e(url('/steward/waypoints/' . $waypoint['id'])) ?>"><?= e($waypoint['title']) ?></a>
      <?php if ($waypoint['archived']): ?><span class="soft">archived</span><?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>
<?php include __DIR__ . '/close.php'; ?>
