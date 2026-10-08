<?php $pageTitle = site_text('notices_title') . ' · ' . site_text('site_title'); ?>
<section class="frame section narrow">
  <h1><?= e(site_text('notices_title')) ?></h1>
  <p class="soft"><?= e(site_text('notices_intro')) ?></p>
  <?php if (!$notices): ?>
    <p><?= e(site_text('empty_notices')) ?></p>
  <?php else: ?>
    <ul class="list">
      <?php foreach ($notices as $notice): ?>
        <li>
          <?php if ($notice['href']): ?><a href="<?= e(url($notice['href'])) ?>"><?= e($notice['body']) ?></a><?php else: ?><?= e($notice['body']) ?><?php endif; ?>
          <span class="soft"> · <?= e(nice_date($notice['created_at'])) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>
