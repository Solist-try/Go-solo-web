<?php $pageTitle = 'Reading Room · Steward Desk'; $desk = 'reading'; include __DIR__ . '/open.php'; ?>
<h1>Reading Room</h1>
<p><a class="button small" href="<?= e(url('/steward/reading/new')) ?>">Create article</a></p>
<h2>Categories</h2>
<?php foreach ($categories as $category): ?>
  <form class="card" method="post" action="<?= e(url('/steward/reading/category')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= e((string) $category['id']) ?>">
    <label><span>Title</span><input type="text" name="title" maxlength="120" required value="<?= e($category['title']) ?>"></label>
    <label><span>Line</span><input type="text" name="line" maxlength="255" value="<?= e($category['line']) ?>"></label>
    <label><span>Image description</span><input type="text" name="image_alt" maxlength="255" value="<?= e($category['image_alt']) ?>"></label>
    <label><span>Image</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>
    <div class="row-actions">
      <button class="small" type="submit">Save category</button>
    </div>
  </form>
  <form method="post" action="<?= e(url('/steward/reading/category-delete')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= e((string) $category['id']) ?>">
    <button class="quiet small" type="submit">Delete category</button>
  </form>
<?php endforeach; ?>
<form method="post" action="<?= e(url('/steward/reading/category')) ?>">
  <?= csrf_field() ?>
  <h2>New category</h2>
  <label><span>Title</span><input type="text" name="title" maxlength="120" required></label>
  <label><span>Line</span><input type="text" name="line" maxlength="255"></label>
  <button type="submit">Create category</button>
</form>
<?php if (function_exists('pin_ready') && !pin_ready()): ?><p class="soft"><?= e(site_text('pin_import')) ?></p><?php endif; ?>
<?php if (function_exists('pin_ready') && pin_ready() && !empty($featured)): ?>
  <h2><?= e(site_text('pin_featured')) ?></h2>
  <form method="post" action="<?= e(url('/steward/reading/feature')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="order">
    <?php foreach ($featured as $article): ?>
      <label><span><?= e($article['title']) ?></span>
        <input type="number" name="featured_order[<?= e((string) $article['id']) ?>]" value="<?= e((string) $article['featured_order']) ?>">
      </label>
    <?php endforeach; ?>
    <button class="small" type="submit"><?= e(site_text('pin_save_order')) ?></button>
  </form>
<?php endif; ?>
<h2>Articles</h2>
<ul class="list">
  <?php foreach ($articles as $article): ?>
    <li>
      <a href="<?= e(url('/steward/reading/' . $article['id'])) ?>"><?= e($article['title']) ?></a>
      <span class="soft"><?= e($article['category_title']) ?> · <?= e($article['status']) ?><?php if (!empty($article['featured'])): ?> · <?= e(site_text('pin_featured')) ?><?php endif; ?></span>
      <?php if (function_exists('pin_ready') && pin_ready()): ?>
        <form method="post" action="<?= e(url('/steward/reading/feature')) ?>" class="inline">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= e((string) $article['id']) ?>">
          <input type="hidden" name="action" value="<?= !empty($article['featured']) ? 'clear' : 'feature' ?>">
          <button class="quiet small" type="submit"<?= $article['status'] !== 'published' && empty($article['featured']) ? ' disabled' : '' ?>><?= e(!empty($article['featured']) ? site_text('pin_clear') : site_text('pin_feature')) ?></button>
        </form>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>
<?php include __DIR__ . '/close.php'; ?>
