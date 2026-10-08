<?php foreach ($images ?? [] as $image): ?>
  <img class="photo story-photo" src="<?= e(media((string) $image['path'])) ?>" alt="<?= e((string) ($image['alt'] ?? '')) ?>">
<?php endforeach; ?>
