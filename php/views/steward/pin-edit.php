<?php
$pageTitle = site_text('pin_edit') . ' · Steward Desk';
$desk = match ($kind ?? '') {
    'campfire' => 'campfire',
    'story' => 'stories',
    default => 'waypoints',
};
include __DIR__ . '/open.php';
$body = (string) ($piece['body'] ?? '');
if (trim(strip_tags($body)) === '' && trim((string) ($piece['what_happened'] ?? '')) !== '') {
    $body = (string) $piece['what_happened'];
}
?>
<p class="kicker"><a href="<?= e(url($back)) ?>"><?= e(site_text('pin_back')) ?></a></p>
<h1><?= e(site_text('pin_edit')) ?></h1>
<p class="pin-mark"><span aria-hidden="true">📌</span> <?= e($kicker) ?></p>
<?php
  $pinSave = match ($kind) {
      'campfire' => '/steward/campfire/' . (int) $piece['id'] . '/edit',
      'story' => '/steward/stories/' . (int) $piece['id'] . '/edit',
      default => '/steward/waypoint-posts/' . (int) $piece['id'] . '/edit',
  };
?>
<form method="post" action="<?= e(url($pinSave)) ?>">
  <?= csrf_field() ?>
  <label><span><?= e(site_text('label_title')) ?></span>
    <input type="text" name="title" maxlength="160" required value="<?= e((string) $piece['title']) ?>">
  </label>
  <label><span><?= e(site_text('label_write')) ?></span>
    <textarea name="body" maxlength="20000" required><?= e($body) ?></textarea>
  </label>
  <p class="soft"><?= e(site_text('pin_edit_note')) ?></p>
  <button type="submit"><?= e(site_text('pin_edit_save')) ?></button>
</form>
<?php include __DIR__ . '/close.php'; ?>
