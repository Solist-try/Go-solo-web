<?php $pageTitle = site_text('join_title') . ' · ' . site_text('site_title'); ?>
<section class="frame section narrow">
  <h1><?= e(site_text('join_title')) ?></h1>
  <p><?= e(site_text('join_intro')) ?></p>
  <form method="post" action="<?= e(url('/join')) ?>">
    <?= csrf_field() ?>
    <label><span><?= e(site_text('join_call_you')) ?></span><input type="text" name="name" maxlength="80" required value="<?= e($name ?? '') ?>"></label>
    <label><span><?= e(site_text('label_email')) ?></span><input type="email" name="email" maxlength="190" required value="<?= e($email ?? '') ?>"></label>
    <label><span><?= e(site_text('label_password')) ?></span><input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
    <p class="soft"><?= e(site_text('join_password_hint')) ?></p>
    <?php if (!empty($askTalk)): ?>
      <fieldset class="choices-set">
        <legend><?= e(site_text('talk_join_heading')) ?></legend>
        <p><?= e(site_text('talk_pref_intro')) ?></p>
        <?php include __DIR__ . '/../partials/talk-choices.php'; ?>
      </fieldset>
    <?php endif; ?>
    <?php if (!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <button type="submit"><?= e(site_text('cta_join')) ?></button>
  </form>
  <p><?= e(site_text('join_have_chair')) ?> <a href="<?= e(url('/login')) ?>"><?= e(site_text('cta_log_in')) ?></a></p>
</section>
