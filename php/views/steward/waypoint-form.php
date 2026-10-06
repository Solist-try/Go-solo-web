<?php $pageTitle = 'Waypoint · Steward Desk'; $desk = 'waypoints'; include __DIR__ . '/open.php'; ?>
<h1><?= !empty($waypoint['id']) ? 'Edit Waypoint' : 'Create Waypoint' ?></h1>
<form method="post" action="<?= e(url('/steward/waypoints/save')) ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= e((string) ($waypoint['id'] ?? 0)) ?>">
  <label><span>Title</span><input type="text" name="title" maxlength="160" required value="<?= e($waypoint['title'] ?? '') ?>"></label>
  <label><span>Slug</span><input type="text" name="slug" maxlength="80" value="<?= e($waypoint['slug'] ?? '') ?>"></label>
  <label><span>Description</span><textarea name="description" maxlength="5000"><?= e($waypoint['description'] ?? '') ?></textarea></label>
  <label><span>Cover image</span><input type="file" name="cover" accept="image/jpeg,image/png,image/webp,image/gif"></label>
  <?php if (!empty($waypoint['cover_path'])): ?><p class="soft"><?= e($waypoint['cover_path']) ?></p><?php endif; ?>
  <label><input type="checkbox" name="archived" value="1"<?= !empty($waypoint['archived']) ? ' checked' : '' ?>> Archive</label>
  <button type="submit">Save waypoint</button>
</form>
<?php include __DIR__ . '/close.php'; ?>
