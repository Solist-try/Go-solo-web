<?php
$pageTitle = 'Article · Steward Desk';
$desk = 'reading';
$creating = empty($article['id']);
include __DIR__ . '/open.php';
$existingImage = trim((string) ($article['image_path'] ?? ''));
?>
<div class="reading-editor" data-reading-preview data-existing-image="<?= e($existingImage !== '' ? media($existingImage) : '') ?>">
  <h1><?= e($creating ? site_text('editor_article_create') : 'Edit article') ?></h1>
  <?php if (!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
  <div class="reading-modes" role="group" aria-label="<?= e(site_text('editor_preview')) ?>">
    <button type="button" data-reading-mode="write" aria-pressed="true"><?= e(site_text('editor_write')) ?></button>
    <button type="button" data-reading-mode="preview" aria-pressed="false"><?= e(site_text('editor_preview')) ?></button>
  </div>
  <form class="reading-write" method="post" action="<?= e(url('/steward/reading/save')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="intent" value="<?= $creating ? 'create' : 'update' ?>">
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
    <?php
      $editor = [
          'embedded' => true,
          'headings' => true,
          'workspace' => true,
          'body' => (string) ($article['body'] ?? ''),
          'body_label' => 'Article',
          'help' => site_text('editor_article_help'),
          'placeholder' => site_text('editor_article_start'),
          'list_label' => site_text('editor_bullets'),
      ];
      include __DIR__ . '/../partials/editor.php';
    ?>
    <label><span>Image</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>
    <?php if ($existingImage !== ''): ?>
      <img class="photo support-photo" src="<?= e(media($existingImage)) ?>" alt="">
    <?php endif; ?>
    <?php if (function_exists('pin_ready') && pin_ready()): ?>
      <label>
        <input type="checkbox" name="featured" value="1"<?= !empty($article['featured']) ? ' checked' : '' ?>>
        <?= e(site_text('pin_feature')) ?>
      </label>
      <label><span><?= e(site_text('pin_order')) ?></span>
        <input type="number" name="featured_order" value="<?= e((string) ($article['featured_order'] ?? 0)) ?>">
      </label>
      <p class="soft"><?= e(site_text('pin_feature_note')) ?></p>
    <?php endif; ?>
    <label><span>Status</span>
      <select name="status">
        <option value="draft"<?= ($article['status'] ?? 'draft') === 'draft' ? ' selected' : '' ?>>Draft</option>
        <option value="published"<?= ($article['status'] ?? '') === 'published' ? ' selected' : '' ?>>Published</option>
      </select>
    </label>
    <button type="submit"><?= e($creating ? site_text('editor_article_create') : site_text('editor_article_save')) ?></button>
  </form>
  <div class="reading-preview band mist" hidden>
    <div class="narrow">
      <p class="kicker" data-preview="category"></p>
      <h1 class="article-title" data-preview="title"></h1>
      <p class="lede article-standfirst" data-preview="standfirst" hidden></p>
      <img class="photo support-photo" data-preview="image" alt="" hidden>
      <div class="writing" data-preview="body"></div>
    </div>
  </div>
<?php if (!$creating): ?>
  <form class="reading-write" method="post" action="<?= e(url('/steward/reading/delete')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= e((string) $article['id']) ?>">
    <label><input type="checkbox" name="yes" value="1"> Yes, delete this article</label>
    <button class="quiet" type="submit">Delete article</button>
  </form>
<?php endif; ?>
</div>
<?php include __DIR__ . '/close.php'; ?>
