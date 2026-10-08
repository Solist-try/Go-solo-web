<?php $pageTitle = ($policyTitle ?? '') . ' · ' . site_text('site_title'); ?>
<section class="frame section narrow">
  <h1><?= e($policyTitle ?? '') ?></h1>
  <?= paragraphs($policyBody ?? '') ?>
</section>
