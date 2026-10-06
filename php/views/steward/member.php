<?php $pageTitle = 'Member · Steward Desk'; $desk = 'members'; include __DIR__ . '/open.php'; ?>
<h1><?= e($person['display_name'] ?: 'A member') ?></h1>
<p class="soft"><?= e($person['email']) ?> · <?= e($person['role']) ?> · <?= e($person['status']) ?></p>
<p><a href="<?= e(url('/members/' . $person['id'])) ?>">See the garden</a></p>

<form method="post" action="<?= e(url('/steward/members/' . $person['id'])) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="profile">
  <label><span>Name</span><input type="text" name="display_name" maxlength="80" value="<?= e($person['display_name'] ?? '') ?>"></label>
  <label><span>Bio</span><textarea name="bio" maxlength="4000"><?= e($person['bio'] ?? '') ?></textarea></label>
  <label><span>Location</span><input type="text" name="location" maxlength="120" value="<?= e($person['location'] ?? '') ?>"></label>
  <label><span>Contact frequency</span>
    <select name="contact_frequency">
      <option value="">Whenever it suits</option>
      <?php foreach (frequency_choices() as $choice): ?>
        <option<?= ($person['contact_frequency'] ?? '') === $choice ? ' selected' : '' ?>><?= e($choice) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label><span>Check-in style</span>
    <select name="check_in_style">
      <option value="">Any way that feels easy</option>
      <?php foreach (style_choices() as $choice): ?>
        <option<?= ($person['check_in_style'] ?? '') === $choice ? ' selected' : '' ?>><?= e($choice) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <div class="checks">
    <?php foreach (support_choices() as $choice): ?>
      <label><input type="checkbox" name="support[]" value="<?= e($choice) ?>"<?= in_array($choice, $supports, true) ? ' checked' : '' ?>> <?= e($choice) ?></label>
    <?php endforeach; ?>
  </div>
  <h2>Seeds I'd Like Help Growing</h2>
  <?php for ($i = 0; $i < 6; $i++): ?>
    <label><input type="text" name="help_request[]" maxlength="120" value="<?= e($helpRequests[$i] ?? '') ?>"></label>
  <?php endfor; ?>
  <h2>Seeds I'm Happy To Help Plant</h2>
  <?php for ($i = 0; $i < 6; $i++): ?>
    <label><input type="text" name="help_offer[]" maxlength="120" value="<?= e($helpOffers[$i] ?? '') ?>"></label>
  <?php endfor; ?>
  <?php if (is_admin()): ?>
    <label><span>Role</span>
      <select name="role">
        <?php foreach (['member', 'moderator', 'admin'] as $role): ?>
          <option value="<?= e($role) ?>"<?= $person['role'] === $role ? ' selected' : '' ?>><?= e($role) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  <?php endif; ?>
  <button type="submit">Save profile</button>
</form>

<?php if ((int) $person['id'] !== (int) $currentUser['id']): ?>
  <h2>Trust</h2>
  <div class="row-actions">
    <form method="post" action="<?= e(url('/steward/members/' . $person['id'])) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="status">
      <input type="hidden" name="status" value="active">
      <button class="quiet small" type="submit">Set active</button>
    </form>
    <form method="post" action="<?= e(url('/steward/members/' . $person['id'])) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="status">
      <input type="hidden" name="status" value="suspended">
      <button class="quiet small" type="submit">Suspend</button>
    </form>
    <?php if (is_admin()): ?>
      <form method="post" action="<?= e(url('/steward/members/' . $person['id'])) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="status">
        <input type="hidden" name="status" value="banned">
        <button class="quiet small" type="submit">Ban</button>
      </form>
    <?php endif; ?>
  </div>
<?php endif; ?>

<form method="post" action="<?= e(url('/steward/members/' . $person['id'])) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="warn">
  <label><span>Warn this member</span><textarea name="note" maxlength="2000"></textarea></label>
  <button class="quiet" type="submit">Leave a warning</button>
</form>

<form method="post" action="<?= e(url('/steward/members/' . $person['id'])) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="note">
  <label><span>Moderator note, only for the desk</span><textarea name="note" maxlength="2000"></textarea></label>
  <button class="quiet" type="submit">Save the note</button>
</form>

<?php if (is_admin() && (int) $person['id'] !== (int) $currentUser['id']): ?>
  <form method="post" action="<?= e(url('/steward/members/' . $person['id'])) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="password">
    <label><span>Set a new password</span><input type="text" name="new_password" minlength="8" autocomplete="off"></label>
    <button class="quiet" type="submit">Update password</button>
  </form>
  <form method="post" action="<?= e(url('/steward/members/' . $person['id'] . '/delete')) ?>">
    <?= csrf_field() ?>
    <h2>Delete member</h2>
    <p>This removes the account and what they shared.</p>
    <label><span>Type the email to confirm</span><input type="text" name="confirm" autocomplete="off"></label>
    <button class="quiet" type="submit">Delete member</button>
  </form>
<?php endif; ?>

<h2>Warnings</h2>
<?php if (!$warnings): ?><p class="soft">None.</p><?php endif; ?>
<ul class="list"><?php foreach ($warnings as $warning): ?><li><?= e($warning['note']) ?> <span class="soft"><?= e(nice_date($warning['created_at'])) ?></span></li><?php endforeach; ?></ul>

<h2>Moderator notes</h2>
<?php if (!$notes): ?><p class="soft">None.</p><?php endif; ?>
<ul class="list"><?php foreach ($notes as $note): ?><li><?= e($note['note']) ?> <span class="soft"><?= e($note['steward_name'] ?: 'Steward') ?> · <?= e(nice_date($note['created_at'])) ?></span></li><?php endforeach; ?></ul>

<h2>Activity</h2>
<?php if (!$activity): ?><p class="soft">Nothing recorded yet.</p><?php endif; ?>
<ul class="list"><?php foreach ($activity as $item): ?><li><?= e($item['summary']) ?> <span class="soft"><?= e(nice_date($item['created_at'])) ?></span></li><?php endforeach; ?></ul>
<?php include __DIR__ . '/close.php'; ?>