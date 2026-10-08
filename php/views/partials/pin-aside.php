<?php if (empty($pinAside) || !$currentUser || !pin_ready()) return; ?>
<?php
  $pinType = (string) ($pinType ?? '');
  $pinBack = (string) ($pinBack ?? '/');
  $pinId = (int) ($pinAsideId ?? 0);
  if ($pinId <= 0) return;
?>
<p class="pin-aside">
  <?= e(site_text('pin_aside')) ?>
  <form method="post" action="<?= e(url('/pins/hide')) ?>" class="inline">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="show">
    <input type="hidden" name="subject_type" value="<?= e($pinType) ?>">
    <input type="hidden" name="subject_id" value="<?= e((string) $pinId) ?>">
    <input type="hidden" name="back" value="<?= e($pinBack) ?>">
    <button class="quiet small" type="submit"><?= e(site_text('pin_show')) ?></button>
  </form>
</p>
