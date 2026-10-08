<?php $pageTitle = ($post['title'] ?? 'Discussion') . ' · ' . site_text('site_title'); ?>
<section class="frame section">
  <p class="kicker"><a href="<?= e(url('/waypoints/' . $waypoint['slug'])) ?>"><?= e($waypoint['title']) ?></a></p>
  <h1><?= e($post['title']) ?></h1>
  <p class="soft"><a href="<?= e(url('/members/' . $post['user_id'])) ?>"><?= e($post['display_name'] ?: 'A member') ?></a> · <?= e(nice_date($post['created_at'])) ?></p>
  <?php if (!empty($post['hidden'])): ?><p class="flash">This discussion is hidden from the house. You can still see it.</p><?php endif; ?>
  <?= render_writing((string) $post['body']) ?>
  <?php include __DIR__ . '/../partials/photos.php'; ?>
  <?php if ($currentUser && ((int) $currentUser['id'] === (int) $post['user_id'] || is_steward())): ?>
    <form method="post" action="<?= e(url('/waypoints/' . $waypoint['slug'] . '/discussions/' . $post['id'] . '/delete')) ?>">
      <?= csrf_field() ?>
      <button class="quiet small" type="submit">Remove this discussion</button>
    </form>
  <?php endif; ?>
  <?php
    $notesHeading = 'In the chair';
    $notesEmpty = 'No one has added to this yet.';
    $closedNote = !empty($post['locked']) ? 'This discussion is resting. New notes are closed.' : '';
    include __DIR__ . '/../partials/notes.php';
  ?>
</section>
