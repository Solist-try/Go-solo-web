<?php
$outcomeType = (string) ($outcomeType ?? '');
$outcomeId = (int) ($outcomeId ?? 0);
$back = (string) ($back ?? '/profile');
$outcome = $outcome ?? null;
if (!life_ready() || !life_outcome_allowed((int) (current_user()['id'] ?? 0), $outcomeType, $outcomeId)) {
    return;
}
$share = (string) ($outcome['share'] ?? 'private');
?>
<form method="post" action="<?= e(url('/outcomes')) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="subject_type" value="<?= e($outcomeType) ?>">
  <input type="hidden" name="subject_id" value="<?= e((string) $outcomeId) ?>">
  <input type="hidden" name="back" value="<?= e($back) ?>">
  <h2><?= e(site_text('life_outcome_invite')) ?></h2>
  <p class="soft"><?= e(site_text('life_outcome_prompts')) ?></p>
  <p class="soft"><?= e(site_text('life_outcome_names')) ?></p>
  <label><span><?= e(site_text('life_outcome_invite')) ?> <span class="soft"><?= e(site_text('life_optional')) ?></span></span>
    <textarea name="body" maxlength="4000"><?= e((string) ($outcome['body'] ?? '')) ?></textarea>
  </label>
  <fieldset class="choices-set">
    <legend><?= e(site_text('life_outcome_preview')) ?></legend>
    <div class="choices">
      <label>
        <input type="radio" name="share" value="private"<?= $share !== 'garden' && $share !== 'offered' ? ' checked' : '' ?>>
        <span class="choice-title"><?= e(site_text('life_outcome_private')) ?></span>
        <p class="soft"><?= e(site_text('life_outcome_private_note')) ?></p>
      </label>
      <label>
        <input type="radio" name="share" value="garden"<?= $share === 'garden' ? ' checked' : '' ?>>
        <span class="choice-title"><?= e(site_text('life_outcome_garden')) ?></span>
        <p class="soft"><?= e(site_text('life_outcome_garden_note')) ?></p>
      </label>
      <label>
        <input type="radio" name="share" value="offered"<?= $share === 'offered' ? ' checked' : '' ?>>
        <span class="choice-title"><?= e(site_text('life_outcome_offer')) ?></span>
        <p class="soft"><?= e(site_text('life_outcome_offer_note')) ?></p>
      </label>
    </div>
  </fieldset>
  <?php if ($outcome && trim((string) ($outcome['public_body'] ?? '')) !== ''): ?>
    <h3><?= e(site_text('life_outcome_preview')) ?></h3>
    <?= paragraphs((string) $outcome['public_body']) ?>
  <?php endif; ?>
  <button type="submit"><?= e(site_text('life_outcome_save')) ?></button>
</form>
<?php if ($outcome && (string) ($outcome['consent'] ?? '') === 'permission'): ?>
  <form method="post" action="<?= e(url('/outcomes')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="permit">
    <input type="hidden" name="subject_type" value="<?= e($outcomeType) ?>">
    <input type="hidden" name="subject_id" value="<?= e((string) $outcomeId) ?>">
    <input type="hidden" name="back" value="<?= e($back) ?>">
    <p><?= e(site_text('life_outcome_permission')) ?></p>
    <button type="submit"><?= e(site_text('life_outcome_permit')) ?></button>
  </form>
<?php endif; ?>
<?php if ($outcome && !in_array((string) ($outcome['consent'] ?? 'none'), ['none', 'withdrawn'], true)): ?>
  <form method="post" action="<?= e(url('/outcomes')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="withdraw">
    <input type="hidden" name="subject_type" value="<?= e($outcomeType) ?>">
    <input type="hidden" name="subject_id" value="<?= e((string) $outcomeId) ?>">
    <input type="hidden" name="back" value="<?= e($back) ?>">
    <button class="quiet" type="submit"><?= e(site_text('life_outcome_withdraw')) ?></button>
  </form>
<?php endif; ?>
