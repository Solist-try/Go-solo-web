<?php $pageTitle = 'Conversations · Steward Desk'; $desk = 'conversations'; include __DIR__ . '/open.php'; ?>
<h1>Private conversations</h1>
<p class="soft">Notes stay with the people in them. This desk shows a conversation after a report, or after it has been rested. Opening the notes is a separate step, and it is recorded.</p>
<?php if (empty($ready)): ?>
  <p class="flash">Import <code>sql/update-talk.sql</code> in phpMyAdmin to open private conversations. It does not remove stories or members.</p>
<?php elseif (!$rows): ?>
  <p class="soft">Nothing has been brought to the desk.</p>
<?php endif; ?>
<?php foreach ($rows as $row): ?>
  <article class="card">
    <p class="kicker"><?= e((string) $row['context_type']) ?><?php if ($row['context_label']): ?> · <?= e((string) $row['context_label']) ?><?php endif; ?></p>
    <p>
      <?php foreach ($row['people'] as $i => $person): ?>
        <?php if ($i > 0): ?> · <?php endif; ?><?= e($person['display_name'] ?: 'A member') ?>
      <?php endforeach; ?>
    </p>
    <p class="soft"><?= e(nice_date($row['created_at'])) ?> · <?= e((string) $row['notes']) ?> notes<?php if ((int) $row['closed'] === 1): ?> · resting<?php endif; ?><?php if ((int) $row['pending_reports'] > 0): ?> · report waiting<?php endif; ?></p>
    <p><a href="<?= e(url('/steward/conversations/' . $row['id'])) ?>">Open the desk note</a></p>
  </article>
<?php endforeach; ?>
<?php include __DIR__ . '/close.php'; ?>
