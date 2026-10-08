<?php
$pageTitle = site_text((string) ($conversation['status'] ?? '') === 'requested' ? 'talk_request_heading' : 'talk_heading') . ' · ' . site_text('site_title');
$reasons = preg_split("/\r\n|\n|\r/", (string) ($conversation['context_note'] ?? '')) ?: [];
$declined = (string) ($conversation['status'] ?? '') === 'declined';
$life = $life ?? ['ready' => false];
?>
<section class="frame section narrow">
  <p class="kicker"><a href="<?= e(url('/profile')) ?>"><?= e(site_text('talk_active')) ?></a></p>
  <h1><?= e(site_text($declined ? 'talk_heading' : ((string) $conversation['status'] === 'requested' ? 'talk_request_heading' : 'talk_heading'))) ?></h1>
  <?php if (!$declined): ?>
    <p class="kicker"><?= e(talk_context_heading((string) $conversation['context_type'])) ?></p>
    <?php if (trim((string) $conversation['context_label']) !== ''): ?>
      <p><?= e((string) $conversation['context_label']) ?></p>
    <?php endif; ?>
    <?php if ($conversation['context_type'] === 'introduction'): ?>
      <p><?= e(site_text('talk_because')) ?></p>
      <ul>
        <?php foreach ($reasons as $reason): ?>
          <?php if (trim($reason) !== ''): ?><li><?= e(trim($reason)) ?></li><?php endif; ?>
        <?php endforeach; ?>
      </ul>
    <?php elseif (trim((string) $conversation['context_note']) !== ''): ?>
      <p><?= e((string) $conversation['context_note']) ?></p>
    <?php endif; ?>
    <h2><?= e(site_text('talk_participants')) ?></h2>
    <ul class="list">
      <?php foreach ($people as $person): ?>
        <li><a href="<?= e(url('/members/' . $person['id'])) ?>"><?= e($person['display_name'] ?: site_text('garden_member')) ?></a></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <?php if (!empty($pauseLine)): ?>
    <p class="soft"><?= e($pauseLine) ?></p>
  <?php endif; ?>
  <?php if (!empty($life['context'])): ?>
    <p><?= e((string) $life['context']) ?></p>
  <?php endif; ?>
  <?php if (!empty($life['served'])): ?>
    <p><?= e(site_text('life_talk_served_note')) ?></p>
  <?php endif; ?>
  <?php if (!$declined): ?>
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
  <?php if (!empty($canAccept) || !empty($canDecline)): ?>
    <div class="row-actions">
      <?php if (!empty($canAccept)): ?>
        <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="accept">
          <button type="submit"><?= e(site_text('talk_accept')) ?></button>
        </form>
      <?php endif; ?>
      <?php if (!empty($canDecline)): ?>
        <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="decline">
          <button class="quiet" type="submit"><?= e(site_text('talk_decline')) ?></button>
        </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($canReply)): ?>
    <?php include __DIR__ . '/../partials/editor.php'; ?>
  <?php endif; ?>
  <?php if (!$declined && !empty($life['ready']) && (string) ($conversation['status'] ?? '') !== 'requested'): ?>
    <div class="row-actions">
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
          <p class="soft"><?= e(site_text('life_talk_close_note')) ?></p>
          <button class="quiet small" type="submit"><?= e(site_text('life_talk_close')) ?></button>
        </form>
      <?php elseif (!empty($life['canReopen'])): ?>
        <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="reopen">
          <button class="quiet small" type="submit"><?= e(site_text('life_talk_reopen')) ?></button>
        </form>
      <?php endif; ?>
      <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= !empty($life['served']) ? 'forget' : 'served' ?>">
        <button class="quiet small" type="submit"><?= e(site_text(!empty($life['served']) ? 'life_talk_forget' : 'life_talk_served')) ?></button>
      </form>
      <?php if (!empty($life['skill'])): ?>
        <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= !empty($life['ended']) ? 'rejoin' : 'end' ?>">
          <button class="quiet small" type="submit"><?= e(site_text(!empty($life['ended']) ? 'life_skill_rejoin' : 'life_skill_end')) ?></button>
        </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>
  <?php if (!$declined && !empty($canBlock)): ?>
    <form method="post" action="<?= e(url('/conversations/' . $conversation['id'])) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="block">
      <button class="quiet small" type="submit"><?= e(site_text('talk_block')) ?></button>
    </form>
  <?php endif; ?>
  <?php if (!$declined): ?>
    <form method="post" action="<?= e(url('/conversations/' . $conversation['id'] . '/report')) ?>">
      <?= csrf_field() ?>
      <label><span><?= e(site_text('label_report')) ?></span><textarea name="reason" maxlength="1000" required></textarea></label>
      <?php include __DIR__ . '/../partials/life-report.php'; ?>
      <button class="quiet small" type="submit"><?= e(site_text('cta_send_report')) ?></button>
    </form>
  <?php endif; ?>
</section>
