<?php $pageTitle = ($post['title'] ?? site_text('waypoints_start_title')) . ' · ' . site_text('site_title'); ?>
<section class="frame section">
  <p class="kicker"><a href="<?= e(url('/waypoints/' . $waypoint['slug'])) ?>"><?= e($waypoint['title']) ?></a></p>
  <h1><?= e($post['title']) ?></h1>
  <?php $pinRow = $post; $pinType = 'waypoint'; $pinBack = '/waypoints/' . $waypoint['slug'] . '/discussions/' . (int) $post['id']; $pinKicker = 'pin_start'; $pinHide = 'pin_hide'; include __DIR__ . '/../partials/pin-status.php'; ?>
  <p class="soft"><a href="<?= e(url('/members/' . $post['user_id'])) ?>"><?= e($post['display_name'] ?: site_text('garden_member')) ?></a> · <?= e(nice_date($post['created_at'])) ?></p>
  <?php if ($currentUser && (int) $currentUser['id'] !== (int) $post['user_id'] && talk_exchange('waypoint', (int) $post['id'], (int) $currentUser['id'], (int) $post['user_id'])): ?>
    <?php $talkOffer = talk_offer($currentUser, (int) $post['user_id'], true, '/waypoints/' . $waypoint['slug'] . '/discussions/' . $post['id'], ['type' => 'waypoint', 'kind' => 'post', 'id' => (int) $post['id'], 'person' => (int) $post['user_id']], site_text('talk_hello')); include __DIR__ . '/../partials/talk-offer.php'; ?>
  <?php endif; ?>
  <?php $talkPost = ['type' => 'waypoint', 'id' => (int) $post['id'], 'author' => (int) $post['user_id'], 'back' => '/waypoints/' . $waypoint['slug'] . '/discussions/' . $post['id']]; ?>
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
