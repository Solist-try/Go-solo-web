<?php
$desk = $desk ?? '';
$links = [
    'overview' => ['/steward', 'Overview'],
    'members' => ['/steward/members', 'Members'],
    'seeds' => ['/steward/seeds', 'Seeds'],
    'same' => ['/steward/same', 'SAME'],
    'skills' => ['/steward/skills', 'Skill Swap'],
    'stories' => ['/steward/out-there', 'Out There'],
    'campfire' => ['/steward/campfire', 'Campfire'],
    'waypoints' => ['/steward/waypoints', 'Waypoints'],
    'reading' => ['/steward/reading', 'Reading Room'],
    'messages' => ['/steward/messages', 'Contact Messages'],
    'reports' => ['/steward/reports', 'Reports'],
];
if (is_admin()) {
    $links['content'] = ['/steward/content', 'Content'];
    $links['settings'] = ['/steward/settings', 'Settings'];
}
?>
<section class="frame section desk">
  <nav class="desk-nav" aria-label="Steward Desk">
    <?php foreach ($links as $key => [$href, $label]): ?>
      <a href="<?= e(url($href)) ?>"<?= $desk === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
  <div>
