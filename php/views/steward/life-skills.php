<?php $pageTitle = site_text('life_desk_skills') . ' · Steward Desk'; $desk = 'community'; include __DIR__ . '/open.php'; ?>
<h1><?= e(site_text('life_desk_skills')) ?></h1>
<p><a href="<?= e(url('/steward/community')) ?>"><?= e(site_text('life_desk_community')) ?></a></p>
<?php
  $filterAction = '/steward/community/skills';
  $filterStatuses = ['open' => site_text('life_skill_open'), 'paused' => site_text('life_skill_paused'), 'completed' => site_text('life_skill_completed'), 'archived' => site_text('life_skill_archived')];
  include __DIR__ . '/../partials/life-filters.php';
?>
<?php if (!$rows): ?><p class="soft"><?= e(site_text('life_empty_skills')) ?></p><?php endif; ?>
<?php foreach ($rows as $row): ?>
  <article class="card">
    <p><?= e((string) $row['title']) ?></p>
    <p class="soft"><?= e((string) ($row['display_name'] ?: 'A member')) ?> · <?= e((string) $row['kind']) ?> · <?= e(life_skill_label((string) $row['listing_status'])) ?></p>
    <?php if ((string) $row['listing_status'] !== 'archived'): ?>
      <form method="post" action="<?= e(url('/steward/community/skills')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="archive">
        <input type="hidden" name="kind" value="<?= e((string) $row['kind']) ?>">
        <input type="hidden" name="id" value="<?= e((string) $row['id']) ?>">
        <button class="quiet small" type="submit"><?= e(site_text('life_skill_archive')) ?></button>
      </form>
    <?php else: ?>
      <form method="post" action="<?= e(url('/steward/community/skills')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="restore">
        <input type="hidden" name="kind" value="<?= e((string) $row['kind']) ?>">
        <input type="hidden" name="id" value="<?= e((string) $row['id']) ?>">
        <button class="quiet small" type="submit"><?= e(site_text('life_skill_restore')) ?></button>
      </form>
    <?php endif; ?>
  </article>
<?php endforeach; ?>
<?php $pagerBase = '/steward/community/skills'; include __DIR__ . '/../partials/life-pager.php'; ?>
<?php include __DIR__ . '/close.php'; ?>
