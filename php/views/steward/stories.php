<?php $pageTitle = 'Out There · Steward Desk'; $desk = 'stories'; include __DIR__ . '/open.php'; ?>
<h1>Out There</h1>
<?php if (!$stories): ?><p>No stories yet. The public page explains the room until a real one arrives.</p><?php endif; ?>
<?php foreach ($stories as $story): ?>
  <article class="card">
    <h2><a href="<?= e(url('/out-there/' . $story['id'])) ?>"><?= e($story['title']) ?></a></h2>
    <p class="soft"><?= e($story['display_name'] ?: 'A member') ?> · <?= e(nice_date($story['created_at'])) ?><?php if ($story['hidden']): ?> · hidden<?php endif; ?></p>
    <div class="row-actions">
      <form method="post" action="<?= e(url('/steward/stories/' . $story['id'])) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= $story['hidden'] ? 'show' : 'hide' ?>">
        <button class="quiet small" type="submit"><?= $story['hidden'] ? 'Show' : 'Hide' ?></button>
      </form>
      <form method="post" action="<?= e(url('/steward/stories/' . $story['id'])) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <button class="quiet small" type="submit">Delete</button>
      </form>
    </div>
  </article>
<?php endforeach; ?>
<?php include __DIR__ . '/close.php'; ?>
