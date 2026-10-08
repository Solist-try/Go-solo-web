<?php $pageTitle = 'Steward Desk · ' . site_text('site_title'); $desk = 'overview'; include __DIR__ . '/open.php'; ?>
<h1>Steward Desk</h1>
<p>A quiet look at what the house needs. Nothing here is a score.</p>
<?php if (empty($conversationsReady)): ?>
  <p class="flash">Private conversations are waiting on one database update. Import <code>sql/update-talk.sql</code> in phpMyAdmin. It does not remove stories or members.</p>
<?php endif; ?>
<?php if (empty($roomsReady)): ?>
  <p class="flash">Campfire photographs, Out There replies, and waypoint discussions are waiting on one database update. Import <code>sql/update-rooms.sql</code> in phpMyAdmin. It does not remove existing stories.</p>
<?php endif; ?>
<?php if (!empty($changePassword)): ?>
  <p class="flash">The starting password is still in use. Change it from <a href="<?= e(url('/account')) ?>">Account</a> when you can.</p>
<?php endif; ?>
<div class="stats">
  <?php foreach ($stats as $stat): ?>
    <div class="stat"><strong><?= e((string) $stat['n']) ?></strong><?= e($stat['label']) ?></div>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/close.php'; ?>
