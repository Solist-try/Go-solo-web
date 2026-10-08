<?php $pageTitle = site_text('account_title') . ' · ' . site_text('site_title'); ?>
<section class="frame section narrow">
  <h1><?= e(site_text('account_title')) ?></h1>
  <form method="post" action="<?= e(url('/account')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="email">
    <label><span><?= e(site_text('label_email')) ?></span><input type="email" name="email" maxlength="190" required value="<?= e($email ?? '') ?>"></label>
    <button type="submit"><?= e(site_text('cta_save_email')) ?></button>
  </form>
  <form method="post" action="<?= e(url('/account')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="password">
    <h2><?= e(site_text('account_password')) ?></h2>
    <label><span><?= e(site_text('account_current')) ?></span><input type="password" name="current_password" required autocomplete="current-password"></label>
    <label><span><?= e(site_text('account_new')) ?></span><input type="password" name="new_password" minlength="8" required autocomplete="new-password"></label>
    <button type="submit"><?= e(site_text('cta_change_password')) ?></button>
  </form>
  <form method="post" action="<?= e(url('/account/delete')) ?>">
    <?= csrf_field() ?>
    <h2><?= e(site_text('account_close')) ?></h2>
    <p><?= e(site_text('account_close_body')) ?></p>
    <label><span><?= e(site_text('account_confirm')) ?></span><input type="text" name="confirm" autocomplete="off"></label>
    <button class="quiet" type="submit"><?= e(site_text('cta_delete_account')) ?></button>
  </form>
</section>
