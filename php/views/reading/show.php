<?php $pageTitle = ($article['title'] ?? 'Reading') . ' · ' . site_text('site_title'); ?>
<section class="band mist">
  <div class="frame section narrow">
  <p class="kicker"><a href="<?= e(url('/reading')) ?>"><?= e($article['category_title']) ?></a></p>
  <h1 class="article-title"><?= e($article['title']) ?></h1>
  <?php if ($article['standfirst']): ?><p class="lede article-standfirst"><?= e($article['standfirst']) ?></p><?php endif; ?>
  <?php if (!empty($article['image_path'])): ?>
    <img class="photo support-photo" src="<?= e(media($article['image_path'])) ?>" alt="">
  <?php endif; ?>
  <?= reading_render_body((string) $article['body'], (int) $article['id']) ?>
  </div>
</section>
