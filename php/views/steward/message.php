<?php $pageTitle = 'Message · Steward Desk'; $desk = 'messages'; include __DIR__ . '/open.php'; ?>
<h1><?= e($message['name']) ?></h1>
<p class="soft"><a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a> · <?= e($message['status']) ?> · <?= e(nice_date($message['created_at'])) ?></p>
<?= paragraphs((string) $message['body']) ?>
<div class="row-actions">
  <?php foreach (['unread' => 'Mark unread', 'replied' => 'Mark replied', 'archived' => 'Archive'] as $status => $label): ?>
    <form method="post" action="<?= e(url('/steward/messages/' . $message['id'])) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="<?= e($status) ?>">
      <button class="quiet small" type="submit"><?= e($label) ?></button>
    </form>
  <?php endforeach; ?>
  <form method="post" action="<?= e(url('/steward/messages/' . $message['id'])) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="delete">
    <button class="quiet small" type="submit">Delete</button>
  </form>
</div>
<?php include __DIR__ . '/close.php'; ?>
