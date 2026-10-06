<?php $pageTitle = 'Tend your garden · ' . copy('site_title'); ?>
<section class="frame section narrow">
  <h1>Tend this garden</h1>
  <p><a href="<?= e(url('/profile')) ?>">See it as others do</a></p>
  <form method="post" action="<?= e(url('/profile')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <label><span>Name</span><input type="text" name="display_name" maxlength="80" required value="<?= e($person['display_name'] ?? '') ?>"></label>
    <label><span>Bio</span><textarea name="bio" maxlength="4000"><?= e($person['bio'] ?? '') ?></textarea></label>
    <label><span>Location</span><input type="text" name="location" maxlength="120" value="<?= e($person['location'] ?? '') ?>"></label>
    <div class="checks"><label><input type="checkbox" name="show_location" value="1"<?= !empty($person['show_location']) ? ' checked' : '' ?>> Show location on the garden</label></div>
    <label><span>Photograph</span><input type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif"></label>
    <?php if (!empty($person['avatar_path'])): ?>
      <label><input type="checkbox" name="remove_avatar" value="1"> Remove the current photograph</label>
    <?php endif; ?>
    <h2>Support Preferences</h2>
    <div class="checks">
      <?php foreach (support_choices() as $choice): ?>
        <label><input type="checkbox" name="support[]" value="<?= e($choice) ?>"<?= in_array($choice, $supports, true) ? ' checked' : '' ?>> <?= e($choice) ?></label>
      <?php endforeach; ?>
    </div>
    <label><span>Contact Frequency</span>
      <select name="contact_frequency">
        <option value="">Whenever it suits</option>
        <?php foreach (frequency_choices() as $choice): ?>
          <option value="<?= e($choice) ?>"<?= ($person['contact_frequency'] ?? '') === $choice ? ' selected' : '' ?>><?= e($choice) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label><span>Check-In Style</span>
      <select name="check_in_style">
        <option value="">Any way that feels easy</option>
        <?php foreach (style_choices() as $choice): ?>
          <option value="<?= e($choice) ?>"<?= ($person['check_in_style'] ?? '') === $choice ? ' selected' : '' ?>><?= e($choice) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <h2>Seeds I'd Like Help Growing</h2>
    <?php for ($i = 0; $i < 6; $i++): ?>
      <label><span class="soft">Title</span><input type="text" name="help_request[]" maxlength="120" value="<?= e($helpRequests[$i] ?? '') ?>"></label>
    <?php endfor; ?>
    <h2>Seeds I'm Happy To Help Plant</h2>
    <?php for ($i = 0; $i < 6; $i++): ?>
      <label><span class="soft">Title</span><input type="text" name="help_offer[]" maxlength="120" value="<?= e($helpOffers[$i] ?? '') ?>"></label>
    <?php endfor; ?>
    <button type="submit">Save the garden</button>
  </form>

  <h2>Seeds I'm Growing</h2>
  <?php foreach ($growing as $seed): ?>
    <div class="card">
      <p><?= e($seed['title']) ?><?php if ($seed['status'] === 'resting'): ?> <span class="soft">· resting</span><?php endif; ?></p>
      <div class="row-actions">
        <form class="inline" method="post" action="<?= e(url('/growing')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="status">
          <input type="hidden" name="id" value="<?= e((string) $seed['id']) ?>">
          <input type="hidden" name="status" value="<?= $seed['status'] === 'resting' ? 'active' : 'resting' ?>">
          <button class="quiet small" type="submit"><?= $seed['status'] === 'resting' ? 'Wake it' : 'Let it rest' ?></button>
        </form>
        <form class="inline" method="post" action="<?= e(url('/growing')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= e((string) $seed['id']) ?>">
          <button class="quiet small" type="submit">Set it down</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
  <form method="post" action="<?= e(url('/growing')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <label><span>A tiny future of your own</span><input type="text" name="title" maxlength="120"></label>
    <label><input type="checkbox" name="looking_for_support" value="1"> I would like help with this one</label>
    <button type="submit">Plant it</button>
  </form>

  <?php if ($planted): ?>
    <h2>From the shelf</h2>
    <?php foreach ($planted as $seed): ?>
      <form method="post" action="<?= e(url('/planted/remove')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="seed_id" value="<?= e((string) $seed['id']) ?>">
        <p><a href="<?= e(url('/seeds/' . $seed['slug'])) ?>"><?= e($seed['title']) ?></a></p>
        <button class="quiet small" type="submit">Set this down</button>
      </form>
    <?php endforeach; ?>
  <?php endif; ?>
</section>
