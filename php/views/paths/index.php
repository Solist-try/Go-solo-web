<section class="frame section">
  <p class="kicker"><?= e(site_text('ground_nav')) ?></p>
  <h1><?= e(site_text('ground_paths_title')) ?></h1>
  <p><?= e(site_text('ground_paths_line')) ?></p>
  <?php if (empty($sections)): ?>
    <p class="soft"><?= e(site_text('ground_paths_empty')) ?></p>
  <?php endif; ?>
  <?php foreach ($sections as $section): ?>
    <h2><?php if (($section['href'] ?? '') !== ''): ?><a href="<?= e(url((string) $section['href'])) ?>"><?= e((string) $section['title']) ?></a><?php else: ?><?= e((string) $section['title']) ?><?php endif; ?></h2>
    <?php $groundPeople = $section['people']; $groundHeading = false; include __DIR__ . '/../partials/ground-people.php'; ?>
  <?php endforeach; ?>
</section>
