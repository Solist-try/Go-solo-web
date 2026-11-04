<?php
$pageTitle = 'Message · Steward Desk';
$desk = 'messages';
include __DIR__ . '/open.php';
$sent = !empty($replies);
$subject = trim((string) ($message['subject'] ?? ''));
?>
<h1><?= e($message['name']) ?></h1>
<p class="soft"><a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a> · <?= e(contact_status_label((string) $message['status'], $sent)) ?> · <?= e(nice_date($message['created_at'])) ?></p>
<?php if ($subject !== ''): ?>
  <p><?= e($subject) ?></p>
<?php endif; ?>
<h2><?= e(site_text('contact_original')) ?></h2>
<?= paragraphs((string) $message['body']) ?>
<?php foreach ($replies as $reply): ?>
  <h2><?= e(site_text('contact_reply_sent')) ?></h2>
  <?php if (trim((string) $reply['subject']) !== ''): ?><p><?= e((string) $reply['subject']) ?></p><?php endif; ?>
  <?= paragraphs((string) $reply['body']) ?>
  <p class="soft"><?= e(site_text('contact_reply_date')) ?> <?= e(nice_stamp((string) $reply['sent_at'])) ?><?php if (trim((string) ($reply['display_name'] ?? '')) !== ''): ?> · <?= e((string) $reply['display_name']) ?><?php endif; ?></p>
<?php endforeach; ?>
<?php if (!empty($replyReady)): ?>
  <h2 id="reply"><?= e(site_text('contact_reply')) ?></h2>
  <form method="post" action="<?= e(url('/steward/messages/' . $message['id'])) ?>">
    <?= csrf_field() ?>
    <label><span><?= e(site_text('contact_reply_subject')) ?></span><input type="text" name="subject" maxlength="180" value="<?= e($replySubject) ?>"></label>
    <label><span><?= e(site_text('contact_reply_body')) ?></span><textarea class="reply-box" name="body" maxlength="8000"><?= e($replyBody) ?></textarea></label>
    <div class="row-actions">
      <button type="submit" name="action" value="send"><?= e(site_text('contact_reply_send')) ?></button>
      <button type="submit" name="action" value="draft"><?= e(site_text('contact_reply_draft')) ?></button>
      <a class="button quiet" href="<?= e(url('/steward/messages')) ?>"><?= e(site_text('contact_reply_cancel')) ?></a>
      <?php if (!empty($draft)): ?>
        <button class="quiet" type="submit" name="action" value="delete_draft"><?= e(site_text('contact_reply_delete')) ?></button>
      <?php endif; ?>
    </div>
  </form>
<?php else: ?>
  <p class="soft"><?= e(site_text('contact_reply_waiting')) ?></p>
<?php endif; ?>
<div class="row-actions">
  <?php foreach (['unread' => 'Mark unread', 'archived' => 'Archive'] as $status => $label): ?>
    <form method="post" action="<?= e(url('/steward/messages/' . $message['id'])) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="<?= e($status) ?>">
      <button class="quiet small" type="submit"><?= e($label) ?></button>
    </form>
  <?php endforeach; ?>
  <?php if (!$sent): ?>
    <form method="post" action="<?= e(url('/steward/messages/' . $message['id'])) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="elsewhere">
      <button class="quiet small" type="submit"><?= e(site_text('contact_elsewhere')) ?></button>
    </form>
  <?php endif; ?>
  <form method="post" action="<?= e(url('/steward/messages/' . $message['id'])) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="delete">
    <button class="quiet small" type="submit">Delete</button>
  </form>
</div>
<?php include __DIR__ . '/close.php'; ?>
