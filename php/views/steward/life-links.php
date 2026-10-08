<?php $pageTitle = site_text('life_desk_links') . ' · Steward Desk'; $desk = 'community'; include __DIR__ . '/open.php'; ?>
<h1><?= e(site_text('life_desk_links')) ?></h1>
<p><a href="<?= e(url('/steward/community')) ?>"><?= e(site_text('life_desk_community')) ?></a></p>
<?php
  $filterAction = '/steward/community/links';
  $filterStatuses = [
      'seed' => site_text('talk_context_seed'),
      'skill-offer' => site_text('board_offer'),
      'skill-request' => site_text('board_ask'),
      'introduction' => site_text('talk_context_introduction'),
      'story' => site_text('talk_context_story'),
      'path' => site_text('life_path_heading'),
  ];
  include __DIR__ . '/../partials/life-filters.php';
?>
<?php if (!$rows): ?><p class="soft"><?= e(site_text('life_empty_outcomes')) ?></p><?php endif; ?>
<ul class="list">
  <?php foreach ($rows as $row): ?>
    <li>
      <?= e((string) $row['subject_type']) ?> · <?= e((string) ($row['seed_title'] ?: $row['story_title'] ?: $row['subject_id'])) ?>
      · <?= e((string) $row['consent']) ?>
      <?php if (($row['subject_type'] === 'seed' && $row['seed_title'] === null) || ($row['subject_type'] === 'story' && $row['story_title'] === null)): ?>
        <span class="soft">· <?= e(site_text('life_desk_missing')) ?></span>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>
<?php $pagerBase = '/steward/community/links'; include __DIR__ . '/../partials/life-pager.php'; ?>
<?php include __DIR__ . '/close.php'; ?>
