<?php $pageTitle = site_text('reading_title') . ' · ' . site_text('site_title'); ?>
<section class="band mist">
  <div class="frame section">
  <h1><?= e(site_text('reading_title')) ?></h1>
  <p><?= e(site_text('reading_intro')) ?></p>
  <?php if (!empty($featured)): ?>
    <article class="reading-note">
      <p class="kicker"><?= e(site_text('pin_featured')) ?></p>
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
  <?php foreach ($categories as $index => $category): ?>
    <?php
      $image = trim((string) ($category['image_path'] ?? ''));
      $alt = trim((string) ($category['image_alt'] ?? ''));
      if ($alt === '') {
          $alt = (string) $category['title'];
      }
      $shelfClass = 'reading-shelf' . ($image !== '' ? ' has-photo' : ' is-text');
      if ($image !== '' && ((int) $index % 2) === 1) {
          $shelfClass .= ' image-left';
      }
    ?>
    <article class="<?= e($shelfClass) ?>">
      <div class="shelf-copy">
        <h2><?= e($category['title']) ?></h2>
        <?php if (trim((string) $category['line']) !== ''): ?><p class="shelf-line"><?= e($category['line']) ?></p><?php endif; ?>
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
      </div>
      <?php if ($image !== ''): ?>
        <img class="photo shelf-photo" src="<?= e(media($image)) ?>" alt="<?= e($alt) ?>">
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
  </div>
</section>
