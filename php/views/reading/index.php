<?php $pageTitle = site_text('reading_title') . ' · ' . site_text('site_title'); ?>
<section class="band mist">
  <div class="frame section">
  <h1><?= e(site_text('reading_title')) ?></h1>
  <p><?= e(site_text('reading_intro')) ?></p>
  <?php if (!empty($featured)): ?>
    <article class="reading-shelf">
      <p class="shelf-label"><?= e(site_text('pin_featured')) ?></p>
      <div class="previews">
        <?php foreach ($featured as $article): ?>
          <a href="<?= e(url('/reading/' . $article['slug'])) ?>">
            <strong><?= e($article['title']) ?></strong>
            <?php if (trim((string) $article['standfirst']) !== ''): ?><span><?= e($article['standfirst']) ?></span><?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </article>
  <?php endif; ?>
  <?php if (!$categories): ?>
    <p><?= e(site_text('empty_shelves')) ?></p>
  <?php endif; ?>
  <?php foreach ($categories as $category): ?>
    <article class="reading-shelf">
      <header class="shelf-head">
        <p class="shelf-label"><?= e($category['title']) ?></p>
        <?php if (trim((string) $category['line']) !== ''): ?><p class="shelf-line"><?= e($category['line']) ?></p><?php endif; ?>
        <?php if ($category['image_path']): ?>
          <img class="photo shelf-photo" src="<?= e(media($category['image_path'])) ?>" alt="<?= e($category['image_alt']) ?>">
        <?php endif; ?>
      </header>
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
