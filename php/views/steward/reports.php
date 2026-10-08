<?php $pageTitle = 'Reports · Steward Desk'; $desk = 'reports'; include __DIR__ . '/open.php'; ?>
<h1>Reports<?php if (!empty($pending)): ?> · <?= e((string) $pending) ?> unreviewed<?php endif; ?></h1>
<?php
  $filterAction = '/steward/reports';
  $filterType = $type ?? '';
  $filterTypes = ['story' => 'story', 'campfire' => 'campfire', 'comment' => 'comment', 'profile' => 'profile', 'waypoint' => 'waypoint', 'conversation' => 'conversation'];
  $filterStatuses = ['pending' => 'pending', 'reviewed' => 'reviewed', 'dismissed' => 'dismissed'];
  include __DIR__ . '/../partials/life-filters.php';
?>
<?php if (!$reports): ?><p>Nothing has been brought to the desk.</p><?php endif; ?>
<?php foreach ($reports as $report): ?>
  <article class="card">
    <p class="soft"><?= e($report['status']) ?> · <?= e($report['target_type']) ?><?php if (!empty($report['category'])): ?> · <?= e((string) $report['category']) ?><?php endif; ?> · <?= e($report['reporter_name'] ?: 'A member') ?><?php if (!empty($report['target_name'])): ?> · <?= e((string) $report['target_name']) ?><?php endif; ?> · <?= e(nice_date($report['created_at'])) ?></p>
    <p><?= e($report['reason']) ?></p>
    <?php if (!empty($report['resolution'])): ?><p><?= e((string) $report['resolution']) ?></p><?php endif; ?>
    <?php if (!empty($report['history'])): ?>
      <ul class="list">
        <?php foreach ($report['history'] as $event): ?>
          <li class="soft"><?= e((string) $event['note']) ?> · <?= e((string) $event['to_status']) ?> · <?= e(nice_date((string) $event['created_at'])) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php if ($report['href']): ?><p><a href="<?= e(url($report['href'])) ?>">Open it</a></p><?php endif; ?>
    <form method="post" action="<?= e(url('/steward/reports/' . $report['id'])) ?>">
      <?= csrf_field() ?>
      <label><span>Note for a warning or the desk</span><textarea name="note" maxlength="2000"></textarea></label>
      <div class="row-actions">
        <button class="quiet small" name="action" value="dismiss" type="submit">Dismiss</button>
        <button class="quiet small" name="action" value="reviewed" type="submit">Mark reviewed</button>
        <?php if ($report['can_hide']): ?><button class="quiet small" name="action" value="hide" type="submit">Hide content</button><?php endif; ?>
        <?php if ($report['can_delete']): ?><button class="quiet small" name="action" value="delete" type="submit">Delete content</button><?php endif; ?>
        <button class="quiet small" name="action" value="warn" type="submit">Warn member</button>
        <button class="quiet small" name="action" value="suspend" type="submit">Suspend member</button>
        <button class="quiet small" name="action" value="note" type="submit">Add moderator note</button>
      </div>
    </form>
  </article>
<?php endforeach; ?>
<?php $pagerBase = '/steward/reports'; include __DIR__ . '/../partials/life-pager.php'; ?>
<?php include __DIR__ . '/close.php'; ?>
