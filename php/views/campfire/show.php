<?php $pageTitle = ($post['title'] ?? site_text('campfire_title')) . ' · ' . site_text('site_title'); ?>
<section class="frame section">
  <p class="kicker"><a href="<?= e(url('/campfire')) ?>"><?= e(site_text('campfire_title')) ?></a></p>
  <h1><?= e($post['title']) ?></h1>
  <p class="soft"><a href="<?= e(url('/members/' . $post['user_id'])) ?>"><?= e($post['display_name'] ?: site_text('garden_member')) ?></a> · <?= e(nice_date($post['created_at'])) ?></p>
  <?php if (!empty($post['hidden'])): ?><p class="flash"><?= e(site_text('campfire_hidden')) ?></p><?php endif; ?>
  <?= render_writing((string) $post['body']) ?>
  <?php include __DIR__ . '/../partials/photos.php'; ?>
  <?php
    $canReply = $currentUser && empty($post['locked']);
    $notesHeading = site_text('campfire_around');
    $notesEmpty = site_text('empty_campfire_replies');
    $closedNote = !empty($post['locked']) ? site_text('campfire_resting') : '';
    include __DIR__ . '/../partials/notes.php';
  ?>
  <?php if ($currentUser && (int) $currentUser['id'] !== (int) $post['user_id']): ?>
    <form method="post" action="<?= e(url('/campfire/' . $post['id'] . '/report')) ?>">
      <?= csrf_field() ?>
      <label><span><?= e(site_text('label_report')) ?></span><textarea name="reason" maxlength="1000" required></textarea></label>
      <button class="quiet small" type="submit"><?= e(site_text('cta_send_report')) ?></button>
    </form>
  <?php endif; ?>
</section>
