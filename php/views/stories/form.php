<?php $pageTitle = site_text('out_there_form_title') . ' · ' . site_text('site_title'); ?>
<section class="frame section narrow">
  <h1><?= e(site_text('out_there_form_title')) ?></h1>
  <?php if (!empty($legacy)): ?>
    <p><?= e(site_text('out_there_legacy_help')) ?></p>
    <form method="post" action="<?= e(url('/out-there')) ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <label><span><?= e(site_text('label_title')) ?></span><input type="text" name="title" maxlength="160" required value="<?= e($title ?? '') ?>"></label>
      <label><span><?= e(site_text('out_there_did')) ?></span><textarea name="what_i_did" maxlength="5000" required><?= e($what_i_did ?? '') ?></textarea></label>
      <label><span><?= e(site_text('out_there_expected')) ?></span><textarea name="expectations" maxlength="5000"><?= e($expectations ?? '') ?></textarea></label>
      <label><span><?= e(site_text('out_there_happened')) ?></span><textarea name="what_happened" maxlength="5000" required><?= e($what_happened ?? '') ?></textarea></label>
      <label><span><?= e(site_text('out_there_again')) ?></span><textarea name="would_do_again" maxlength="5000"><?= e($would_do_again ?? '') ?></textarea></label>
      <label><span><?= e(site_text('label_photo')) ?></span><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>
      <?php if (!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
      <button type="submit"><?= e(site_text('cta_tell_story')) ?></button>
    </form>
  <?php else: ?>
    <?php include __DIR__ . '/../partials/editor.php'; ?>
  <?php endif; ?>
</section>
