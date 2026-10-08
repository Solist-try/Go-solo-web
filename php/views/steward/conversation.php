<?php $pageTitle = 'Conversation · Steward Desk'; $desk = 'conversations'; include __DIR__ . '/open.php'; ?>
<p><a href="<?= e(url('/steward/conversations')) ?>">Private conversations</a></p>
<h1><?= e((string) $conversation['context_label'] ?: 'Private conversation') ?></h1>
<p class="soft"><?= e((string) $conversation['context_type']) ?> · <?= e(nice_date($conversation['created_at'])) ?><?php if ((int) $conversation['closed'] === 1): ?> · resting<?php endif; ?></p>
<h2>Participants</h2>
<ul class="list">
  <?php foreach ($people as $person): ?>
    <li>
      <a href="<?= e(url('/steward/members/' . $person['id'])) ?>"><?= e($person['display_name'] ?: 'A member') ?></a>
      <form method="post" action="<?= e(url('/steward/conversations/' . $conversation['id'])) ?>" class="inline">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="hold">
        <input type="hidden" name="person_id" value="<?= e((string) $person['id']) ?>">
        <button class="quiet small" type="submit">Rest their conversations</button>
      </form>
      <form method="post" action="<?= e(url('/steward/conversations/' . $conversation['id'])) ?>" class="inline">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="release">
        <input type="hidden" name="person_id" value="<?= e((string) $person['id']) ?>">
        <button class="quiet small" type="submit">Restore their conversations</button>
      </form>
    </li>
  <?php endforeach; ?>
</ul>
<div class="row-actions">
  <?php if ((int) $conversation['closed'] === 1): ?>
    <form method="post" action="<?= e(url('/steward/conversations/' . $conversation['id'])) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="open">
      <button class="quiet small" type="submit">Open this conversation again</button>
    </form>
  <?php else: ?>
    <form method="post" action="<?= e(url('/steward/conversations/' . $conversation['id'])) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="close">
      <button class="quiet small" type="submit">Let this conversation rest</button>
    </form>
  <?php endif; ?>
</div>
<?php if (!$looking): ?>
  <p class="soft">The notes are not open on this desk. A report is what permits a look, and the look is recorded.</p>
  <?php if (!empty($reported)): ?>
    <form method="post" action="<?= e(url('/steward/conversations/' . $conversation['id'])) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="look">
      <button type="submit">Look, because a report is waiting</button>
    </form>
  <?php endif; ?>
<?php else: ?>
  <h2>Notes</h2>
  <?php foreach ($messages as $message): ?>
    <article class="card">
      <p class="soft"><?= e($message['display_name'] ?: 'A member') ?> · <?= e(nice_date($message['created_at'])) ?></p>
      <?php if (trim((string) $message['body']) !== ''): ?><?= render_writing((string) $message['body']) ?><?php endif; ?>
      <?php $images = $message['images'] ?? []; include __DIR__ . '/../partials/photos.php'; ?>
    </article>
  <?php endforeach; ?>
<?php endif; ?>
<?php include __DIR__ . '/close.php'; ?>
