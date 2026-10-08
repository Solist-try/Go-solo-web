<?php
$pin = $pin ?? null;
if (!$pin) {
    return;
}
$pinType = (string) ($pinType ?? '');
$pinBack = (string) ($pinBack ?? '/');
$pinKicker = (string) ($pinKicker ?? 'pin_by');
$pinHide = (string) ($pinHide ?? 'pin_hide');
?>
<article class="card welcome">
  <p class="pin-mark"><span aria-hidden="true">📌</span> <?= e(site_text($pinKicker)) ?></p>
  <h2><a href="<?= e(url((string) $pin['href'])) ?>"><?= e((string) $pin['title']) ?></a></h2>
  <?php if (trim((string) ($pin['meta'] ?? '')) !== ''): ?><p class="soft"><?= e((string) $pin['meta']) ?></p><?php endif; ?>
  <?php if (trim((string) ($pin['excerpt'] ?? '')) !== ''): ?><p><?= e((string) $pin['excerpt']) ?></p><?php endif; ?>
  <?php if (is_steward() && ($pinManage ?? '') !== ''): ?>
    <div class="row-actions">
      <form method="post" action="<?= e(url((string) $pinManage)) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="unpin">
        <button class="quiet small" type="submit"><?= e(site_text('pin_unpin')) ?></button>
      </form>
      <?php if (($pinEdit ?? '') !== ''): ?><a class="button quiet small" href="<?= e(url((string) $pinEdit)) ?>"><?= e(site_text('pin_edit')) ?></a><?php endif; ?>
    </div>
  <?php endif; ?>
  <?php if ($currentUser): ?>
    <form method="post" action="<?= e(url('/pins/hide')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="subject_type" value="<?= e($pinType) ?>">
      <input type="hidden" name="subject_id" value="<?= e((string) $pin['id']) ?>">
      <input type="hidden" name="back" value="<?= e($pinBack) ?>">
      <button class="quiet small" type="submit"><?= e(site_text($pinHide)) ?></button>
    </form>
  <?php endif; ?>
</article>
