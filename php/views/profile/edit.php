<?php $pageTitle = site_text('edit_title') . ' · ' . site_text('site_title'); ?>
<section class="frame section narrow">
  <h1><?= e(site_text('edit_title')) ?></h1>
  <p><a href="<?= e(url('/profile')) ?>"><?= e(site_text('edit_see')) ?></a></p>
  <?php if (conversations_ready()): ?>
    <p><a href="<?= e(url('/profile#communication')) ?>"><?= e(site_text('talk_edit_link')) ?></a></p>
  <?php endif; ?>
  <form method="post" action="<?= e(url('/profile')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <label><span><?= e(site_text('label_name')) ?></span><input type="text" name="display_name" maxlength="80" required value="<?= e($person['display_name'] ?? '') ?>"></label>
    <label><span><?= e(site_text('label_bio')) ?></span><textarea name="bio" maxlength="4000"><?= e($person['bio'] ?? '') ?></textarea></label>
    <label><span><?= e(site_text('edit_location')) ?></span><input type="text" name="location" maxlength="120" value="<?= e($person['location'] ?? '') ?>"></label>
    <div class="checks"><label><input type="checkbox" name="show_location" value="1"<?= !empty($person['show_location']) ? ' checked' : '' ?>> <?= e(site_text('edit_show_location')) ?></label></div>
    <label><span><?= e(site_text('edit_photo')) ?></span><input type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif"></label>
    <?php if (!empty($person['avatar_path'])): ?>
      <label><input type="checkbox" name="remove_avatar" value="1"> <?= e(site_text('edit_remove_photo')) ?></label>
    <?php endif; ?>
    <h2><?= e(site_text('garden_support')) ?></h2>
    <div class="checks">
      <?php foreach (support_choices() as $choice): ?>
        <label><input type="checkbox" name="support[]" value="<?= e($choice) ?>"<?= in_array($choice, $supports, true) ? ' checked' : '' ?>> <?= e($choice) ?></label>
      <?php endforeach; ?>
    </div>
    <label><span><?= e(site_text('garden_frequency')) ?></span>
      <select name="contact_frequency">
        <option value=""><?= e(site_text('garden_frequency_any')) ?></option>
        <?php foreach (frequency_choices() as $choice): ?>
          <option value="<?= e($choice) ?>"<?= ($person['contact_frequency'] ?? '') === $choice ? ' selected' : '' ?>><?= e($choice) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label><span><?= e(site_text('garden_style')) ?></span>
      <select name="check_in_style">
        <option value=""><?= e(site_text('garden_style_any')) ?></option>
        <?php foreach (style_choices() as $choice): ?>
          <option value="<?= e($choice) ?>"<?= ($person['check_in_style'] ?? '') === $choice ? ' selected' : '' ?>><?= e($choice) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <h2><?= e(site_text('garden_help')) ?></h2>
    <?php for ($i = 0; $i < 6; $i++): ?>
      <label><span class="soft"><?= e(site_text('label_title')) ?></span><input type="text" name="help_request[]" maxlength="120" value="<?= e($helpRequests[$i] ?? '') ?>"></label>
    <?php endfor; ?>
    <h2><?= e(site_text('garden_offers')) ?></h2>
    <?php for ($i = 0; $i < 6; $i++): ?>
      <label><span class="soft"><?= e(site_text('label_title')) ?></span><input type="text" name="help_offer[]" maxlength="120" value="<?= e($helpOffers[$i] ?? '') ?>"></label>
    <?php endfor; ?>
    <button type="submit"><?= e(site_text('cta_save_garden')) ?></button>
  </form>

  <h2><?= e(site_text('garden_growing')) ?></h2>
  <?php foreach ($growing as $seed): ?>
    <div class="card">
      <?php if (life_ready()): ?>
        <p><?= e($seed['title']) ?> <span class="soft">· <?= e(life_seed_label((string) $seed['status'])) ?></span></p>
        <p><a class="button quiet small" href="<?= e(url('/growing/' . $seed['id'])) ?>"><?= e(site_text('life_seed_open')) ?></a></p>
      <?php else: ?>
      <p><?= e($seed['title']) ?><?php if ($seed['status'] === 'resting'): ?> <span class="soft">· <?= e(site_text('garden_resting')) ?></span><?php endif; ?></p>
      <div class="row-actions">
        <form class="inline" method="post" action="<?= e(url('/growing')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="status">
          <input type="hidden" name="id" value="<?= e((string) $seed['id']) ?>">
          <input type="hidden" name="status" value="<?= $seed['status'] === 'resting' ? 'active' : 'resting' ?>">
          <button class="quiet small" type="submit"><?= e($seed['status'] === 'resting' ? site_text('cta_wake') : site_text('cta_rest')) ?></button>
        </form>
        <form class="inline" method="post" action="<?= e(url('/growing')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= e((string) $seed['id']) ?>">
          <button class="quiet small" type="submit"><?= e(site_text('cta_set_down')) ?></button>
        </form>
      </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <form method="post" action="<?= e(url('/growing')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <label><span><?= e(site_text('edit_tiny')) ?></span><input type="text" name="title" maxlength="120"></label>
    <label><input type="checkbox" name="looking_for_support" value="1"> <?= e(site_text('edit_want_help')) ?></label>
    <button type="submit"><?= e(site_text('cta_plant_it')) ?></button>
  </form>

  <?php if ($planted): ?>
    <h2><?= e(site_text('edit_shelf')) ?></h2>
    <?php foreach ($planted as $seed): ?>
      <form method="post" action="<?= e(url('/planted/remove')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="seed_id" value="<?= e((string) $seed['id']) ?>">
        <p><a href="<?= e(url('/seeds/' . $seed['slug'])) ?>"><?= e($seed['title']) ?></a></p>
        <button class="quiet small" type="submit"><?= e(site_text('cta_set_this')) ?></button>
      </form>
    <?php endforeach; ?>
  <?php endif; ?>
</section>
