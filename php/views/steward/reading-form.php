<?php
$pageTitle = 'Article · Steward Desk';
$desk = 'reading';
$deskWide = true;
$creating = empty($article['id']);
include __DIR__ . '/open.php';
$existingImage = trim((string) ($article['image_path'] ?? ''));
$readingImagesReady = function_exists('reading_images_ready') && reading_images_ready();
$readingMedia = [];
if ($readingImagesReady && function_exists('reading_media_catalog')) {
    try {
        $readingMedia = reading_media_catalog((string) ($article['body'] ?? ''));
    } catch (Throwable $e) {
        $readingMedia = [];
    }
}
?>
<div class="reading-editor article-sheet" data-reading-preview data-preview-url="<?= e(url('/steward/reading/preview')) ?>" data-existing-image="<?= e($existingImage !== '' ? media($existingImage) : '') ?>">
  <h1><?= e($creating ? site_text('editor_article_create') : 'Edit article') ?></h1>
  <?php if (!empty($error)): ?><p class="error" id="reading-error" role="alert"><?= e($error) ?></p><?php endif; ?>
  <form class="reading-write" method="post" action="<?= e(url('/steward/reading/save')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="intent" value="<?= $creating ? 'create' : 'update' ?>">
    <input type="hidden" name="id" value="<?= e((string) ($article['id'] ?? 0)) ?>">
    <section class="article-details" aria-labelledby="article-details-title">
      <h2 id="article-details-title"><?= e(site_text('editor_details')) ?></h2>
      <label><span>Title</span><input type="text" name="title" maxlength="180" required value="<?= e($article['title'] ?? '') ?>"></label>
      <label><span>Standfirst</span><input type="text" name="standfirst" maxlength="255" value="<?= e($article['standfirst'] ?? '') ?>"></label>
      <label><span>Category</span>
        <select name="category_id">
          <?php foreach ($categories as $category): ?>
            <option value="<?= e((string) $category['id']) ?>"<?= (int) ($article['category_id'] ?? 0) === (int) $category['id'] ? ' selected' : '' ?>><?= e($category['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label><span>Slug</span><input type="text" name="slug" maxlength="120" value="<?= e($article['slug'] ?? '') ?>"></label>
      <label><span>Status</span>
        <select name="status">
          <option value="draft"<?= ($article['status'] ?? 'draft') === 'draft' ? ' selected' : '' ?>>Draft</option>
          <option value="published"<?= ($article['status'] ?? '') === 'published' ? ' selected' : '' ?>>Published</option>
        </select>
      </label>
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
    </section>
    <section class="article-content" aria-labelledby="article-content-title">
      <h2 id="article-content-title"><?= e(site_text('editor_content')) ?></h2>
      <div class="reading-modes" role="group" aria-label="<?= e(site_text('editor_preview')) ?>">
        <button type="button" data-reading-mode="write" aria-pressed="true"><?= e(site_text('editor_write')) ?></button>
        <button type="button" data-reading-mode="preview" aria-pressed="false"><?= e(site_text('editor_preview')) ?></button>
      </div>
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
            'numbered_label' => site_text('editor_numbered'),
            'quote_label' => site_text('editor_blockquote'),
            'images' => $readingImagesReady,
            'image_url' => url('/steward/reading/image'),
            'article_id' => (int) ($article['id'] ?? 0),
            'describedby' => !empty($error) ? 'reading-error' : '',
        ];
        include __DIR__ . '/../partials/editor.php';
      ?>
      <?php if (!$readingImagesReady): ?><p class="soft"><?= e(site_text('editor_image_unavailable')) ?></p><?php endif; ?>
      <script type="application/json" id="reading-media"><?= json_encode($readingMedia, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
      <div class="article-media">
        <label><span>Image</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>
        <?php if ($existingImage !== ''): ?>
          <img class="photo support-photo" src="<?= e(media($existingImage)) ?>" alt="">
        <?php endif; ?>
      </div>
      <div class="article-actions">
        <button type="submit" name="commit" value="draft"><?= e(site_text('editor_article_draft')) ?></button>
        <button type="submit" name="commit" value="publish"><?= e($creating ? site_text('editor_article_publish') : site_text('editor_article_update')) ?></button>
        <a class="button quiet" href="<?= e(url('/steward/reading')) ?>"><?= e(site_text('editor_article_return')) ?></a>
        <p class="editor-saving" data-saving-status="<?= e(site_text('editor_article_saving')) ?>" role="status" hidden></p>
      </div>
    </section>
  </form>
  <div class="reading-preview band mist" role="region" aria-label="<?= e(site_text('editor_preview')) ?>" aria-live="polite" hidden>
    <div class="narrow">
      <p class="kicker" data-preview="category"></p>
      <h1 class="article-title" data-preview="title"></h1>
      <p class="lede article-standfirst" data-preview="standfirst" hidden></p>
      <img class="photo support-photo" data-preview="image" alt="" hidden>
      <div data-preview="body"></div>
    </div>
  </div>
<?php if (!$creating): ?>
  <form class="reading-delete" method="post" action="<?= e(url('/steward/reading/delete')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= e((string) $article['id']) ?>">
    <label><input type="checkbox" name="yes" value="1"> Yes, delete this article</label>
    <button class="quiet" type="submit">Delete article</button>
  </form>
<?php endif; ?>
</div>
<?php include __DIR__ . '/close.php'; ?>
