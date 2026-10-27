<?php
$draftBody = (string) ($draftBody ?? '');
$hasDraft = trim($draftBody) !== '';
?>
<form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="form_key" value="<?= e((string) ($formKey ?? '')) ?>">
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
    include __DIR__ . '/editor.php';
  ?>
  <div class="row-actions">
    <button type="submit" name="action" value="send"><?= e(site_text('talk_reply_send')) ?></button>
    <button type="submit" name="action" value="draft"><?= e(site_text('talk_draft_save')) ?></button>
    <?php if ($hasDraft): ?>
      <button class="quiet" type="submit" name="action" value="delete_draft"><?= e(site_text('talk_draft_delete')) ?></button>
    <?php endif; ?>
  </div>
  <?php if (!user_is_verified()): ?>
    <p class="soft"><?= e(site_text('msg_verify_first')) ?> <a href="<?= e(url('/account')) ?>"><?= e(site_text('cta_account')) ?></a></p>
  <?php endif; ?>
</form>
