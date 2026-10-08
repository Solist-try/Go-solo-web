<?php $notesHeading = $notesHeading ?? 'Notes'; ?>
<h2><?= e($notesHeading) ?></h2>
<?php if (!$comments): ?><p class="soft"><?= e($notesEmpty ?? 'No one has added to this yet.') ?></p><?php endif; ?>
<div class="stack">
  <?php foreach ($comments as $comment): ?>
    <article class="card">
      <?php if (!empty($comment['hidden'])): ?><p class="soft">Hidden from the house.</p><?php endif; ?>
      <p class="soft"><a href="<?= e(url('/members/' . $comment['user_id'])) ?>"><?= e($comment['display_name'] ?: 'A member') ?></a> · <?= e(nice_date($comment['created_at'])) ?></p>
      <?= render_writing((string) $comment['body']) ?>
      <?php $images = $comment['images'] ?? []; include __DIR__ . '/photos.php'; ?>
      <?php if ($currentUser && (int) $currentUser['id'] === (int) $comment['user_id']): ?>
        <form method="post" action="<?= e(url('/comments/' . $comment['id'] . '/delete')) ?>">
          <?= csrf_field() ?>
          <button class="quiet small" type="submit">Remove this note</button>
        </form>
      <?php endif; ?>
      <?php if (is_steward()): ?>
        <form method="post" action="<?= e(url('/steward/comments/' . $comment['id'])) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= !empty($comment['hidden']) ? 'show' : 'hide' ?>">
          <button class="quiet small" type="submit"><?= !empty($comment['hidden']) ? 'Show this note' : 'Hide this note' ?></button>
        </form>
      <?php endif; ?>
      <?php if ($currentUser && (int) $currentUser['id'] !== (int) $comment['user_id']): ?>
        <form method="post" action="<?= e(url('/comments/' . $comment['id'] . '/report')) ?>">
          <?= csrf_field() ?>
          <label><span>Report this note</span><textarea name="reason" maxlength="1000" required></textarea></label>
          <button class="quiet small" type="submit">Send a report</button>
        </form>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</div>
<?php if (!empty($canReply) && !empty($editor)): ?>
  <?php include __DIR__ . '/editor.php'; ?>
<?php elseif (!empty($closedNote)): ?>
  <p class="soft"><?= e($closedNote) ?></p>
<?php endif; ?>
