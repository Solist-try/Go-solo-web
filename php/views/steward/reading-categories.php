<?php
$pageTitle = 'Reading Room categories · Steward Desk';
$desk = 'reading';
$readingDesk = 'categories';
include __DIR__ . '/open.php';
?>
<h1>Reading Room</h1>
<?php include __DIR__ . '/reading-nav.php'; ?>
<h2>Categories</h2>
<p class="soft">The description sits directly under the category heading on the Reading Room page. Lower numbers appear first.</p>
<?php if (!$categories): ?><p>No categories yet.</p><?php endif; ?>
<?php foreach ($categories as $category): ?>
  <?php $count = (int) ($category['article_count'] ?? 0); ?>
  <form class="card" method="post" action="<?= e(url('/steward/reading/category')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= e((string) $category['id']) ?>">
    <p class="soft"><?= e($count === 1 ? '1 article' : $count . ' articles') ?></p>
    <?php if (trim((string) $category['image_path']) !== ''): ?>
      <img class="reading-manage-photo" src="<?= e(media($category['image_path'])) ?>" alt="<?= e($category['image_alt']) ?>">
    <?php endif; ?>
    <label><span>Name</span><input type="text" name="title" maxlength="120" required value="<?= e($category['title']) ?>"></label>
    <label><span>Description</span><input type="text" name="line" maxlength="255" value="<?= e($category['line']) ?>"></label>
    <label><span>Display order</span><input type="number" name="sort_order" min="0" max="9999" value="<?= e((string) $category['sort_order']) ?>"></label>
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
<form method="post" action="<?= e(url('/steward/reading/category')) ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <h2>New category</h2>
  <label><span>Name</span><input type="text" name="title" maxlength="120" required></label>
  <label><span>Description</span><input type="text" name="line" maxlength="255"></label>
  <label><span>Display order</span><input type="number" name="sort_order" min="0" max="9999"></label>
  <label><span>Image description</span><input type="text" name="image_alt" maxlength="255"></label>
  <label><span>Image</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>
  <button type="submit">Create category</button>
</form>
<?php include __DIR__ . '/close.php'; ?>
