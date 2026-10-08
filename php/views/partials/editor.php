<?php
$editorMode = ($editor['mode'] ?? 'full') === 'compact' ? 'compact' : 'full';
$editorAction = (string) ($editor['action'] ?? '');
?>
<form class="editor<?= $editorMode === 'compact' ? ' editor-compact' : '' ?>" method="post" action="<?= e($editorAction) ?>" enctype="multipart/form-data" data-editor>
  <?= csrf_field() ?>
  <?php if ($editorMode === 'full'): ?>
    <label><span><?= e((string) ($editor['title_label'] ?? 'Title')) ?></span>
      <input type="text" name="title" maxlength="160" required value="<?= e((string) ($editor['title'] ?? '')) ?>">
    </label>
  <?php endif; ?>
  <?php if (!empty($editor['help'])): ?><p class="soft"><?= e((string) $editor['help']) ?></p><?php endif; ?>
  <label><span><?= e((string) ($editor['body_label'] ?? 'Write here')) ?></span></label>
  <div class="editor-tools" role="toolbar" aria-label="Writing">
    <button type="button" data-cmd="bold"><strong>B</strong></button>
    <button type="button" data-cmd="italic"><em>I</em></button>
    <button type="button" data-cmd="insertUnorderedList">List</button>
    <button type="button" data-cmd="insertOrderedList">1.</button>
    <button type="button" data-cmd="quote">Quote</button>
    <button type="button" data-cmd="link">Link</button>
  </div>
  <div class="editor-surface" contenteditable="true" role="textbox" aria-multiline="true"></div>
  <textarea class="editor-source" name="body" maxlength="20000"><?= e((string) ($editor['body'] ?? '')) ?></textarea>
  <?php if (!empty($editor['show_image'])): ?>
    <label><span><?= e((string) ($editor['image_label'] ?? 'A photograph, if you have one')) ?></span>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
    </label>
    <label><span>A short description of the photograph</span>
      <input type="text" name="image_alt" maxlength="255" value="<?= e((string) ($editor['image_alt'] ?? '')) ?>">
    </label>
  <?php endif; ?>
  <?php if (!empty($editor['error'])): ?><p class="error"><?= e((string) $editor['error']) ?></p><?php endif; ?>
  <button type="submit"><?= e((string) ($editor['submit'] ?? 'Share it')) ?></button>
</form>
<script src="<?= e(url('/assets/js/editor.js')) ?>" defer></script>
