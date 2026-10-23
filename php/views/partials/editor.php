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
    <button type="button" data-cmd="bold" aria-label="<?= e(site_text('editor_bold')) ?>" title="<?= e(site_text('editor_bold')) ?>"><strong>B</strong></button>
    <button type="button" data-cmd="italic" aria-label="<?= e(site_text('editor_italic')) ?>" title="<?= e(site_text('editor_italic')) ?>"><em>I</em></button>
    <?php if (!empty($editor['headings'])): ?>
      <button type="button" data-cmd="heading" aria-label="<?= e(site_text('editor_heading')) ?>" title="<?= e(site_text('editor_heading')) ?>"><?= e(site_text('editor_heading')) ?></button>
      <button type="button" data-cmd="subheading" aria-label="<?= e(site_text('editor_subheading')) ?>" title="<?= e(site_text('editor_subheading')) ?>"><?= e(site_text('editor_subheading')) ?></button>
    <?php endif; ?>
    <button type="button" data-cmd="insertUnorderedList" aria-label="<?= e($editorListLabel) ?>" title="<?= e($editorListLabel) ?>"><?= e($editorListLabel) ?></button>
    <button type="button" data-cmd="insertOrderedList" aria-label="<?= e(site_text('editor_numbered')) ?>" title="<?= e(site_text('editor_numbered')) ?>"><?= e($editorNumberLabel) ?></button>
    <button type="button" data-cmd="quote" aria-label="<?= e(site_text('editor_quote')) ?>" title="<?= e(site_text('editor_quote')) ?>"><?= e(site_text('editor_quote')) ?></button>
    <button type="button" data-cmd="link" aria-label="<?= e(site_text('editor_link')) ?>" title="<?= e(site_text('editor_link')) ?>"><?= e(site_text('editor_link')) ?></button>
  </div>
  <div class="editor-surface" contenteditable="true" role="textbox" aria-multiline="true" aria-label="<?= e((string) ($editor['body_label'] ?? 'Write here')) ?>"></div>
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
