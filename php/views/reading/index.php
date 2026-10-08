<?php $pageTitle = site_text('reading_title') . ' · ' . site_text('site_title'); ?>
<section class="band mist">
  <div class="frame section">
  <h1><?= e(site_text('reading_title')) ?></h1>
  <p><?= e(site_text('reading_intro')) ?></p>
  <?php if (!$categories): ?>
    <p><?= e(site_text('empty_shelves')) ?></p>
  <?php endif; ?>
  <?php foreach ($categories as $category): ?>
    <article style="margin-top:2.4rem">
      <div class="split two">
        <div>
          <h2><?= e($category['title']) ?></h2>
          <p><?= e($category['line']) ?></p>
        </div>
        <?php if ($category['image_path']): ?>
          <img class="photo" src="<?= e(media($category['image_path'])) ?>" alt="<?= e($category['image_alt']) ?>">
        <?php endif; ?>
      </div>
      <?php if (!$category['articles']): ?>
        <p class="soft"><?= e(site_text('empty_shelf')) ?></p>
      <?php else: ?>
        <div class="previews">
          <?php foreach ($category['articles'] as $article): ?>
            <a href="<?= e(url('/reading/' . $article['slug'])) ?>">
              <strong><?= e($article['title']) ?></strong>
              <?php if (trim((string) $article['standfirst']) !== ''): ?><span><?= e($article['standfirst']) ?></span><?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
  </div>
</section>
