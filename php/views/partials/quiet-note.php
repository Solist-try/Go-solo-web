<?php
$quietKeys = [
    'decline' => 'quiet_decline',
    'close' => 'quiet_close',
    'declined' => 'quiet_declined',
];
$quietKey = $quietKeys[$quietNote] ?? '';
?>
<?php if ($quietKey !== ''): ?>
<div class="frame">
  <aside class="quiet-note" data-quiet-note>
    <div><?= paragraphs(site_text($quietKey)) ?></div>
    <button class="quiet small" type="button" data-quiet-hide><?= e(site_text('quiet_note_hide')) ?></button>
  </aside>
</div>
<script>
document.querySelector('[data-quiet-hide]')?.addEventListener('click', function () {
  var note = this.closest('[data-quiet-note]');
  if (note) {
    note.remove();
  }
});
</script>
<?php endif; ?>
