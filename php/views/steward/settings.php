<?php $pageTitle = 'Settings · Steward Desk'; $desk = 'settings'; include __DIR__ . '/open.php'; ?>
<h1>Settings</h1>
<form method="post" action="<?= e(url('/steward/settings')) ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <?php foreach (settings_text_keys() as $key => $label): ?>
    <?php $value = site_text($key); ?>
    <label><span><?= e($label) ?></span>
      <?php if (long_setting($key, $value)): ?>
        <textarea name="<?= e($key) ?>" maxlength="4000"><?= e($value) ?></textarea>
      <?php else: ?>
        <input type="text" name="<?= e($key) ?>" maxlength="190" value="<?= e($value) ?>">
      <?php endif; ?>
    </label>
  <?php endforeach; ?>
  <p class="soft">The reset note can use {name} and {link}.</p>
  <h2>Colours</h2>
  <p class="soft">Use a colour like #f7f5f2.</p>
  <?php foreach (color_keys() as $key => $label): ?>
    <label><span><?= e($label) ?></span><input type="text" name="<?= e($key) ?>" maxlength="7" value="<?= e(site_text($key)) ?>"></label>
  <?php endforeach; ?>
  <h2>Photographs</h2>
  <?php foreach (image_keys() as $key => $label): ?>
    <label><span><?= e($label) ?> path</span><input type="text" name="<?= e($key) ?>" maxlength="255" value="<?= e(site_text($key)) ?>"></label>
    <label><span>Upload a replacement</span><input type="file" name="file_<?= e($key) ?>" accept="image/jpeg,image/png,image/webp,image/gif"></label>
    <?php if (site_text($key) !== ''): ?><p><img class="photo" style="max-width:16rem" src="<?= e(media(site_text($key))) ?>" alt=""></p><?php endif; ?>
    <?php $altKey = $key . '_alt'; if (array_key_exists($altKey, copy_defaults())): ?>
      <label><span>Description of that photograph</span><input type="text" name="<?= e($altKey) ?>" maxlength="255" value="<?= e(site_text($altKey)) ?>"></label>
    <?php endif; ?>
  <?php endforeach; ?>
  <button type="submit">Save settings</button>
</form>
<?php include __DIR__ . '/close.php'; ?>