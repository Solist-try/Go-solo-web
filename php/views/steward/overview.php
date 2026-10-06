<?php $pageTitle = 'Steward Desk · ' . copy('site_title'); $desk = 'overview'; include __DIR__ . '/open.php'; ?>
<h1>Steward Desk</h1>
<p>A quiet look at what the house needs. Nothing here is a score.</p>
<?php if (!empty($changePassword)): ?>
  <p class="flash">The starting password is still in use. Change it from <a href="<?= e(url('/account')) ?>">Account</a> when you can.</p>
<?php endif; ?>
<div class="stats">
  <?php foreach ($stats as $stat): ?>
    <div class="stat"><strong><?= e((string) $stat['n']) ?></strong><?= e($stat['label']) ?></div>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/close.php'; ?>
