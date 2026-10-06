<?php $pageTitle = 'Reading Room · ' . copy('site_title'); ?>
<section class="band mist">
  <div class="frame section">
  <h1>Reading Room</h1>
  <p>Practical notes for ordinary life. Not a blog, and not a funnel.</p>
  <?php if (!$categories): ?>
    <p>The shelf is empty for now.</p>
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
        <p class="soft">Nothing on this shelf yet.</p>
      <?php else: ?>
        <ul class="list">
          <?php foreach ($category['articles'] as $article): ?>
            <li><a href="<?= e(url('/reading/' . $article['slug'])) ?>"><?= e($article['title']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
  </div>
</section>
