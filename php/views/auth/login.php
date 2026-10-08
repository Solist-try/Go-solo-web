<?php $pageTitle = site_text('login_title') . ' · ' . site_text('site_title'); ?>
<section class="frame section narrow">
  <h1><?= e(site_text('login_title')) ?></h1>
  <p><?= e(site_text('login_intro')) ?></p>
  <form method="post" action="<?= e(url('/login')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= e($next ?? '/profile') ?>">
    <label><span><?= e(site_text('label_email')) ?></span><input type="email" name="email" maxlength="190" required value="<?= e($email ?? '') ?>" autocomplete="username"></label>
    <label><span><?= e(site_text('label_password')) ?></span><input type="password" name="password" required autocomplete="current-password"></label>
    <?php if (!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <button type="submit"><?= e(site_text('cta_log_in')) ?></button>
  </form>
  <p><a href="<?= e(url('/forgot')) ?>"><?= e(site_text('login_forgot')) ?></a></p>
  <p><?= e(site_text('login_new')) ?> <a href="<?= e(url('/join')) ?>"><?= e(site_text('cta_join')) ?></a></p>
</section>
