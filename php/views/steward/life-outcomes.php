<?php $pageTitle = site_text('life_desk_outcomes') . ' · Steward Desk'; $desk = 'community'; include __DIR__ . '/open.php'; ?>
<h1><?= e(site_text('life_desk_outcomes')) ?></h1>
<p><a href="<?= e(url('/steward/community')) ?>"><?= e(site_text('life_desk_community')) ?></a></p>
<?php
  $filterAction = '/steward/community/outcomes';
  $filterStatuses = [
      'offered' => site_text('life_outcome_offer'),
      'review' => 'review',
      'permission' => 'permission',
      'approved' => 'approved',
      'published' => 'published',
      'withdrawn' => site_text('life_outcome_withdraw'),
      'declined' => 'declined',
  ];
  include __DIR__ . '/../partials/life-filters.php';
?>
<?php if (!$rows): ?><p class="soft"><?= e(site_text('life_empty_offered')) ?></p><?php endif; ?>
<?php foreach ($rows as $row): ?>
  <article class="card">
    <p class="soft"><?= e((string) ($row['display_name'] ?: 'A member')) ?> · <?= e((string) $row['subject_type']) ?> · <?= e((string) $row['consent']) ?> · <?= e(nice_date((string) $row['updated_at'])) ?></p>
    <?= paragraphs((string) $row['body']) ?>
    <?php if (trim((string) ($row['public_body'] ?? '')) !== ''): ?>
      <h2><?= e(site_text('life_outcome_preview')) ?></h2>
      <?= paragraphs((string) $row['public_body']) ?>
    <?php endif; ?>
    <p><a href="<?= e(url('/outcomes/' . $row['id'])) ?>"><?= e(site_text('life_outcome_preview')) ?></a></p>
    <div class="row-actions">
      <?php foreach (['review' => 'review', 'permission' => 'permission', 'approve' => 'approved', 'publish' => 'published', 'decline' => 'declined'] as $action => $label): ?>
        <?php if (life_outcome_steward((string) $row['consent'], $action === 'approve' ? 'approved' : ($action === 'publish' ? 'published' : ($action === 'decline' ? 'declined' : $action)), (int) $row['member_confirmed'])): ?>
          <form method="post" action="<?= e(url('/steward/community/outcomes/' . $row['id'])) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= e($action) ?>">
            <button class="quiet small" type="submit"><?= e($label) ?></button>
          </form>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </article>
<?php endforeach; ?>
<?php $pagerBase = '/steward/community/outcomes'; include __DIR__ . '/../partials/life-pager.php'; ?>
<?php include __DIR__ . '/close.php'; ?>
