<?php
$pageTitle = site_text('talk_heading') . ' · ' . site_text('site_title');
$reasons = preg_split("/\r\n|\n|\r/", (string) ($subject['note'] ?? '')) ?: [];
?>
<section class="frame section narrow">
  <p class="kicker"><a href="<?= e(url($fields['back'])) ?>"><?= e(site_text('talk_active')) ?></a></p>
  <h1><?= e(site_text('talk_heading')) ?></h1>
  <p><?= e(site_line('talk_with', ['name' => $otherName])) ?></p>
  <p class="kicker"><?= e(talk_context_heading((string) $fields['type'])) ?></p>
  <?php if (trim((string) $subject['label']) !== ''): ?>
    <p><?= e((string) $subject['label']) ?></p>
  <?php endif; ?>
  <?php if ($fields['type'] === 'introduction'): ?>
    <p><?= e(site_text('talk_because')) ?></p>
    <ul>
      <?php foreach ($reasons as $reason): ?>
        <?php if (trim($reason) !== ''): ?><li><?= e(trim($reason)) ?></li><?php endif; ?>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <form method="post" action="<?= e(url('/conversations')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="type" value="<?= e((string) $fields['type']) ?>">
    <input type="hidden" name="kind" value="<?= e((string) $fields['kind']) ?>">
    <input type="hidden" name="id" value="<?= e((string) $fields['id']) ?>">
    <input type="hidden" name="person" value="<?= e((string) $fields['person']) ?>">
    <input type="hidden" name="back" value="<?= e((string) $fields['back']) ?>">
    <label>
      <span><?= e(site_text('talk_opening')) ?></span>
      <textarea name="note" maxlength="1000" required></textarea>
    </label>
    <p class="soft"><?= e(site_text('talk_opening_hint')) ?></p>
    <button type="submit"><?= e(site_text('talk_send_request')) ?></button>
  </form>
</section>
