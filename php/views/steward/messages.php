<?php $pageTitle = 'Contact Messages · Steward Desk'; $desk = 'messages'; include __DIR__ . '/open.php'; ?>
<h1>Contact Messages</h1>
<?php if (!$messages): ?><p>The inbox is quiet.</p><?php endif; ?>
<ul class="list">
  <?php foreach ($messages as $message): ?>
    <li>
      <a href="<?= e(url('/steward/messages/' . $message['id'])) ?>"><?= e($message['name']) ?></a>
      <span class="soft"><?= e($message['status']) ?> · <?= e(nice_date($message['created_at'])) ?></span>
    </li>
  <?php endforeach; ?>
</ul>
<?php include __DIR__ . '/close.php'; ?>
