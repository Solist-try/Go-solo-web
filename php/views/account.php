<?php $pageTitle = 'Account · ' . copy('site_title'); ?>
<section class="frame section narrow">
  <h1>Account</h1>
  <form method="post" action="<?= e(url('/account')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="email">
    <label><span>Email</span><input type="email" name="email" maxlength="190" required value="<?= e($email ?? '') ?>"></label>
    <button type="submit">Save email</button>
  </form>
  <form method="post" action="<?= e(url('/account')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="password">
    <h2>Password</h2>
    <label><span>Current password</span><input type="password" name="current_password" required autocomplete="current-password"></label>
    <label><span>New password</span><input type="password" name="new_password" minlength="8" required autocomplete="new-password"></label>
    <button type="submit">Change password</button>
  </form>
  <form method="post" action="<?= e(url('/account/delete')) ?>">
    <?= csrf_field() ?>
    <h2>Close this account</h2>
    <p>This removes your chair, your seeds, and what you shared. It cannot be undone.</p>
    <label><span>Type DELETE to confirm</span><input type="text" name="confirm" autocomplete="off"></label>
    <button class="quiet" type="submit">Delete my account</button>
  </form>
</section>
