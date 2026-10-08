<?php
$talkType = (string) ($talkType ?? '');
$talkKind = (string) ($talkKind ?? '');
$talkId = (int) ($talkId ?? 0);
$talkPerson = (int) ($talkPerson ?? 0);
$talkLabel = (string) ($talkLabel ?? '');
$talkBack = (string) ($talkBack ?? '');
?>
<form method="post" action="<?= e(url('/conversations')) ?>" class="inline">
  <?= csrf_field() ?>
  <input type="hidden" name="context_type" value="<?= e($talkType) ?>">
  <input type="hidden" name="context_kind" value="<?= e($talkKind) ?>">
  <input type="hidden" name="context_id" value="<?= e((string) $talkId) ?>">
  <?php if ($talkPerson > 0): ?><input type="hidden" name="person_id" value="<?= e((string) $talkPerson) ?>"><?php endif; ?>
  <?php if ($talkBack !== ''): ?><input type="hidden" name="back" value="<?= e($talkBack) ?>"><?php endif; ?>
  <button class="quiet small" type="submit"><?= e($talkLabel) ?></button>
</form>
