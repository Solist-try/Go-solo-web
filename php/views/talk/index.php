<?php
$pageTitle = site_text('nav_conversations') . ' · ' . site_text('site_title');
$sections = [
    ['new', 'talk_section_new', 'talk_section_new_empty', $incoming, false],
    ['open', 'talk_section_open', 'talk_section_open_empty', $openTalk, false],
    ['drafts', 'talk_section_drafts', 'talk_section_drafts_empty', $drafts, true],
    ['closed', 'talk_section_closed', 'talk_section_closed_empty', $closedTalk, false],
];
?>
<section class="frame section narrow talk-board">
  <h1><?= e(site_text('nav_conversations')) ?></h1>
  <p><?= e(site_text('talk_home_note')) ?></p>
  <?php foreach ($sections as [$anchor, $heading, $empty, $rows, $isDraft]): ?>
    <h2 id="<?= e($anchor) ?>"><?= e(site_text($heading)) ?></h2>
    <?php if (!$rows): ?>
      <p class="soft"><?= e(site_text($empty)) ?></p>
    <?php else: ?>
      <ul class="list">
        <?php foreach ($rows as $conversation): ?>
          <li>
            <a href="<?= e(url('/conversations/' . $conversation['id'])) ?>"><?= e($conversation['other_name'] ?: ($conversation['context_label'] ?: site_text('talk_heading'))) ?></a>
            <?php if ((int) ($conversation['other_id'] ?? 0) > 0): ?> · <a href="<?= e(url('/members/' . $conversation['other_id'])) ?>"><?= e(site_text('garden_link')) ?></a><?php endif; ?>
            <?php include __DIR__ . '/../partials/talk-unread.php'; ?>
            <?php if (trim((string) ($conversation['context_label'] ?? '')) !== ''): ?><span class="soft"> · <?= e($conversation['context_label']) ?></span><?php endif; ?>
            <?php if ($isDraft && trim((string) ($conversation['snippet'] ?? '')) !== ''): ?><span class="soft"> · <?= e($conversation['snippet']) ?></span><?php endif; ?>
            <?php if (!$isDraft): ?><span class="soft"> · <?= e(nice_date($conversation['created_at'])) ?></span><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  <?php endforeach; ?>
  <?php if (!empty($waiting)): ?>
    <h2 id="waiting"><?= e(site_text('talk_waiting')) ?></h2>
    <ul class="list">
      <?php foreach ($waiting as $conversation): ?>
        <li>
          <a href="<?= e(url('/conversations/' . $conversation['id'])) ?>"><?= e($conversation['other_name'] ?: ($conversation['context_label'] ?: site_text('talk_heading'))) ?></a>
          <?php if ((int) ($conversation['other_id'] ?? 0) > 0): ?> · <a href="<?= e(url('/members/' . $conversation['other_id'])) ?>"><?= e(site_text('garden_link')) ?></a><?php endif; ?>
          <?php include __DIR__ . '/../partials/talk-unread.php'; ?>
          <?php if (trim((string) ($conversation['context_label'] ?? '')) !== ''): ?><span class="soft"> · <?= e($conversation['context_label']) ?></span><?php endif; ?>
          <span class="soft"> · <?= e(nice_date($conversation['created_at'])) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>
