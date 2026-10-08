<?php $pageTitle = site_text('life_desk_pauses') . ' · Steward Desk'; $desk = 'community'; include __DIR__ . '/open.php'; ?>
<h1><?= e(site_text('life_desk_pauses')) ?></h1>
<p><a href="<?= e(url('/steward/community')) ?>"><?= e(site_text('life_desk_community')) ?></a></p>
<?php
  $filterAction = '/steward/community/pauses';
  $filterStatuses = [
      'introductions' => site_text('life_space_intro'),
      'seeds' => site_text('life_space_seed'),
      'skills' => site_text('life_space_skill'),
      'notices' => site_text('life_space_notices'),
      'conversations' => site_text('life_space_talk'),
      'restricted' => site_text('life_desk_restricted'),
  ];
  include __DIR__ . '/../partials/life-filters.php';
?>
<?php if (!$rows): ?><p class="soft"><?= e(site_text('life_empty_desk')) ?></p><?php endif; ?>
<?php foreach ($rows as $row): ?>
  <article class="card">
    <p><a href="<?= e(url('/steward/members/' . $row['id'])) ?>"><?= e((string) ($row['display_name'] ?: $row['email'])) ?></a></p>
    <p class="soft">
      <?= e((string) $row['status']) ?>
      <?php if ((int) $row['pause_introductions'] === 1): ?> · <?= e(site_text('life_space_intro')) ?><?php endif; ?>
      <?php if ((int) $row['pause_seed_support'] === 1): ?> · <?= e(site_text('life_space_seed')) ?><?php endif; ?>
      <?php if ((int) $row['pause_skill_interest'] === 1): ?> · <?= e(site_text('life_space_skill')) ?><?php endif; ?>
      <?php if ((int) $row['mute_notices'] === 1): ?> · <?= e(site_text('life_space_notices')) ?><?php endif; ?>
      <?php if ((string) $row['conversations_pref'] === 'none'): ?> · <?= e(site_text('life_space_talk')) ?><?php endif; ?>
      <?php if ((int) $row['conversations_held'] === 1): ?> · <?= e(site_text('talk_held')) ?><?php endif; ?>
    </p>
  </article>
<?php endforeach; ?>
<?php $pagerBase = '/steward/community/pauses'; include __DIR__ . '/../partials/life-pager.php'; ?>
<?php include __DIR__ . '/close.php'; ?>
