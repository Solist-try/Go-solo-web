<?php $pageTitle = site_text('forgot_title') . ' · ' . site_text('site_title'); ?>
<section class="frame section narrow">
  <h1><?= e(site_text('forgot_title')) ?></h1>
  <p><?= e(site_text('forgot_intro')) ?></p>
  <form method="post" action="<?= e(url('/forgot')) ?>">
    <?= csrf_field() ?>
    <label><span><?= e(site_text('label_email')) ?></span><input type="email" name="email" maxlength="190" required value="<?= e($email ?? '') ?>"></label>
    <button type="submit"><?= e(site_text('cta_reset')) ?></button>
  </form>
  <p><a href="<?= e(url('/login')) ?>"><?= e(site_text('forgot_back')) ?></a></p>
</section>
