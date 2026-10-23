<?php $pageTitle = site_text('account_close') . ' · ' . site_text('site_title'); ?>
<section class="frame section narrow">
  <h1><?= e(site_text('account_close')) ?></h1>
  <p><?= e(site_text('delete_confirm_body')) ?></p>
  <form method="post" action="<?= e(url('/account/delete/confirm')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
    <button class="quiet" type="submit"><?= e(site_text('cta_confirm_delete')) ?></button>
  </form>
</section>
