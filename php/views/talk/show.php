<?php
$status = (string) ($conversation['status'] ?? '');
$incoming = !empty($canDecline) && $status === 'requested';
$waiting = !$incoming && $status === 'requested';
$memberClosed = (int) ($conversation['member_closed'] ?? 0) === 1;
$resting = (int) ($conversation['closed'] ?? 0) === 1;
$closedView = $status === 'declined' || $memberClosed || $resting;
if ($incoming) {
    $backHref = '/conversations#new';
    $backLabel = 'talk_section_new';
} elseif ($waiting) {
    $backHref = '/conversations#waiting';
    $backLabel = 'talk_waiting';
} elseif ($closedView) {
    $backHref = '/conversations#closed';
    $backLabel = 'talk_section_closed';
} else {
    $backHref = '/conversations#open';
    $backLabel = 'talk_section_open';
}
$askerId = (int) ($conversation['opened_by'] ?? 0);
$askerName = '';
foreach ($people as $person) {
    if ((int) $person['id'] === $askerId) {
        $askerName = trim((string) ($person['display_name'] ?? ''));
    }
}
if ($askerName === '') {
    $askerName = $askerId > 0 ? display_name_of($askerId) : site_text('garden_member');
}
$heading = $incoming ? $askerName : site_text('talk_heading');
$otherPerson = null;
foreach ($people as $person) {
    if ((int) ($currentUser['id'] ?? 0) !== (int) $person['id']) {
        $otherPerson = $person;
        break;
    }
}
$pageTitle = $heading . ' · ' . site_text('site_title');
$purpose = trim((string) ($conversation['subject'] ?? ''));
$origin = talk_origin_label($conversation);
$shared = (string) ($conversation['context_type'] ?? '') === 'introduction'
    ? trim((string) ($conversation['context_note'] ?? ''))
    : '';
if ($shared === $origin) {
    $shared = '';
}
$life = $life ?? ['ready' => false];
$showLife = $status === 'open' && !empty($life['ready']) && !$memberClosed && !$resting;
?>
<section class="frame section talk-sheet">
  <p class="kicker"><a href="<?= e(url($backHref)) ?>"><?= e(site_text($backLabel)) ?></a></p>
  <h1><?php if ($incoming && $askerId > 0): ?><?= member_name($askerId, (string) $askerName) ?><?php else: ?><?= e($heading) ?><?php endif; ?></h1>
  <?php if (!$incoming && $otherPerson): ?>
    <p><?= member_name((int) $otherPerson['id'], (string) ($otherPerson['display_name'] ?? '')) ?></p>
  <?php endif; ?>
  <?php if ($purpose !== ''): ?>
    <p class="kicker"><?= e(site_text('talk_subject')) ?></p>
    <p><?= e($purpose) ?></p>
  <?php elseif ($origin === ''): ?>
    <p><?= e(site_text('talk_generic')) ?></p>
  <?php endif; ?>
  <?php if ($origin !== '' && $status !== 'declined'): ?>
    <p class="kicker"><?= e(site_text('talk_started_from')) ?></p>
    <p><?= e($origin) ?></p>
  <?php endif; ?>
  <?php if ($shared !== ''): ?><?= paragraphs($shared) ?><?php endif; ?>
  <?php if ($incoming): ?>
    <p class="soft"><?= e(site_line('talk_received', ['date' => nice_date((string) $conversation['created_at'])])) ?></p>
  <?php endif; ?>
  <?php if (!empty($pauseLine) && !$incoming): ?>
    <p class="soft"><?= e($pauseLine) ?></p>
  <?php endif; ?>
  <?php if ($status !== 'declined' && !empty($messages)): ?>
    <div class="stack">
      <?php foreach ($messages as $message): ?>
        <article class="talk-note">
          <?php if (!$incoming): ?>
            <p class="soft"><?= member_name((int) ($message['user_id'] ?? 0), (string) ($message['display_name'] ?? '')) ?> · <?= e(nice_date($message['created_at'])) ?></p>
          <?php endif; ?>
          <?php if (trim((string) $message['body']) !== ''): ?><?= render_writing((string) $message['body']) ?><?php endif; ?>
          <?php $images = $message['images'] ?? []; include __DIR__ . '/../partials/photos.php'; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php if ($incoming && empty($canCompose) && !empty($pauseLine)): ?>
    <p class="soft"><?= e($pauseLine) ?></p>
  <?php endif; ?>
  <?php if (!empty($canCompose)): ?>
    <?php include __DIR__ . '/../partials/talk-composer.php'; ?>
  <?php endif; ?>
  <?php if ($incoming): ?>
    <form class="talk-decline" method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="decline">
      <button class="quiet" type="submit"><?= e(site_text('talk_decline')) ?></button>
    </form>
  <?php endif; ?>
  <?php if ($showLife || !empty($canBlock)): ?>
    <h2><?= e(site_text('talk_settings')) ?></h2>
    <div class="row-actions">
      <?php if ($showLife): ?>
        <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= !empty($life['muted']) ? 'unmute' : 'mute' ?>">
          <button class="quiet small" type="submit"><?= e(site_text(!empty($life['muted']) ? 'life_talk_unmute' : 'life_talk_mute')) ?></button>
        </form>
        <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="close">
          <button class="quiet small" type="submit"><?= e(site_text('life_talk_close')) ?></button>
        </form>
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
