<?php
$readingDesk = $readingDesk ?? 'articles';
$readingSections = [
    'articles' => ['/steward/reading', 'Articles'],
    'categories' => ['/steward/reading/categories', 'Categories'],
    'layout' => ['/steward/reading/layout', 'Layout'],
];
?>
<nav class="reading-sections" aria-label="Reading Room">
  <?php foreach ($readingSections as $key => [$href, $label]): ?>
    <a href="<?= e(url($href)) ?>"<?= $readingDesk === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>
