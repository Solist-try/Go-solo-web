<?php $pageTitle = 'SAME · Steward Desk'; $desk = 'same'; include __DIR__ . '/open.php'; ?>
<h1>Suggested SAME Matches</h1>
<p>People are open. A steward makes the match. Nothing here pairs them on its own.</p>
<h2>Open requests</h2>
<?php if (!$requests): ?><p class="soft">No one is waiting.</p><?php endif; ?>
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
      <label><span>Suggest a partner</span>
        <select name="partner_id" required>
          <option value="">Choose a member</option>
          <?php foreach ($members as $member): ?>
            <?php if ((int) $member['id'] === (int) $request['user_id']) continue; ?>
            <option value="<?= e((string) $member['id']) ?>"><?= e($member['display_name'] ?: $member['email']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label><span>A private note for them</span><textarea name="note" maxlength="1000"></textarea></label>
      <button type="submit">Suggest this match</button>
    </form>
  </article>
<?php endforeach; ?>

<h2>Matches</h2>
<?php if (!$matches): ?><p class="soft">None yet.</p><?php endif; ?>
<div class="table-wrap">
  <table>
    <thead><tr><th>People</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($matches as $match): ?>
        <tr>
          <td><?= e($match['name_a']) ?> and <?= e($match['name_b']) ?><?php if ($match['note']): ?><br><span class="soft"><?= e($match['note']) ?></span><?php endif; ?></td>
          <td><?= e($match['status']) ?></td>
          <td class="row-actions">
            <?php if ($match['status'] === 'suggested'): ?>
              <form method="post" action="<?= e(url('/steward/same/' . $match['id'])) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="approve">
                <button class="small" type="submit">Approve</button>
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
<?php include __DIR__ . '/close.php'; ?>
