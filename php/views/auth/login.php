<?php $pageTitle = 'Log In · ' . copy('site_title'); ?>
<section class="frame section narrow">
  <h1>Log In</h1>
  <p>Come in whenever you are ready.</p>
  <form method="post" action="<?= e(url('/login')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= e($next ?? '/profile') ?>">
    <label><span>Email</span><input type="email" name="email" maxlength="190" required value="<?= e($email ?? '') ?>" autocomplete="username"></label>
    <label><span>Password</span><input type="password" name="password" required autocomplete="current-password"></label>
    <?php if (!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <button type="submit">Log in</button>
  </form>
  <p><a href="<?= e(url('/forgot')) ?>">Forgot password</a></p>
  <p>New here? <a href="<?= e(url('/join')) ?>">Join Go Solo</a></p>
</section>
