<?php $pageTitle = 'Content · Steward Desk'; $desk = 'content'; include __DIR__ . '/open.php'; ?>
<h1>Content</h1>
<p class="soft">Leave a box empty and save it to restore the original line.</p>
<form method="post" action="<?= e(url('/steward/content')) ?>">
  <?= csrf_field() ?>
  <?php foreach (content_groups() as $group => $fields): ?>
    <h2><?= e($group) ?></h2>
    <?php foreach ($fields as $key => $label): ?>
      <?php $value = copy($key); ?>
      <label><span><?= e($label) ?></span>
        <?php if (long_setting($key, $value)): ?>
          <textarea name="<?= e($key) ?>" maxlength="8000"><?= e($value) ?></textarea>
        <?php else: ?>
          <input type="text" name="<?= e($key) ?>" maxlength="500" value="<?= e($value) ?>">
        <?php endif; ?>
      </label>
    <?php endforeach; ?>
  <?php endforeach; ?>
  <h2>How Go Solo works</h2>
  <?php
  $steps = json_decode(copy('how_steps'), true);
  if (!is_array($steps)) {
      $steps = [];
  }
  for ($i = 0; $i < 4; $i++):
      $step = $steps[$i] ?? ['name' => '', 'body' => '', 'href' => ''];
  ?>
    <label><span>Step <?= e((string) ($i + 1)) ?> name</span><input type="text" name="how_<?= e((string) ($i + 1)) ?>_name" maxlength="80" value="<?= e((string) ($step['name'] ?? '')) ?>"></label>
    <label><span>Step <?= e((string) ($i + 1)) ?> text</span><input type="text" name="how_<?= e((string) ($i + 1)) ?>_body" maxlength="300" value="<?= e((string) ($step['body'] ?? '')) ?>"></label>
    <label><span>Step <?= e((string) ($i + 1)) ?> link</span><input type="text" name="how_<?= e((string) ($i + 1)) ?>_href" maxlength="120" value="<?= e((string) ($step['href'] ?? '')) ?>"></label>
  <?php endfor; ?>
  <button type="submit">Save the words</button>
</form>
<?php include __DIR__ . '/close.php'; ?>
