<?php
$pageTitle = site_text('talk_heading') . ' · ' . site_text('site_title');
$reasons = preg_split("/\r\n|\n|\r/", (string) ($conversation['context_note'] ?? '')) ?: [];
?>
<section class="frame section narrow">
  <p class="kicker"><a href="<?= e(url('/profile')) ?>"><?= e(site_text('talk_active')) ?></a></p>
  <h1><?= e(site_text('talk_heading')) ?></h1>
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
  <?php endif; ?>
  <h2><?= e(site_text('talk_participants')) ?></h2>
  <ul class="list">
    <?php foreach ($people as $person): ?>
      <li><a href="<?= e(url('/members/' . $person['id'])) ?>"><?= e($person['display_name'] ?: site_text('garden_member')) ?></a></li>
    <?php endforeach; ?>
  </ul>
  <?php if ((int) $conversation['closed'] === 1): ?>
    <p class="soft"><?= e(site_text('talk_closed')) ?></p>
  <?php elseif (!empty($held)): ?>
    <p class="soft"><?= e(site_text('talk_held')) ?></p>
  <?php elseif (empty($canReply)): ?>
    <p class="soft"><?= e(site_text('talk_not_taking')) ?></p>
  <?php endif; ?>
  <div class="stack">
    <?php foreach ($messages as $message): ?>
      <article class="card">
        <p class="soft"><a href="<?= e(url('/members/' . $message['user_id'])) ?>"><?= e($message['display_name'] ?: site_text('garden_member')) ?></a> · <?= e(nice_date($message['created_at'])) ?></p>
        <?php if (trim((string) $message['body']) !== ''): ?><?= render_writing((string) $message['body']) ?><?php endif; ?>
        <?php $images = $message['images'] ?? []; include __DIR__ . '/../partials/photos.php'; ?>
      </article>
    <?php endforeach; ?>
  </div>
  <?php if (!empty($canReply)): ?>
    <?php include __DIR__ . '/../partials/editor.php'; ?>
  <?php endif; ?>
  <form method="post" action="<?= e(url('/conversations/' . $conversation['id'] . '/report')) ?>">
    <?= csrf_field() ?>
    <label><span><?= e(site_text('label_report')) ?></span><textarea name="reason" maxlength="1000" required></textarea></label>
    <button class="quiet small" type="submit"><?= e(site_text('cta_send_report')) ?></button>
  </form>
</section>
