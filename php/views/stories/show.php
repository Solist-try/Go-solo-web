<?php $pageTitle = ($story['title'] ?? site_text('out_there_title')) . ' · ' . site_text('site_title'); ?>
<section class="frame section">
  <p class="kicker"><a href="<?= e(url('/out-there')) ?>"><?= e(site_text('out_there_title')) ?></a></p>
  <h1><?= e($story['title']) ?></h1>
  <p class="soft"><a href="<?= e(url('/members/' . $story['user_id'])) ?>"><?= e($story['display_name'] ?: site_text('garden_member')) ?></a> · <?= e(nice_date($story['created_at'])) ?></p>
  <?php if (!empty($story['hidden'])): ?><p class="flash"><?= e(site_text('out_there_hidden')) ?></p><?php endif; ?>
  <?php if (!empty($story['image_path']) && empty($images)): ?>
    <img class="photo story-photo" src="<?= e(media($story['image_path'])) ?>" alt="">
  <?php endif; ?>
  <?php include __DIR__ . '/../partials/photos.php'; ?>
  <?php if (story_has_body($story)): ?>
    <?= render_writing((string) $story['body']) ?>
  <?php else: ?>
    <h2><?= e(site_text('out_there_did')) ?></h2>
    <?= paragraphs((string) $story['what_i_did']) ?>
    <?php if (trim((string) $story['expectations']) !== ''): ?>
      <h2><?= e(site_text('out_there_expected')) ?></h2>
      <?= paragraphs((string) $story['expectations']) ?>
    <?php endif; ?>
    <h2><?= e(site_text('out_there_happened')) ?></h2>
    <?= paragraphs((string) $story['what_happened']) ?>
    <?php if (trim((string) $story['would_do_again']) !== ''): ?>
      <h2><?= e(site_text('out_there_again')) ?></h2>
      <?= paragraphs((string) $story['would_do_again']) ?>
    <?php endif; ?>
  <?php endif; ?>
  <?php
    $canReply = (bool) $currentUser;
    $notesHeading = site_text('out_there_after');
    $notesEmpty = site_text('empty_story_replies');
    include __DIR__ . '/../partials/notes.php';
  ?>
  <?php if ($currentUser && (int) $currentUser['id'] !== (int) $story['user_id']): ?>
    <form method="post" action="<?= e(url('/out-there/' . $story['id'] . '/report')) ?>">
      <?= csrf_field() ?>
      <label><span><?= e(site_text('label_report')) ?></span><textarea name="reason" maxlength="1000" required></textarea></label>
      <button class="quiet small" type="submit"><?= e(site_text('cta_send_report')) ?></button>
    </form>
  <?php endif; ?>
</section>
