<?php $pageTitle = ($article['title'] ?? 'Reading') . ' · ' . site_text('site_title'); ?>
<section class="band mist">
  <div class="frame section narrow">
  <p class="kicker"><a href="<?= e(url('/reading')) ?>"><?= e($article['category_title']) ?></a></p>
  <h1><?= e($article['title']) ?></h1>
  <?php if ($article['standfirst']): ?><p class="lede" style="font-size:1.6rem"><?= e($article['standfirst']) ?></p><?php endif; ?>
  <?php if (!empty($article['image_path'])): ?>
    <img class="photo story-photo" src="<?= e(media($article['image_path'])) ?>" alt="">
  <?php endif; ?>
  <?= paragraphs((string) $article['body']) ?>
  </div>
</section>
