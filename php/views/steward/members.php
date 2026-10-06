<?php $pageTitle = 'Members · Steward Desk'; $desk = 'members'; include __DIR__ . '/open.php'; ?>
<h1>Members</h1>
<form method="get" action="<?= e(url('/steward/members')) ?>">
  <label><span>Search by name or email</span><input type="text" name="q" value="<?= e($q ?? '') ?>"></label>
  <button type="submit">Search</button>
</form>
<div class="table-wrap">
  <table>
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Last seen</th></tr></thead>
    <tbody>
      <?php foreach ($members as $member): ?>
        <tr>
          <td><a href="<?= e(url('/steward/members/' . $member['id'])) ?>"><?= e($member['display_name'] ?: 'A member') ?></a></td>
          <td><?= e($member['email']) ?></td>
          <td><?= e($member['role']) ?></td>
          <td><?= e($member['status']) ?></td>
          <td><?= e(nice_date($member['created_at'])) ?></td>
          <td><?= e(nice_date($member['last_seen_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php if (!$members): ?><p>No one matches.</p><?php endif; ?>
<?php include __DIR__ . '/close.php'; ?>
