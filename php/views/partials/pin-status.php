<?php
$pinRow = $pinRow ?? null;
$pinType = (string) ($pinType ?? '');
if (!$pinRow || !function_exists('pin_ready') || !pin_ready() || (int) ($pinRow['pinned'] ?? 0) !== 1 || !empty($pinRow['hidden'])) {
    return;
}
$me = current_user();
$setAside = $me && pin_hidden((int) $me['id'], $pinType, (int) $pinRow['id']);
?>
<?php if ($setAside): ?>
  <?php $pinAside = true; $pinAsideId = (int) $pinRow['id']; include __DIR__ . '/pin-aside.php'; ?>
<?php else: ?>
  <p class="pin-mark"><span aria-hidden="true">📌</span> <?= e(site_text((string) ($pinKicker ?? 'pin_by'))) ?></p>
  <?php if ($me): ?>
    <form method="post" action="<?= e(url('/pins/hide')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="subject_type" value="<?= e($pinType) ?>">
      <input type="hidden" name="subject_id" value="<?= e((string) $pinRow['id']) ?>">
      <input type="hidden" name="back" value="<?= e((string) ($pinBack ?? '/')) ?>">
      <button class="quiet small" type="submit"><?= e(site_text((string) ($pinHide ?? 'pin_hide'))) ?></button>
    </form>
  <?php endif; ?>
<?php endif; ?>
