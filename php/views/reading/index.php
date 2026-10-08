<?php $pageTitle = 'Reading Room · ' . site_text('site_title'); ?>
<section class="band mist">
  <div class="frame section">
  <h1>Reading Room</h1>
  <p>Practical notes for ordinary days. A trip, a meal, a question, a first try.</p>
  <?php if (!$categories): ?>
    <p>The shelves are still bare.</p>
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
        <p class="soft">This shelf is still bare.</p>
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
