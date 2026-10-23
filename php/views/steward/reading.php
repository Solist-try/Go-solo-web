<?php
$pageTitle = 'Reading Room · Steward Desk';
$desk = 'reading';
$readingDesk = 'articles';
$filters = $filters ?? ['status' => 'all', 'category' => 0, 'q' => '', 'sort' => 'edited', 'dir' => 'desc'];
include __DIR__ . '/open.php';
$tabs = ['all' => 'All', 'published' => 'Published', 'draft' => 'Drafts'];
$columns = [
    'title' => 'Title',
    'category' => 'Category',
    'status' => 'Status',
    'edited' => 'Last edited',
];
$sortNotes = [
    'edited' => ['desc' => 'newest first', 'asc' => 'oldest first'],
    'title' => ['asc' => 'A to Z', 'desc' => 'Z to A'],
    'category' => ['asc' => 'A to Z', 'desc' => 'Z to A'],
    'status' => ['asc' => 'drafts first', 'desc' => 'published first'],
];
?>
<h1>Reading Room</h1>
<?php include __DIR__ . '/reading-nav.php'; ?>
<p><a class="button small" href="<?= e(url('/steward/reading/new')) ?>">Create article</a></p>
<nav class="reading-status" aria-label="Article status">
  <?php foreach ($tabs as $code => $label): ?>
    <a href="<?= e(url(reading_list_url($filters, ['status' => $code]))) ?>"<?= $filters['status'] === $code ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>
<form class="reading-filters" method="get" action="<?= e(url('/steward/reading')) ?>">
  <?php if ($filters['status'] !== 'all'): ?><input type="hidden" name="status" value="<?= e($filters['status']) ?>"><?php endif; ?>
  <?php if ($filters['sort'] !== 'edited' || $filters['dir'] !== 'desc'): ?>
    <input type="hidden" name="sort" value="<?= e($filters['sort']) ?>">
    <input type="hidden" name="dir" value="<?= e($filters['dir']) ?>">
  <?php endif; ?>
  <label><span>Category</span>
    <select name="category">
      <option value="0">All categories</option>
      <?php foreach ($categories as $category): ?>
        <option value="<?= e((string) $category['id']) ?>"<?= (int) $filters['category'] === (int) $category['id'] ? ' selected' : '' ?>><?= e($category['title']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label><span>Title</span><input type="search" name="q" maxlength="120" value="<?= e($filters['q']) ?>"></label>
  <button class="small" type="submit">Search</button>
</form>
<?php if (!$articles): ?>
  <p>No articles in this view.</p>
<?php else: ?>
  <div class="table-wrap">
    <table class="reading-table">
      <thead>
        <tr>
          <?php foreach ($columns as $column => $label): ?>
            <?php $active = $filters['sort'] === $column; ?>
            <th scope="col"<?= $active ? ' aria-sort="' . e($filters['dir'] === 'asc' ? 'ascending' : 'descending') . '"' : '' ?>>
              <a href="<?= e(url(reading_sort_url($filters, $column))) ?>"><?= e($label) ?><?php if ($active): ?> <span class="soft"><?= e($sortNotes[$column][$filters['dir']] ?? '') ?></span><?php endif; ?></a>
            </th>
          <?php endforeach; ?>
          <?php if (!empty($pins)): ?><th scope="col">Featured</th><?php endif; ?>
          <th scope="col">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($articles as $article): ?>
          <?php $published = $article['status'] === 'published'; ?>
          <tr>
            <td><a href="<?= e(url('/steward/reading/' . $article['id'])) ?>"><?= e($article['title']) ?></a></td>
            <td><?= e($article['category_title']) ?></td>
            <td><span class="reading-state reading-state-<?= e($article['status']) ?>"><?= e($published ? 'Published' : 'Draft') ?></span></td>
            <td><time datetime="<?= e((string) $article['updated_at']) ?>"><?= e(nice_date($article['updated_at'])) ?></time></td>
            <?php if (!empty($pins)): ?><td><?= !empty($article['featured']) ? 'Featured' : '' ?></td><?php endif; ?>
            <td>
              <div class="row-actions">
                <a class="button quiet small" href="<?= e(url('/steward/reading/' . $article['id'])) ?>">Edit</a>
                <?php if ($published && trim((string) $article['slug']) !== ''): ?>
                  <a class="button quiet small" href="<?= e(url('/reading/' . $article['slug'])) ?>">View</a>
                <?php endif; ?>
                <form method="post" action="<?= e(url('/steward/reading/status')) ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= e((string) $article['id']) ?>">
                  <input type="hidden" name="next" value="<?= e($published ? 'draft' : 'published') ?>">
                  <?php include __DIR__ . '/reading-keep.php'; ?>
                  <button class="quiet small" type="submit"><?= e($published ? 'Unpublish' : 'Publish') ?></button>
                </form>
                <?php if (!empty($pins) && ($published || !empty($article['featured']))): ?>
                  <form method="post" action="<?= e(url('/steward/reading/feature')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e((string) $article['id']) ?>">
                    <input type="hidden" name="action" value="<?= !empty($article['featured']) ? 'clear' : 'feature' ?>">
                    <?php include __DIR__ . '/reading-keep.php'; ?>
                    <button class="quiet small" type="submit"<?= !$published && empty($article['featured']) ? ' disabled' : '' ?>><?= e(!empty($article['featured']) ? site_text('pin_clear') : site_text('pin_feature')) ?></button>
                  </form>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                  <a class="button quiet small" href="<?= e(url('/steward/reading/' . $article['id'])) ?>#delete-article">Delete</a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php include __DIR__ . '/close.php'; ?>
