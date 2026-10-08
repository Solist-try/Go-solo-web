<?php
$pageTitle = ($seed['title'] ?? site_text('garden_growing')) . ' · ' . site_text('site_title');
$status = (string) ($seed['status'] ?? 'active');
$outcomeType = 'seed';
$outcomeId = (int) $seed['id'];
$back = '/growing/' . (int) $seed['id'];
?>
<section class="frame section narrow">
  <p class="kicker"><a href="<?= e(url('/profile/edit')) ?>"><?= e(site_text('garden_growing')) ?></a></p>
  <h1><?= e((string) $seed['title']) ?></h1>
  <p><?= e(life_seed_label($status)) ?></p>
  <p class="soft"><?= e(site_text(match ($status) {
      'resting' => 'life_seed_hold_note',
      'grown' => 'life_seed_grown_note',
      'archived' => 'life_seed_archived_note',
      default => 'life_seed_growing_note',
  })) ?></p>
  <?php if (trim((string) ($seed['reflection'] ?? '')) !== ''): ?>
    <h2><?= e(site_text('life_seed_reflect')) ?></h2>
    <?= paragraphs((string) $seed['reflection']) ?>
  <?php endif; ?>

  <?php if ($status !== 'active'): ?>
    <form method="post" action="<?= e(url('/growing')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="status">
      <input type="hidden" name="id" value="<?= e((string) $seed['id']) ?>">
      <input type="hidden" name="status" value="active">
      <button type="submit"><?= e($status === 'archived' ? site_text('life_seed_restore') : site_text('life_seed_resume')) ?></button>
    </form>
  <?php endif; ?>

  <?php if (life_seed_transition($status, 'resting')): ?>
    <form method="post" action="<?= e(url('/growing')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="status">
      <input type="hidden" name="id" value="<?= e((string) $seed['id']) ?>">
      <input type="hidden" name="status" value="resting">
      <p><?= e(site_text('life_seed_hold_note')) ?></p>
      <label><input type="checkbox" name="confirm" value="1" required> <?= e(site_text('life_seed_confirm')) ?></label>
      <button type="submit"><?= e(site_text('life_seed_mark_hold')) ?></button>
    </form>
  <?php endif; ?>

  <?php if (life_seed_transition($status, 'grown')): ?>
    <form method="post" action="<?= e(url('/growing')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="status">
      <input type="hidden" name="id" value="<?= e((string) $seed['id']) ?>">
      <input type="hidden" name="status" value="grown">
      <h2><?= e(site_text('life_seed_reflect')) ?></h2>
      <p class="soft"><?= e(site_text('life_seed_reflect_help')) ?></p>
      <label><span><?= e(site_text('life_seed_reflect')) ?> <span class="soft"><?= e(site_text('life_optional')) ?></span></span>
        <textarea name="reflection" maxlength="4000"><?= e((string) ($seed['reflection'] ?? '')) ?></textarea>
      </label>
      <fieldset class="choices-set">
        <legend><?= e(site_text('life_seed_visibility')) ?></legend>
        <div class="choices">
          <label>
            <input type="radio" name="grown_visibility" value="garden">
            <span class="choice-title"><?= e(site_text('life_seed_garden')) ?></span>
          </label>
          <label>
            <input type="radio" name="grown_visibility" value="private" checked>
            <span class="choice-title"><?= e(site_text('life_seed_private')) ?></span>
          </label>
        </div>
      </fieldset>
      <label><input type="checkbox" name="confirm" value="1" required> <?= e(site_text('life_seed_confirm')) ?></label>
      <button type="submit"><?= e(site_text('life_seed_mark_grown')) ?></button>
    </form>
  <?php endif; ?>

  <?php if ($status === 'grown'): ?>
    <form method="post" action="<?= e(url('/growing')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="visibility">
      <input type="hidden" name="id" value="<?= e((string) $seed['id']) ?>">
      <fieldset class="choices-set">
        <legend><?= e(site_text('life_seed_visibility')) ?></legend>
        <div class="choices">
          <label>
            <input type="radio" name="grown_visibility" value="garden"<?= (string) ($seed['grown_visibility'] ?? '') === 'garden' ? ' checked' : '' ?>>
            <span class="choice-title"><?= e(site_text('life_seed_garden')) ?></span>
          </label>
          <label>
            <input type="radio" name="grown_visibility" value="private"<?= (string) ($seed['grown_visibility'] ?? 'private') !== 'garden' ? ' checked' : '' ?>>
            <span class="choice-title"><?= e(site_text('life_seed_private')) ?></span>
          </label>
        </div>
      </fieldset>
      <button type="submit"><?= e(site_text('life_season_save')) ?></button>
    </form>
    <?php $outcome = $outcome ?? life_outcome_of((int) current_user()['id'], 'seed', (int) $seed['id']); include __DIR__ . '/../partials/life-outcome.php'; ?>
  <?php endif; ?>

  <?php if (life_seed_transition($status, 'archived')): ?>
    <form method="post" action="<?= e(url('/growing')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="status">
      <input type="hidden" name="id" value="<?= e((string) $seed['id']) ?>">
      <input type="hidden" name="status" value="archived">
      <p><?= e(site_text('life_seed_archived_note')) ?></p>
      <label><input type="checkbox" name="confirm" value="1" required> <?= e(site_text('life_seed_confirm')) ?></label>
      <button class="quiet" type="submit"><?= e(site_text('life_seed_archive')) ?></button>
    </form>
  <?php endif; ?>
</section>
