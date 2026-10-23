<?php
$pageTitle = 'Reading Room layout · Steward Desk';
$desk = 'reading';
$readingDesk = 'layout';
include __DIR__ . '/open.php';
?>
<h1>Reading Room</h1>
<?php include __DIR__ . '/reading-nav.php'; ?>
<h2>Layout</h2>
<p>Category images alternate with the text. The first category in this order places its image on the right, the next on the left, and so on. A category without an image uses the full width. The page keeps that order each time it loads.</p>
<?php if (function_exists('pin_ready') && !pin_ready()): ?><p class="soft"><?= e(site_text('pin_import')) ?></p><?php endif; ?>
<h2>Category order</h2>
<?php if (!$categories): ?>
  <p>No categories yet. Add them under Categories.</p>
<?php else: ?>
  <form method="post" action="<?= e(url('/steward/reading/category-order')) ?>">
    <?= csrf_field() ?>
    <?php foreach ($categories as $category): ?>
      <?php $count = (int) ($category['article_count'] ?? 0); ?>
      <label><span><?= e($category['title']) ?> · <?= e($count === 1 ? '1 article' : $count . ' articles') ?><?php if (trim((string) $category['image_path']) === ''): ?> · no image<?php endif; ?></span>
        <input type="number" name="sort_order[<?= e((string) $category['id']) ?>]" min="0" max="9999" value="<?= e((string) $category['sort_order']) ?>">
      </label>
    <?php endforeach; ?>
    <button class="small" type="submit">Save order</button>
  </form>
<?php endif; ?>
<?php if (function_exists('pin_ready') && pin_ready()): ?>
  <h2><?= e(site_text('pin_featured')) ?></h2>
  <?php if (empty($featured)): ?>
    <p class="soft">No featured articles yet. Feature a published article from Articles.</p>
  <?php else: ?>
    <form method="post" action="<?= e(url('/steward/reading/feature')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="return" value="layout">
      <input type="hidden" name="action" value="order">
      <?php foreach ($featured as $article): ?>
        <label><span><?= e($article['title']) ?></span>
          <input type="number" name="featured_order[<?= e((string) $article['id']) ?>]" value="<?= e((string) $article['featured_order']) ?>">
        </label>
      <?php endforeach; ?>
      <button class="small" type="submit"><?= e(site_text('pin_save_order')) ?></button>
    </form>
  <?php endif; ?>
<?php endif; ?>
<?php include __DIR__ . '/close.php'; ?>
