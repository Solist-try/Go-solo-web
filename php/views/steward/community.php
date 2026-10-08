<?php $pageTitle = site_text('life_desk_community') . ' · Steward Desk'; $desk = 'community'; include __DIR__ . '/open.php'; ?>
<h1><?= e(site_text('life_desk_community')) ?></h1>
<?php if (empty($ready)): ?>
  <p><?= e(site_text('life_desk_ready')) ?></p>
<?php else: ?>
  <p><?= e(site_text('life_desk_overview')) ?></p>
  <ul class="list">
    <li><a href="<?= e(url('/steward/community/seeds')) ?>"><?= e(site_text('life_desk_seeds')) ?></a></li>
    <li><a href="<?= e(url('/steward/community/skills')) ?>"><?= e(site_text('life_desk_skills')) ?></a></li>
    <li><a href="<?= e(url('/steward/same')) ?>"><?= e(site_text('life_desk_same')) ?></a></li>
    <li><a href="<?= e(url('/steward/waypoints')) ?>"><?= e(site_text('life_desk_waypoints')) ?></a></li>
    <li><a href="<?= e(url('/steward/reports')) ?>"><?= e(site_text('life_desk_reports')) ?><?php if ($pending > 0): ?> · <?= e((string) $pending) ?><?php endif; ?></a></li>
    <li><a href="<?= e(url('/steward/community/outcomes')) ?>"><?= e(site_text('life_desk_outcomes')) ?></a></li>
    <li><a href="<?= e(url('/steward/community/pauses')) ?>"><?= e(site_text('life_desk_pauses')) ?><?php if ($paused > 0): ?> · <?= e((string) $paused) ?><?php endif; ?></a></li>
    <li><a href="<?= e(url('/steward/community/links')) ?>"><?= e(site_text('life_desk_links')) ?></a></li>
    <li><a href="<?= e(url('/steward/community/seasons')) ?>"><?= e(site_text('life_desk_seasons')) ?></a></li>
  </ul>
  <h2><?= e(site_text('life_desk_seeds')) ?></h2>
  <ul class="list"><?php foreach ($seedCounts as $row): ?><li><?= e(life_seed_label((string) $row['status'])) ?> · <?= e((string) $row['n']) ?></li><?php endforeach; ?></ul>
  <h2><?= e(site_text('life_desk_skills')) ?></h2>
  <ul class="list"><?php foreach ($skillCounts as $row): ?><li><?= e(life_skill_label((string) $row['status'])) ?> · <?= e((string) $row['n']) ?></li><?php endforeach; ?></ul>
  <h2><?= e(site_text('life_desk_same')) ?></h2>
  <ul class="list"><?php foreach ($introCounts as $row): ?><li><?= e((string) $row['status']) ?> · <?= e((string) $row['n']) ?></li><?php endforeach; ?></ul>
  <h2><?= e(site_text('life_desk_outcomes')) ?></h2>
  <?php if (!$outcomeCounts): ?><p class="soft"><?= e(site_text('life_empty_offered')) ?></p><?php endif; ?>
  <ul class="list"><?php foreach ($outcomeCounts as $row): ?><li><?= e((string) $row['status']) ?> · <?= e((string) $row['n']) ?></li><?php endforeach; ?></ul>
  <h2><?= e(site_text('life_season_heading')) ?></h2>
  <?php if (!$seasons): ?><p class="soft"><?= e(site_text('life_empty_seasons')) ?></p><?php endif; ?>
  <ul class="list"><?php foreach ($seasons as $season): ?><li><?= e((string) $season['label']) ?> · <?= e((string) $season['n']) ?></li><?php endforeach; ?></ul>
<?php endif; ?>
<?php include __DIR__ . '/close.php'; ?>
