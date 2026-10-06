<?php $pageTitle = 'Reset password · ' . copy('site_title'); ?>
<section class="frame section narrow">
  <h1>Choose a new password</h1>
  <?php if (empty($valid)): ?>
    <p>This link has finished or was already used.</p>
    <p><a href="<?= e(url('/forgot')) ?>">Ask for another</a></p>
  <?php else: ?>
    <form method="post" action="<?= e(url('/reset')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
      <label><span>New password</span><input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
      <?php if (!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
      <button type="submit">Save the password</button>
    </form>
  <?php endif; ?>
</section>
