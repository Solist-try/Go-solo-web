<?php $pageTitle = 'Article · Steward Desk'; $desk = 'reading'; include __DIR__ . '/open.php'; ?>
<h1><?= !empty($article['id']) ? 'Edit article' : 'Create article' ?></h1>
<form method="post" action="<?= e(url('/steward/reading/save')) ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= e((string) ($article['id'] ?? 0)) ?>">
  <label><span>Title</span><input type="text" name="title" maxlength="180" required value="<?= e($article['title'] ?? '') ?>"></label>
  <label><span>Slug</span><input type="text" name="slug" maxlength="120" value="<?= e($article['slug'] ?? '') ?>"></label>
  <label><span>Category</span>
    <select name="category_id">
      <?php foreach ($categories as $category): ?>
        <option value="<?= e((string) $category['id']) ?>"<?= (int) ($article['category_id'] ?? 0) === (int) $category['id'] ? ' selected' : '' ?>><?= e($category['title']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label><span>Standfirst</span><input type="text" name="standfirst" maxlength="255" value="<?= e($article['standfirst'] ?? '') ?>"></label>
  <label><span>Article</span><textarea name="body" maxlength="20000"><?= e($article['body'] ?? '') ?></textarea></label>
  <p class="soft">Leave a blank line between paragraphs.</p>
  <label><span>Image</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>
  <label><span>Status</span>
    <select name="status">
      <option value="draft"<?= ($article['status'] ?? '') === 'draft' ? ' selected' : '' ?>>Draft</option>
      <option value="published"<?= ($article['status'] ?? 'published') === 'published' ? ' selected' : '' ?>>Published</option>
    </select>
  </label>
  <button type="submit">Save article</button>
</form>
<?php if (!empty($article['id'])): ?>
  <form method="post" action="<?= e(url('/steward/reading/delete')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= e((string) $article['id']) ?>">
    <label><input type="checkbox" name="yes" value="1"> Yes, delete this article</label>
    <button class="quiet" type="submit">Delete article</button>
  </form>
<?php endif; ?>
<?php include __DIR__ . '/close.php'; ?>
