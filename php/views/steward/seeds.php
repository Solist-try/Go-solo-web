<?php $pageTitle = 'Seeds · Steward Desk'; $desk = 'seeds'; include __DIR__ . '/open.php'; ?>
<h1>Seeds</h1>
<p>A seed is a tiny future. The pages named <em>same</em> and <em>skill-swap</em> stay in place.</p>
<form method="post" action="<?= e(url('/steward/seeds/save')) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? 0)) ?>">
  <label><span>Title</span><input type="text" name="title" maxlength="160" required value="<?= e($edit['title'] ?? '') ?>"></label>
  <label><span>Slug</span><input type="text" name="slug" maxlength="80" value="<?= e($edit['slug'] ?? '') ?>"></label>
  <label><span>Description</span><textarea name="description" maxlength="4000"><?= e($edit['description'] ?? '') ?></textarea></label>
  <label><span>Prompt</span><input type="text" name="prompt" maxlength="255" value="<?= e($edit['prompt'] ?? '') ?>"></label>
  <label><span>Kind</span>
    <select name="kind">
      <?php foreach (['practice', 'same', 'skill-swap'] as $kind): ?>
        <option value="<?= e($kind) ?>"<?= ($edit['kind'] ?? '') === $kind ? ' selected' : '' ?>><?= e($kind) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label><span>Category</span><input type="text" name="category" maxlength="80" value="<?= e($edit['category'] ?? '') ?>"></label>
  <label><input type="checkbox" name="archived" value="1"<?= !empty($edit['archived']) ? ' checked' : '' ?>> Archive</label>
  <button type="submit"><?= !empty($edit['id']) ? 'Save seed' : 'Create seed' ?></button>
</form>
<div class="table-wrap">
  <table>
    <thead><tr><th>Title</th><th>Kind</th><th>Planted</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($seeds as $seed): ?>
        <tr>
          <td><?= e($seed['title']) ?><?php if ($seed['archived']): ?> <span class="soft">archived</span><?php endif; ?></td>
          <td><?= e($seed['kind']) ?></td>
          <td><?= e((string) $seed['planted']) ?></td>
          <td><a href="<?= e(url('/steward/seeds?id=' . $seed['id'])) ?>">Edit</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/close.php'; ?>
