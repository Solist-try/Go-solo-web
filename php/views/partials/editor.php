<?php
$editorMode = ($editor['mode'] ?? 'full') === 'compact' ? 'compact' : 'full';
$editorAction = (string) ($editor['action'] ?? '');
$editorEmbedded = !empty($editor['embedded']);
$editorClass = 'editor' . ($editorMode === 'compact' ? ' editor-compact' : '');
if (!empty($editor['letter'])) {
    $editorClass .= ' editor-letter';
}
if (!empty($editor['workspace'])) {
    $editorClass .= ' editor-article';
}
$editorListLabel = (string) ($editor['list_label'] ?? site_text('editor_list'));
$editorNumberLabel = (string) ($editor['numbered_label'] ?? '1.');
$editorNumberName = (string) ($editor['numbered_name'] ?? site_text('editor_numbered'));
$editorQuoteLabel = (string) ($editor['quote_label'] ?? site_text('editor_quote'));
$editorHeadings = !empty($editor['headings']);
$editorImages = !empty($editor['images']);
$editorPlaceholder = trim((string) ($editor['placeholder'] ?? ''));
$editorAttrs = ' data-link-ask="' . e(site_text('editor_link_ask')) . '"';
if ($editorPlaceholder !== '') {
    $editorAttrs .= ' data-placeholder="' . e($editorPlaceholder) . '"';
}
if ($editorImages) {
    $imageMax = function_exists('reading_inline_limit') ? reading_inline_limit() : 0;
    $editorAttrs .= ' data-image-url="' . e((string) ($editor['image_url'] ?? '')) . '"';
    $editorAttrs .= ' data-article-id="' . e((string) ($editor['article_id'] ?? '0')) . '"';
    $editorAttrs .= ' data-image-max="' . e((string) $imageMax) . '"';
    $editorAttrs .= ' data-image-limit="' . e(function_exists('site_line') ? site_line('editor_image_limit', ['count' => (string) $imageMax]) : site_text('editor_image_limit')) . '"';
    $editorAttrs .= ' data-image-gap="' . e(site_text('editor_image_gap')) . '"';
    $editorAttrs .= ' data-image-words="' . e(site_text('editor_image_words')) . '"';
    $editorAttrs .= ' data-image-alt-label="' . e(site_text('editor_image_alt')) . '"';
    $editorAttrs .= ' data-image-alt-help="' . e(site_text('editor_image_alt_help')) . '"';
    $editorAttrs .= ' data-image-alt-ready="' . e(site_text('editor_image_alt_ready')) . '"';
    $editorAttrs .= ' data-image-alt-missing="' . e(site_text('editor_image_alt_missing')) . '"';
    $editorAttrs .= ' data-image-caption-label="' . e(site_text('editor_image_caption')) . '"';
    $editorAttrs .= ' data-image-label="' . e(site_text('editor_image_label')) . '"';
    $editorAttrs .= ' data-image-replace-label="' . e(site_text('editor_image_replace')) . '"';
    $editorAttrs .= ' data-image-remove-label="' . e(site_text('editor_image_remove')) . '"';
    $editorAttrs .= ' data-image-remove-ask="' . e(site_text('editor_image_remove_ask')) . '"';
    $editorAttrs .= ' data-image-up="' . e(site_text('editor_image_up')) . '"';
    $editorAttrs .= ' data-image-down="' . e(site_text('editor_image_down')) . '"';
    $editorAttrs .= ' data-image-moved-up="' . e(site_text('editor_image_moved_up')) . '"';
    $editorAttrs .= ' data-image-moved-down="' . e(site_text('editor_image_moved_down')) . '"';
    $editorAttrs .= ' data-image-needs="' . e(site_text('editor_image_needs')) . '"';
    $editorAttrs .= ' data-image-failed="' . e(site_text('editor_image_failed')) . '"';
    $editorAttrs .= ' data-image-missing="' . e(site_text('editor_image_missing')) . '"';
    $editorAttrs .= ' data-image-retry="' . e(site_text('editor_image_retry')) . '"';
    $editorAttrs .= ' data-image-size="' . e(site_text('msg_picture_size')) . '"';
    $editorAttrs .= ' data-image-use="' . e(site_text('editor_image_use')) . '"';
    $editorAttrs .= ' data-image-upload="' . e(site_text('editor_image_upload')) . '"';
    $editorAttrs .= ' data-image-place="' . e(site_text('editor_image_place')) . '"';
    $editorAttrs .= ' data-image-cancel="' . e(site_text('editor_image_cancel')) . '"';
}
?>
<?php if ($editorEmbedded): ?>
<div class="<?= e($editorClass) ?>" data-editor<?= $editorAttrs ?>>
<?php else: ?>
<form class="<?= e($editorClass) ?>" method="post" action="<?= e($editorAction) ?>" enctype="multipart/form-data" data-editor<?= $editorAttrs ?>>
  <?= csrf_field() ?>
  <?php if ($editorMode === 'full'): ?>
    <label><span><?= e((string) ($editor['title_label'] ?? 'Title')) ?></span>
      <input type="text" name="title" maxlength="160" required value="<?= e((string) ($editor['title'] ?? '')) ?>">
    </label>
  <?php endif; ?>
<?php endif; ?>
  <?php if (!empty($editor['help'])): ?><p class="soft"><?= e((string) $editor['help']) ?></p><?php endif; ?>
  <label><span><?= e((string) ($editor['body_label'] ?? 'Write here')) ?></span></label>
  <div class="editor-tools" role="toolbar" aria-label="Writing">
    <button type="button" data-cmd="bold" aria-label="<?= e(site_text('editor_bold')) ?>" title="<?= e(site_text('editor_bold')) ?>"<?= $editorHeadings ? ' aria-pressed="false"' : '' ?>><strong>B</strong></button>
    <button type="button" data-cmd="italic" aria-label="<?= e(site_text('editor_italic')) ?>" title="<?= e(site_text('editor_italic')) ?>"<?= $editorHeadings ? ' aria-pressed="false"' : '' ?>><em>I</em></button>
    <?php if ($editorHeadings): ?>
      <button type="button" data-cmd="heading" aria-pressed="false" aria-label="<?= e(site_text('editor_heading')) ?>" title="<?= e(site_text('editor_heading')) ?>"><?= e(site_text('editor_heading')) ?></button>
      <button type="button" data-cmd="subheading" aria-pressed="false" aria-label="<?= e(site_text('editor_subheading')) ?>" title="<?= e(site_text('editor_subheading')) ?>"><?= e(site_text('editor_subheading')) ?></button>
      <button type="button" data-cmd="quote" aria-pressed="false" aria-label="<?= e($editorQuoteLabel) ?>" title="<?= e($editorQuoteLabel) ?>"><?= e($editorQuoteLabel) ?></button>
      <button type="button" data-cmd="link" aria-pressed="false" aria-label="<?= e(site_text('editor_link')) ?>" title="<?= e(site_text('editor_link')) ?>"><?= e(site_text('editor_link')) ?></button>
    <?php endif; ?>
    <button type="button" data-cmd="insertUnorderedList" aria-label="<?= e($editorListLabel) ?>" title="<?= e($editorListLabel) ?>"<?= $editorHeadings ? ' aria-pressed="false"' : '' ?>><?= e($editorListLabel) ?></button>
    <button type="button" data-cmd="insertOrderedList" aria-label="<?= e($editorNumberName) ?>" title="<?= e($editorNumberName) ?>"<?= $editorHeadings ? ' aria-pressed="false"' : '' ?>><?= e($editorNumberLabel) ?></button>
    <?php if ($editorImages): ?>
      <button type="button" data-cmd="image" aria-label="<?= e(site_text('editor_image')) ?>" title="<?= e(site_text('editor_image')) ?>"><?= e(site_text('editor_image')) ?></button>
    <?php endif; ?>
    <?php if (!$editorHeadings): ?>
      <button type="button" data-cmd="quote" aria-label="<?= e($editorQuoteLabel) ?>" title="<?= e($editorQuoteLabel) ?>"><?= e($editorQuoteLabel) ?></button>
      <button type="button" data-cmd="link" aria-label="<?= e(site_text('editor_link')) ?>" title="<?= e(site_text('editor_link')) ?>"><?= e(site_text('editor_link')) ?></button>
    <?php endif; ?>
  </div>
  <?php if ($editorImages): ?>
    <input class="editor-image-file" type="file" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
    <p class="error" data-image-error role="alert" hidden></p>
    <p class="soft" id="reading-image-limit" data-image-limit-note hidden></p>
    <div class="editor-image-panel" data-image-choices hidden>
      <div data-image-library></div>
      <div class="writing-figure-actions">
        <button type="button" data-image-upload><?= e(site_text('editor_image_upload')) ?></button>
        <button type="button" data-image-choices-cancel><?= e(site_text('editor_image_cancel')) ?></button>
      </div>
    </div>
    <div class="editor-image-panel" data-image-draft hidden>
      <p class="writing-figure-label"><?= e(site_text('editor_image_label')) ?></p>
      <img data-image-draft-preview alt="" hidden>
      <label><span><?= e(site_text('editor_image_alt')) ?></span>
        <input type="text" data-image-draft-alt maxlength="255" autocomplete="off">
        <span class="soft"><?= e(site_text('editor_image_alt_help')) ?></span>
      </label>
      <label><span><?= e(site_text('editor_image_caption')) ?></span>
        <input type="text" data-image-draft-caption maxlength="300" autocomplete="off">
      </label>
      <div class="writing-figure-actions">
        <button type="button" data-image-place disabled><?= e(site_text('editor_image_place')) ?></button>
        <button type="button" data-image-draft-cancel><?= e(site_text('editor_image_cancel')) ?></button>
      </div>
    </div>
    <p class="editor-live" data-image-live role="status"></p>
  <?php endif; ?>
  <div class="editor-surface" contenteditable="true" role="textbox" aria-multiline="true" aria-label="<?= e((string) ($editor['body_label'] ?? 'Write here')) ?>"<?php if (!empty($editor['describedby'])): ?> aria-describedby="<?= e((string) $editor['describedby']) ?>"<?php endif; ?>></div>
  <textarea class="editor-source" name="<?= e((string) ($editor['body_name'] ?? 'body')) ?>" maxlength="20000"><?= e((string) ($editor['body'] ?? '')) ?></textarea>
<?php if (!$editorEmbedded): ?>
  <?php if (!empty($editor['show_image'])): ?>
    <label><span><?= e((string) ($editor['image_label'] ?? 'A photograph, if you have one')) ?></span>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
    </label>
    <label><span><?= e(site_text('label_photo_alt')) ?></span>
      <input type="text" name="image_alt" maxlength="255" value="<?= e((string) ($editor['image_alt'] ?? '')) ?>">
    </label>
  <?php endif; ?>
  <?php if (!empty($editor['error'])): ?><p class="error"><?= e((string) $editor['error']) ?></p><?php endif; ?>
  <button type="submit"><?= e((string) ($editor['submit'] ?? 'Share it')) ?></button>
</form>
<?php else: ?>
</div>
<?php endif; ?>
<script src="<?= e(url('/assets/js/editor.js')) ?>" defer></script>
