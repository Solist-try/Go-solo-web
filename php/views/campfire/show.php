<?php $pageTitle = ($post['title'] ?? site_text('campfire_title')) . ' · ' . site_text('site_title'); ?>
<section class="frame section">
  <p class="kicker"><a href="<?= e(url('/campfire')) ?>"><?= e(site_text('campfire_title')) ?></a></p>
  <h1><?= e($post['title']) ?></h1>
  <?php $pinRow = $post; $pinType = 'campfire'; $pinBack = '/campfire/' . (int) $post['id']; $pinKicker = 'pin_welcome'; $pinHide = 'pin_hide_welcome'; include __DIR__ . '/../partials/pin-status.php'; ?>
  <p class="soft"><a href="<?= e(url('/members/' . $post['user_id'])) ?>"><?= e($post['display_name'] ?: site_text('garden_member')) ?></a> · <?= e(nice_date($post['created_at'])) ?></p>
  <?php if ($currentUser && (int) $currentUser['id'] !== (int) $post['user_id'] && talk_exchange('campfire', (int) $post['id'], (int) $currentUser['id'], (int) $post['user_id'])): ?>
    <?php $talkOffer = talk_offer($currentUser, (int) $post['user_id'], true, '/campfire/' . $post['id'], ['type' => 'campfire', 'kind' => 'post', 'id' => (int) $post['id'], 'person' => (int) $post['user_id']], site_text('talk_hello')); include __DIR__ . '/../partials/talk-offer.php'; ?>
  <?php endif; ?>
  <?php $talkPost = ['type' => 'campfire', 'id' => (int) $post['id'], 'author' => (int) $post['user_id'], 'back' => '/campfire/' . $post['id']]; ?>
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
      <?php include __DIR__ . '/../partials/life-report.php'; ?>
      <button class="quiet small" type="submit"><?= e(site_text('cta_send_report')) ?></button>
    </form>
  <?php endif; ?>
</section>
