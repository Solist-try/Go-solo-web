<?php foreach (['status', 'category', 'q', 'sort', 'dir'] as $key): ?>
<input type="hidden" name="<?= e($key) ?>" value="<?= e((string) ($filters[$key] ?? '')) ?>">
<?php endforeach; ?>
