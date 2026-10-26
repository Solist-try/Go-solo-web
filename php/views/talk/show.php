<?php
$declined = (string) ($conversation['status'] ?? '') === 'declined';
$status = (string) ($conversation['status'] ?? '');
$incomingRequest = !empty($canDecline) && $status === 'requested';
$waiting = !$incomingRequest && $status === 'requested';
$life = $life ?? ['ready' => false];
$notNow = !empty($notNow);
if ($incomingRequest) {
    $backHref = $notNow ? '/profile#later' : '/profile#requests';
    $backLabel = $notNow ? 'talk_not_now' : 'talk_requests';
} elseif ($waiting) {
    $backHref = '/profile';
    $backLabel = 'talk_waiting';
} else {
    $backHref = '/profile#conversations';
    $backLabel = 'talk_active';
}
$heading = $incomingRequest || $waiting ? 'talk_request_heading' : 'talk_heading';
$pageTitle = site_text($heading) . ' · ' . site_text('site_title');
$label = trim((string) ($conversation['context_label'] ?? ''));
$showLife = $status === 'open' && !empty($life['ready']);
?>
<section class="frame section narrow">
  <p class="kicker"><a href="<?= e(url($backHref)) ?>"><?= e(site_text($backLabel)) ?></a></p>
  <h1><?= e(site_text($heading)) ?></h1>
  <?php if ($incomingRequest): ?>
    <?php include __DIR__ . '/../partials/talk-request.php'; ?>
  <?php endif; ?>
  <?php if (!$incomingRequest && !$declined && $label !== ''): ?>
    <p class="soft"><?= e($label) ?></p>
  <?php endif; ?>
  <?php if (!empty($pauseLine) && !$incomingRequest): ?>
    <p class="soft"><?= e($pauseLine) ?></p>
  <?php endif; ?>
  <?php if (!$incomingRequest && !$declined && !empty($messages)): ?>
    <div class="stack">
      <?php foreach ($messages as $message): ?>
        <article class="card">
          <p class="soft"><a href="<?= e(url('/members/' . $message['user_id'])) ?>"><?= e($message['display_name'] ?: site_text('garden_member')) ?></a> · <?= e(nice_date($message['created_at'])) ?></p>
          <?php if (trim((string) $message['body']) !== ''): ?><?= render_writing((string) $message['body']) ?><?php endif; ?>
          <?php $images = $message['images'] ?? []; include __DIR__ . '/../partials/photos.php'; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($canReply)): ?>
    <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
      <?= csrf_field() ?>
      <label>
        <span><?= e(site_text('talk_body')) ?></span>
        <textarea name="body" maxlength="4000" required></textarea>
      </label>
      <button type="submit"><?= e(site_text('talk_send')) ?></button>
    </form>
  <?php endif; ?>
  <?php if ($showLife || !empty($canBlock)): ?>
    <h2><?= e(site_text('talk_settings')) ?></h2>
    <div class="row-actions">
      <?php if ($showLife): ?>
        <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= !empty($life['archived']) ? 'restore' : 'archive' ?>">
          <button class="quiet small" type="submit"><?= e(site_text(!empty($life['archived']) ? 'life_talk_restore' : 'life_talk_archive')) ?></button>
        </form>
        <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= !empty($life['muted']) ? 'unmute' : 'mute' ?>">
          <button class="quiet small" type="submit"><?= e(site_text(!empty($life['muted']) ? 'life_talk_unmute' : 'life_talk_mute')) ?></button>
        </form>
        <?php if (empty($life['closed'])): ?>
          <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="close">
            <button class="quiet small" type="submit"><?= e(site_text('life_talk_close')) ?></button>
          </form>
        <?php endif; ?>
      <?php endif; ?>
      <?php if (!empty($canBlock)): ?>
        <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="block">
          <button class="quiet small" type="submit"><?= e(site_text('talk_block')) ?></button>
        </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>
