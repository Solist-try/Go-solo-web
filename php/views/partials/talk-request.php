<?php
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
$why = talk_context_heading((string) $conversation['context_type']);
$label = trim((string) ($conversation['context_label'] ?? ''));
$shared = trim((string) ($conversation['context_note'] ?? ''));
if ($shared === $label) {
    $shared = '';
}
?>
<article class="card">
  <h2><?= e(site_text('talk_request_who')) ?></h2>
  <p><?= e($askerName) ?></p>
  <h2><?= e(site_text('talk_request_why')) ?></h2>
  <p><?= e($why) ?></p>
  <?php if ($label !== '' && $label !== $why): ?>
    <p><?= e($label) ?></p>
  <?php endif; ?>
  <?php if ($shared !== ''): ?>
    <h2><?= e(site_text('talk_request_shared')) ?></h2>
    <?= paragraphs($shared) ?>
  <?php endif; ?>
  <?php if (!empty($messages)): ?>
    <h2><?= e(site_text('talk_request_opening')) ?></h2>
    <?php foreach ($messages as $message): ?>
      <div>
        <?php if (trim((string) $message['body']) !== ''): ?><?= render_writing((string) $message['body']) ?><?php endif; ?>
        <?php $images = $message['images'] ?? []; include __DIR__ . '/photos.php'; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  <div class="row-actions">
    <?php if (!empty($canAccept) && user_is_verified()): ?>
      <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="accept">
        <button type="submit"><?= e(site_text('talk_accept')) ?></button>
      </form>
    <?php endif; ?>
    <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="later">
      <button class="quiet" type="submit"><?= e(site_text('talk_not_now')) ?></button>
    </form>
    <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="decline">
      <button class="quiet" type="submit"><?= e(site_text('talk_decline')) ?></button>
    </form>
  </div>
  <?php if (!empty($canAccept) && !user_is_verified()): ?>
    <p class="soft"><?= e(site_text('msg_verify_first')) ?> <a href="<?= e(url('/account')) ?>"><?= e(site_text('cta_account')) ?></a></p>
  <?php elseif (empty($canAccept) && !empty($pauseLine)): ?>
    <p class="soft"><?= e($pauseLine) ?></p>
  <?php endif; ?>
</article>
