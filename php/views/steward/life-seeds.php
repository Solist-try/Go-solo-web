<?php $pageTitle = site_text('life_desk_seeds') . ' · Steward Desk'; $desk = 'community'; include __DIR__ . '/open.php'; ?>
<h1><?= e(site_text('life_desk_seeds')) ?></h1>
<p><a href="<?= e(url('/steward/community')) ?>"><?= e(site_text('life_desk_community')) ?></a></p>
<?php
  $filterAction = '/steward/community/seeds';
  $filterStatuses = ['active' => site_text('life_seed_growing'), 'resting' => site_text('life_seed_hold'), 'grown' => site_text('life_seed_grown'), 'archived' => site_text('life_seed_archived')];
  include __DIR__ . '/../partials/life-filters.php';
?>
<?php if (!$rows): ?><p class="soft"><?= e(site_text('life_empty_grown')) ?></p><?php endif; ?>
<?php foreach ($rows as $row): ?>
  <article class="card">
    <p><?= e((string) $row['title']) ?></p>
    <p class="soft"><?= e((string) ($row['display_name'] ?: 'A member')) ?> · <?= e(life_seed_label((string) $row['status'])) ?> · <?= e((string) ($row['grown_visibility'] ?? '')) ?> · <?= e(nice_date((string) ($row['status_at'] ?: $row['created_at']))) ?></p>
    <?php if ((string) $row['status'] === 'archived'): ?>
      <form method="post" action="<?= e(url('/steward/community/seeds')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="restore">
        <input type="hidden" name="id" value="<?= e((string) $row['id']) ?>">
        <label><input type="checkbox" name="confirm" value="1" required> <?= e(site_text('life_seed_confirm')) ?></label>
        <button class="quiet small" type="submit"><?= e(site_text('life_seed_restore')) ?></button>
      </form>
    <?php endif; ?>
  </article>
<?php endforeach; ?>
<?php $pagerBase = '/steward/community/seeds'; include __DIR__ . '/../partials/life-pager.php'; ?>
<h2><?= e(site_text('life_desk_look')) ?></h2>
<?php if (!$broken): ?><p class="soft"><?= e(site_text('life_empty_desk')) ?></p><?php endif; ?>
<ul class="list"><?php foreach ($broken as $row): ?><li><?= e((string) ($row['display_name'] ?: 'A member')) ?> · <?= e((string) $row['status']) ?></li><?php endforeach; ?></ul>
<?php include __DIR__ . '/close.php'; ?>
