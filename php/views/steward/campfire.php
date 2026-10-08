<?php $pageTitle = 'Campfire · Steward Desk'; $desk = 'campfire'; include __DIR__ . '/open.php'; ?>
<h1>Campfire</h1>
<?php if (function_exists('pin_ready') && !pin_ready()): ?><p class="soft"><?= e(site_text('pin_import')) ?></p><?php endif; ?>
<?php if (!$posts): ?><p>The campfire is waiting for its first conversation.</p><?php endif; ?>
<?php
  $pinTaken = false;
  foreach ($posts as $candidate) {
      if (!empty($candidate['pinned'])) {
          $pinTaken = true;
          break;
      }
  }
?>
<?php foreach ($posts as $post): ?>
  <article class="card">
    <h2><a href="<?= e(url('/campfire/' . $post['id'])) ?>"><?= e($post['title']) ?></a></h2>
    <p class="soft"><?= e($post['display_name'] ?: 'A member') ?> · <?= e(nice_date($post['created_at'])) ?><?php if (!empty($post['pinned'])): ?> · <?= e(site_text('pin_welcome')) ?><?php endif; ?><?php if ($post['hidden']): ?> · hidden<?php endif; ?><?php if ($post['locked']): ?> · resting<?php endif; ?></p>
    <div class="row-actions">
      <?php if (function_exists('pin_ready') && pin_ready() && empty($post['hidden'])): ?>
        <form method="post" action="<?= e(url('/steward/campfire/' . $post['id'])) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= !empty($post['pinned']) ? 'unpin' : 'pin' ?>">
          <button class="quiet small" type="submit"><?= e(!empty($post['pinned']) ? site_text('pin_unpin') : ($pinTaken ? site_text('pin_replace') : site_text('pin_pin'))) ?></button>
        </form>
      <?php endif; ?>
      <?php if (function_exists('pin_ready') && pin_ready()): ?>
        <a class="button quiet small" href="<?= e(url('/steward/campfire/' . $post['id'] . '/edit')) ?>"><?= e(site_text('pin_edit')) ?></a>
      <?php endif; ?>
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
