<?php $pageTitle = 'Campfire · Steward Desk'; $desk = 'campfire'; include __DIR__ . '/open.php'; ?>
<h1>Campfire</h1>
<?php if (!$posts): ?><p>The campfire is waiting for its first conversation.</p><?php endif; ?>
<?php foreach ($posts as $post): ?>
  <article class="card">
    <h2><a href="<?= e(url('/campfire/' . $post['id'])) ?>"><?= e($post['title']) ?></a></h2>
    <p class="soft"><?= e($post['display_name'] ?: 'A member') ?> · <?= e(nice_date($post['created_at'])) ?><?php if ($post['hidden']): ?> · hidden<?php endif; ?><?php if ($post['locked']): ?> · resting<?php endif; ?></p>
    <div class="row-actions">
      <form method="post" action="<?= e(url('/steward/campfire/' . $post['id'])) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= $post['hidden'] ? 'show' : 'hide' ?>">
        <button class="quiet small" type="submit"><?= $post['hidden'] ? 'Show' : 'Hide' ?></button>
      </form>
      <form method="post" action="<?= e(url('/steward/campfire/' . $post['id'])) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= $post['locked'] ? 'unlock' : 'lock' ?>">
        <button class="quiet small" type="submit"><?= $post['locked'] ? 'Open notes' : 'Let it rest' ?></button>
      </form>
      <form method="post" action="<?= e(url('/steward/campfire/' . $post['id'])) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <button class="quiet small" type="submit">Delete</button>
      </form>
    </div>
  </article>
<?php endforeach; ?>
<h2>Notes</h2>
<?php foreach ($comments as $comment): ?>
  <article class="card mist">
    <p><?= e(clip((string) $comment['body'], 280)) ?></p>
    <p class="soft"><?= e($comment['display_name'] ?: 'A member') ?><?php if ($comment['hidden']): ?> · hidden<?php endif; ?></p>
    <div class="row-actions">
      <form method="post" action="<?= e(url('/steward/comments/' . $comment['id'])) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= $comment['hidden'] ? 'show' : 'hide' ?>">
        <button class="quiet small" type="submit"><?= $comment['hidden'] ? 'Show' : 'Hide' ?></button>
      </form>
      <form method="post" action="<?= e(url('/steward/comments/' . $comment['id'])) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <button class="quiet small" type="submit">Delete</button>
      </form>
    </div>
  </article>
<?php endforeach; ?>
<?php include __DIR__ . '/close.php'; ?>
