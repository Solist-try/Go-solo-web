<?php
$editorMode = ($editor['mode'] ?? 'full') === 'compact' ? 'compact' : 'full';
$editorAction = (string) ($editor['action'] ?? '');
$editorEmbedded = !empty($editor['embedded']);
$editorClass = 'editor' . ($editorMode === 'compact' ? ' editor-compact' : '');
if (!empty($editor['workspace'])) {
    $editorClass .= ' editor-article';
}
$editorListLabel = (string) ($editor['list_label'] ?? site_text('editor_list'));
$editorNumberLabel = (string) ($editor['numbered_label'] ?? '1.');
$editorNumberName = (string) ($editor['numbered_name'] ?? site_text('editor_numbered'));
$editorQuoteLabel = (string) ($editor['quote_label'] ?? site_text('editor_quote'));
$editorHeadings = !empty($editor['headings']);
$editorPlaceholder = trim((string) ($editor['placeholder'] ?? ''));
$editorAttrs = ' data-link-ask="' . e(site_text('editor_link_ask')) . '"';
if ($editorPlaceholder !== '') {
    $editorAttrs .= ' data-placeholder="' . e($editorPlaceholder) . '"';
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
    <?php if (!$editorHeadings): ?>
      <button type="button" data-cmd="quote" aria-label="<?= e($editorQuoteLabel) ?>" title="<?= e($editorQuoteLabel) ?>"><?= e($editorQuoteLabel) ?></button>
      <button type="button" data-cmd="link" aria-label="<?= e(site_text('editor_link')) ?>" title="<?= e(site_text('editor_link')) ?>"><?= e(site_text('editor_link')) ?></button>
    <?php endif; ?>
  </div>
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
