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
<h2>Articles</h2>
<ul class="list">
  <?php foreach ($articles as $article): ?>
    <li>
      <a href="<?= e(url('/steward/reading/' . $article['id'])) ?>"><?= e($article['title']) ?></a>
      <span class="soft"><?= e($article['category_title']) ?> · <?= e($article['status']) ?></span>
    </li>
  <?php endforeach; ?>
</ul>
<?php include __DIR__ . '/close.php'; ?>
