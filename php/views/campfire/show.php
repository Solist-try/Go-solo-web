<?php $pageTitle = ($post['title'] ?? 'Campfire') . ' · ' . site_text('site_title'); ?>
<section class="frame section">
  <p class="kicker"><a href="<?= e(url('/campfire')) ?>">Campfire</a></p>
  <h1><?= e($post['title']) ?></h1>
  <p class="soft"><a href="<?= e(url('/members/' . $post['user_id'])) ?>"><?= e($post['display_name'] ?: 'A member') ?></a> · <?= e(nice_date($post['created_at'])) ?></p>
  <?php if (!empty($post['hidden'])): ?><p class="flash">This conversation is hidden from the house. You can still see it.</p><?php endif; ?>
  <?= render_writing((string) $post['body']) ?>
  <?php include __DIR__ . '/../partials/photos.php'; ?>
  <?php
    $canReply = $currentUser && empty($post['locked']);
    $notesHeading = 'Around the table';
    $notesEmpty = 'No one has pulled a chair closer yet.';
    $closedNote = !empty($post['locked']) ? 'This conversation is resting. New notes are closed.' : '';
    include __DIR__ . '/../partials/notes.php';
  ?>
  <?php if ($currentUser && (int) $currentUser['id'] !== (int) $post['user_id']): ?>
    <form method="post" action="<?= e(url('/campfire/' . $post['id'] . '/report')) ?>">
      <?= csrf_field() ?>
      <label><span>If something here is not right, tell the steward</span><textarea name="reason" maxlength="1000" required></textarea></label>
      <button class="quiet small" type="submit">Send a report</button>
    </form>
  <?php endif; ?>
</section>
