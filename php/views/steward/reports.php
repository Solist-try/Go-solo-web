<?php $pageTitle = 'Reports · Steward Desk'; $desk = 'reports'; include __DIR__ . '/open.php'; ?>
<h1>Reports</h1>
<?php if (!$reports): ?><p>Nothing has been brought to the desk.</p><?php endif; ?>
<?php foreach ($reports as $report): ?>
  <article class="card">
    <p class="soft"><?= e($report['status']) ?> · <?= e($report['target_type']) ?> · <?= e($report['reporter_name'] ?: 'A member') ?> · <?= e(nice_date($report['created_at'])) ?></p>
    <p><?= e($report['reason']) ?></p>
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
<?php include __DIR__ . '/close.php'; ?>
