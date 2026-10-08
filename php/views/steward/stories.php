<?php $pageTitle = 'Out There · Steward Desk'; $desk = 'stories'; include __DIR__ . '/open.php'; ?>
<h1>Out There</h1>
<?php if (function_exists('pin_ready') && !pin_ready()): ?><p class="soft"><?= e(site_text('pin_import')) ?></p><?php endif; ?>
<?php if (!$stories): ?><p>No stories brought back yet. The public page keeps the room until a real one arrives.</p><?php endif; ?>
<?php
  $pinTaken = false;
  foreach ($stories as $candidate) {
      if (!empty($candidate['pinned'])) {
          $pinTaken = true;
          break;
      }
  }
?>
<?php foreach ($stories as $story): ?>
  <article class="card">
    <h2><a href="<?= e(url('/out-there/' . $story['id'])) ?>"><?= e($story['title']) ?></a></h2>
    <p class="soft"><?= e($story['display_name'] ?: 'A member') ?> · <?= e(nice_date($story['created_at'])) ?><?php if (!empty($story['pinned'])): ?> · <?= e(site_text('pin_by')) ?><?php endif; ?><?php if ($story['hidden']): ?> · hidden<?php endif; ?></p>
    <div class="row-actions">
      <?php if (function_exists('pin_ready') && pin_ready() && empty($story['hidden'])): ?>
        <form method="post" action="<?= e(url('/steward/stories/' . $story['id'])) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= !empty($story['pinned']) ? 'unpin' : 'pin' ?>">
          <button class="quiet small" type="submit"><?= e(!empty($story['pinned']) ? site_text('pin_unpin') : ($pinTaken ? site_text('pin_replace') : site_text('pin_pin'))) ?></button>
        </form>
      <?php endif; ?>
      <?php if (function_exists('pin_ready') && pin_ready()): ?>
        <a class="button quiet small" href="<?= e(url('/steward/stories/' . $story['id'] . '/edit')) ?>"><?= e(site_text('pin_edit')) ?></a>
      <?php endif; ?>
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
