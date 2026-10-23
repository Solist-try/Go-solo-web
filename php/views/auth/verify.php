<?php $pageTitle = site_text('verify_title') . ' · ' . site_text('site_title'); ?>
<section class="frame section narrow">
  <h1><?= e(site_text('verify_title')) ?></h1>
  <p><?= e(site_text('msg_verify_link')) ?></p>
  <?php if ($currentUser): ?>
    <p><a href="<?= e(url('/account')) ?>"><?= e(site_text('cta_resend_verify')) ?></a></p>
  <?php else: ?>
    <p><a href="<?= e(url('/login')) ?>"><?= e(site_text('cta_log_in')) ?></a></p>
  <?php endif; ?>
</section>
