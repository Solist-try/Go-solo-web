<?php $pageTitle = 'SAME · Steward Desk'; $desk = 'same'; include __DIR__ . '/open.php'; ?>
<h1>Introductions</h1>
<p>Someone has asked for company on a similar path. You make the introduction. Nothing here pairs people on its own.</p>
<?php if (!empty($lifeReady)): ?>
  <?php
    $filterAction = '/steward/same';
    $filterStatuses = [
        'awaiting' => 'awaiting',
        'open' => 'open',
        'closed' => 'closed',
        'declined' => 'declined',
        'archived' => 'archived',
    ];
    include __DIR__ . '/../partials/life-filters.php';
  ?>
<?php endif; ?>
<h2>Open requests</h2>
<?php if (!$requests): ?><p class="soft"><?= e(site_text('life_empty_intro')) ?></p><?php endif; ?>
<?php foreach ($requests as $request): ?>
  <article class="card">
    <h3><?= e($request['display_name'] ?: 'A member') ?></h3>
    <p class="soft"><?= e($request['seed_title'] ?: 'SAME') ?> · <?= e(nice_date($request['created_at'])) ?></p>
    <?php if ($request['note']): ?><p><?= e($request['note']) ?></p><?php endif; ?>
    <p class="kicker">Support preferences</p>
    <ul class="chips"><?php foreach ($request['supports'] as $choice): ?><li><?= e($choice) ?></li><?php endforeach; ?></ul>
    <p class="soft"><?= e($request['contact_frequency'] ?: 'Frequency not set') ?> · <?= e($request['check_in_style'] ?: 'Style not set') ?></p>
    <form method="post" action="<?= e(url('/steward/same/suggest')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="request_id" value="<?= e((string) $request['id']) ?>">
      <label><span>Introduce someone</span>
        <select name="partner_id" required>
          <option value="">Choose a member</option>
          <?php foreach ($members as $member): ?>
            <?php if ((int) $member['id'] === (int) $request['user_id']) continue; ?>
            <option value="<?= e((string) $member['id']) ?>"><?= e($member['display_name'] ?: $member['email']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label><span>A private note for them</span><textarea name="note" maxlength="1000"></textarea></label>
      <button type="submit">Make this introduction</button>
    </form>
  </article>
<?php endforeach; ?>
<?php if (!empty($pausedRequests)): ?>
  <h2>Not open to an introduction right now</h2>
  <ul class="list">
    <?php foreach ($pausedRequests as $request): ?>
      <li><?= e($request['display_name'] ?: 'A member') ?> · <?= e($request['seed_title'] ?: 'SAME') ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<h2>Introductions</h2>
<?php if (!$matches): ?><p class="soft">None yet.</p><?php endif; ?>
<div class="table-wrap">
  <table>
    <thead><tr><th>People</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($matches as $match): ?>
        <tr>
          <td><?= e($match['name_a']) ?> and <?= e($match['name_b']) ?><?php if ($match['note']): ?><br><span class="soft"><?= e($match['note']) ?></span><?php endif; ?></td>
          <td><?= e($match['status']) ?><?php if (!empty($lifeReady)): ?><br><span class="soft"><?= (int) ($match['consent_a'] ?? 0) === 1 ? 'One person has agreed' : '' ?> <?= (int) ($match['consent_a'] ?? 0) === 1 && (int) ($match['consent_b'] ?? 0) === 1 ? '· both have agreed' : ((int) ($match['consent_b'] ?? 0) === 1 ? 'One person has agreed' : '') ?></span><?php endif; ?></td>
          <td class="row-actions">
            <?php if ($match['status'] === 'suggested' && empty($lifeReady)): ?>
              <form method="post" action="<?= e(url('/steward/same/' . $match['id'])) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="approve">
                <button class="small" type="submit">Approve</button>
              </form>
            <?php endif; ?>
            <?php if (!empty($lifeReady) && in_array($match['status'], ['awaiting', 'suggested', 'open', 'approved'], true)): ?>
              <form method="post" action="<?= e(url('/steward/same/' . $match['id'])) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="close">
                <button class="quiet small" type="submit">Close</button>
              </form>
            <?php endif; ?>
            <?php if ($match['status'] !== 'archived'): ?>
              <form method="post" action="<?= e(url('/steward/same/' . $match['id'])) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="archive">
                <button class="quiet small" type="submit">Archive</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php if (!empty($lifeReady)): ?>
  <?php $pagerBase = '/steward/same'; include __DIR__ . '/../partials/life-pager.php'; ?>
<?php endif; ?>
<?php include __DIR__ . '/close.php'; ?>
