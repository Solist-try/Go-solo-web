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
$pageTitle = $heading . ' · ' . site_text('site_title');
$label = trim((string) ($conversation['context_label'] ?? ''));
$shared = trim((string) ($conversation['context_note'] ?? ''));
if ($shared === $label || (string) ($conversation['context_type'] ?? '') === 'member') {
    $shared = '';
}
$life = $life ?? ['ready' => false];
$showLife = $status === 'open' && !empty($life['ready']) && !$memberClosed && !$resting;
?>
<section class="frame section narrow">
  <p class="kicker"><a href="<?= e(url($backHref)) ?>"><?= e(site_text($backLabel)) ?></a></p>
  <h1><?= e($heading) ?></h1>
  <?php if ($incoming): ?>
    <?php if ($label !== ''): ?><p><?= e($label) ?></p><?php endif; ?>
    <?php if ($shared !== ''): ?><?= paragraphs($shared) ?><?php endif; ?>
    <p class="soft"><?= e(site_line('talk_received', ['date' => nice_date((string) $conversation['created_at'])])) ?></p>
  <?php elseif ($label !== '' && $status !== 'declined'): ?>
    <p class="soft"><?= e($label) ?></p>
  <?php endif; ?>
  <?php if (!empty($pauseLine) && !$incoming): ?>
    <p class="soft"><?= e($pauseLine) ?></p>
  <?php endif; ?>
  <?php if ($status !== 'declined' && !empty($messages)): ?>
    <div class="stack">
      <?php foreach ($messages as $message): ?>
        <article class="card">
          <?php if (!$incoming): ?>
            <p class="soft"><?= e($message['display_name'] ?: site_text('garden_member')) ?> · <?= e(nice_date($message['created_at'])) ?></p>
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
