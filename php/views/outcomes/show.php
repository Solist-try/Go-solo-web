<?php $pageTitle = site_text('life_outcome_invite') . ' · ' . site_text('site_title'); ?>
<section class="frame section narrow">
  <p class="kicker"><a href="<?= e(url('/profile')) ?>"><?= e(site_text('life_path_heading')) ?></a></p>
  <h1><?= e(site_text('life_outcome_invite')) ?></h1>
  <?php if (!$mine && (string) $outcome['consent'] !== 'published' && (string) $outcome['share'] !== 'garden'): ?>
    <p class="soft"><?= e(site_text('life_outcome_private_note')) ?></p>
  <?php endif; ?>
  <?php if (trim((string) $outcome['body']) !== ''): ?>
    <h2><?= e(site_text('life_outcome_preview')) ?></h2>
    <?= paragraphs((string) $outcome['body']) ?>
  <?php endif; ?>
  <?php if (trim((string) ($outcome['public_body'] ?? '')) !== ''): ?>
    <h2><?= e(site_text('life_outcome_preview')) ?></h2>
    <?= paragraphs((string) $outcome['public_body']) ?>
  <?php endif; ?>
  <p class="soft"><?= e((string) $outcome['share']) ?> · <?= e((string) $outcome['consent']) ?></p>
  <?php if ($mine): ?>
    <?php
      $outcomeType = (string) $outcome['subject_type'];
      $outcomeId = (int) $outcome['subject_id'];
      $back = '/outcomes/' . (int) $outcome['id'];
      include __DIR__ . '/../partials/life-outcome.php';
    ?>
  <?php endif; ?>
</section>
