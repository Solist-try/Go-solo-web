<?php $pageTitle = site_text('reset_title') . ' · ' . site_text('site_title'); ?>
<section class="frame section narrow">
  <h1><?= e(site_text('reset_title')) ?></h1>
  <?php if (empty($valid)): ?>
    <p><?= e(site_text('reset_invalid')) ?></p>
    <p><a href="<?= e(url('/forgot')) ?>"><?= e(site_text('reset_again')) ?></a></p>
  <?php else: ?>
    <form method="post" action="<?= e(url('/reset')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
      <label><span><?= e(site_text('account_new')) ?></span><input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
      <?php if (!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
      <button type="submit"><?= e(site_text('cta_save_password')) ?></button>
    </form>
  <?php endif; ?>
</section>
