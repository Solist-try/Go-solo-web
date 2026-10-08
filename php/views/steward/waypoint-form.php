<?php $pageTitle = 'Waypoint · Steward Desk'; $desk = 'waypoints'; include __DIR__ . '/open.php'; ?>
<h1><?= !empty($waypoint['id']) ? 'Edit Waypoint' : 'Create Waypoint' ?></h1>
<form method="post" action="<?= e(url('/steward/waypoints/save')) ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= e((string) ($waypoint['id'] ?? 0)) ?>">
  <label><span>Title</span><input type="text" name="title" maxlength="160" required value="<?= e($waypoint['title'] ?? '') ?>"></label>
  <label><span>Slug</span><input type="text" name="slug" maxlength="80" value="<?= e($waypoint['slug'] ?? '') ?>"></label>
  <label><span>Description</span><textarea name="description" maxlength="5000"><?= e($waypoint['description'] ?? '') ?></textarea></label>
  <?php if (waypoint_copy_ready()): ?>
    <label><span>Sitting line</span><input type="text" name="intro_line" maxlength="255" value="<?= e($waypoint['intro_line'] ?? '') ?>"></label>
    <p class="soft">Leave this blank to keep the original line on the first three chairs, or to show no sitting line on a new chair.</p>
    <label><span>Food for Thought introduction</span><textarea name="food_intro" maxlength="5000"><?= e($waypoint['food_intro'] ?? '') ?></textarea></label>
    <label><span>Discussion prompt</span><textarea name="discussion_prompt" maxlength="2000"><?= e($waypoint['discussion_prompt'] ?? '') ?></textarea></label>
    <label><span>Discussion button</span><input type="text" name="discussion_cta" maxlength="80" value="<?= e($waypoint['discussion_cta'] ?? '') ?>"></label>
    <label><span>Empty discussion line</span><input type="text" name="discussion_empty" maxlength="500" value="<?= e($waypoint['discussion_empty'] ?? '') ?>"></label>
    <p class="soft">Leave a discussion field blank to use the shared line from Content.</p>
  <?php else: ?>
    <p class="soft">Import sql/update-content.sql to edit this chair’s sitting line, Food for Thought introduction, discussion prompt, button, and empty line.</p>
  <?php endif; ?>
  <label><span>Cover image</span><input type="file" name="cover" accept="image/jpeg,image/png,image/webp,image/gif"></label>
  <?php if (!empty($waypoint['cover_path'])): ?><p class="soft"><?= e($waypoint['cover_path']) ?></p><?php endif; ?>
  <label><input type="checkbox" name="archived" value="1"<?= !empty($waypoint['archived']) ? ' checked' : '' ?>> Archive</label>
  <button type="submit">Save waypoint</button>
</form>
<?php if (!empty($waypoint['id']) && !empty($readings)): ?>
  <h2>Food for Thought</h2>
  <p class="soft">Connect pieces from the reading room. A number sets the order. Uncheck a piece to take it off this chair.</p>
  <form method="post" action="<?= e(url('/steward/waypoints/readings')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="waypoint_id" value="<?= e((string) $waypoint['id']) ?>">
    <?php foreach ($readings as $article): ?>
      <?php $chosen = isset($linked[(int) $article['id']]); ?>
      <div class="row-actions">
        <label>
          <input type="checkbox" name="reading_id[]" value="<?= e((string) $article['id']) ?>"<?= $chosen ? ' checked' : '' ?>>
          <?= e($article['title']) ?><?php if ($article['status'] !== 'published'): ?> <span class="soft">(<?= e($article['status']) ?>)</span><?php endif; ?>
        </label>
        <label><span class="soft">Order</span>
          <input type="text" name="sort_order[<?= e((string) $article['id']) ?>]" value="<?= e((string) ($linked[(int) $article['id']] ?? '')) ?>" maxlength="4">
        </label>
      </div>
    <?php endforeach; ?>
    <button type="submit">Save the reading room pieces</button>
  </form>
<?php endif; ?>
<?php include __DIR__ . '/close.php'; ?>
