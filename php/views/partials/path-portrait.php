<?php
$portrait = $portrait ?? [];
$sections = [
    'growing' => 'path_growing',
    'waypoint' => 'path_waypoint',
    'looking' => 'path_looking',
    'offers' => 'path_offers',
];
$hasFacts = false;
foreach (['growing', 'waypoint', 'looking', 'offers', 'stories'] as $key) {
    if (!empty($portrait[$key])) {
        $hasFacts = true;
    }
}
if (!empty($portrait['campfire']) || !empty($portrait['chairs'])) {
    $hasFacts = true;
}
?>
<aside class="path-portrait">
  <p class="kicker"><?= e(site_text('path_question')) ?></p>
  <?php foreach ($sections as $key => $label): ?>
    <?php if (empty($portrait[$key])) continue; ?>
    <h2><?= e(site_text($label)) ?></h2>
    <ul class="chips">
      <?php foreach ($portrait[$key] as $item): ?>
        <li><?= e((string) $item) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endforeach; ?>
  <?php if (trim((string) ($portrait['pace'] ?? '')) !== ''): ?>
    <h2><?= e(site_text('path_pace')) ?></h2>
    <p><?= e((string) $portrait['pace']) ?></p>
  <?php endif; ?>
  <?php if (!empty($portrait['stories']) || !empty($portrait['campfire']) || !empty($portrait['chairs'])): ?>
    <h2><?= e(site_text('path_shows')) ?></h2>
    <?php if (!empty($portrait['stories'])): ?>
      <ul class="list">
        <?php foreach ($portrait['stories'] as $story): ?>
          <li><a href="<?= e(url('/out-there/' . $story['id'])) ?>"><?= e((string) $story['title']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php if (!empty($portrait['campfire'])): ?>
      <p><?= e(site_line('path_show_campfire', ['count' => (string) $portrait['campfire']])) ?></p>
    <?php endif; ?>
    <?php if (!empty($portrait['chairs'])): ?>
      <p><?= e(site_line('path_show_chair', ['count' => (string) $portrait['chairs']])) ?></p>
    <?php endif; ?>
  <?php endif; ?>
  <?php if (!$hasFacts): ?>
    <p class="soft"><?= e(site_text('path_empty')) ?></p>
  <?php endif; ?>
</aside>
