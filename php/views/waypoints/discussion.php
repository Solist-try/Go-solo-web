<?php $pageTitle = ($post['title'] ?? site_text('waypoints_start_title')) . ' · ' . site_text('site_title'); ?>
<section class="frame section">
  <p class="kicker"><a href="<?= e(url('/waypoints/' . $waypoint['slug'])) ?>"><?= e($waypoint['title']) ?></a></p>
  <h1><?= e($post['title']) ?></h1>
  <p class="soft"><a href="<?= e(url('/members/' . $post['user_id'])) ?>"><?= e($post['display_name'] ?: site_text('garden_member')) ?></a> · <?= e(nice_date($post['created_at'])) ?></p>
  <?php if (!empty($post['hidden'])): ?><p class="flash"><?= e(site_text('waypoints_hidden')) ?></p><?php endif; ?>
  <?= render_writing((string) $post['body']) ?>
  <?php include __DIR__ . '/../partials/photos.php'; ?>
  <?php if ($currentUser && ((int) $currentUser['id'] === (int) $post['user_id'] || is_steward())): ?>
    <form method="post" action="<?= e(url('/waypoints/' . $waypoint['slug'] . '/discussions/' . $post['id'] . '/delete')) ?>">
      <?= csrf_field() ?>
      <button class="quiet small" type="submit"><?= e(site_text('cta_remove_discussion')) ?></button>
    </form>
  <?php endif; ?>
  <?php
    $notesHeading = site_text('waypoints_in_chair');
    $notesEmpty = site_text('empty_discussion_replies');
    $closedNote = !empty($post['locked']) ? site_text('waypoints_resting') : '';
    include __DIR__ . '/../partials/notes.php';
  ?>
</section>
