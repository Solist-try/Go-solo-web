<?php
$pageTitle = site_text('talk_start') . ' · ' . site_text('site_title');
$reasons = preg_split("/\r\n|\n|\r/", (string) ($subject['note'] ?? '')) ?: [];
$draftBody = (string) ($draftBody ?? '');
?>
<section class="frame section talk-sheet">
  <p class="kicker"><a href="<?= e(url($fields['back'])) ?>"><?= e(site_text('talk_active')) ?></a></p>
  <h1><?= e(site_text('talk_start')) ?></h1>
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
    <input type="hidden" name="form_key" value="<?= e((string) ($formKey ?? '')) ?>">
    <input type="hidden" name="type" value="<?= e((string) $fields['type']) ?>">
    <input type="hidden" name="kind" value="<?= e((string) $fields['kind']) ?>">
    <input type="hidden" name="id" value="<?= e((string) $fields['id']) ?>">
    <input type="hidden" name="person" value="<?= e((string) $fields['person']) ?>">
    <input type="hidden" name="back" value="<?= e((string) $fields['back']) ?>">
    <?php
      $editor = [
          'mode' => 'compact',
          'letter' => true,
          'embedded' => true,
          'body' => $draftBody,
          'body_label' => site_text('talk_write'),
          'body_name' => 'body',
          'help' => site_text('talk_compose_note'),
      ];
      include __DIR__ . '/../partials/editor.php';
    ?>
    <div class="row-actions">
      <button type="submit" name="action" value="send"><?= e(site_text('talk_reply_send')) ?></button>
      <button type="submit" name="action" value="draft"><?= e(site_text('talk_draft_save')) ?></button>
      <?php if (trim($draftBody) !== ''): ?>
        <button class="quiet" type="submit" name="action" value="delete_draft"><?= e(site_text('talk_draft_delete')) ?></button>
      <?php endif; ?>
    </div>
  </form>
</section>
