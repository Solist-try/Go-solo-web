<?php $pageTitle = 'Content · Steward Desk'; $desk = 'content'; include __DIR__ . '/open.php'; ?>
<h1>Content</h1>
<p class="soft">These are the words visitors see. Leave a box empty and save it to restore the original line. The site keeps showing that original if a line is missing.</p>
<p class="soft">
  <?php $first = true; foreach (content_groups() as $group => $fields): ?>
    <?php if (!$first): ?> · <?php endif; $first = false; ?>
    <a href="#<?= e(content_anchor($group)) ?>"><?= e($group) ?></a>
  <?php endforeach; ?>
</p>
<form method="post" action="<?= e(url('/steward/content')) ?>">
  <?= csrf_field() ?>
  <?php foreach (content_groups() as $group => $fields): ?>
    <h2 id="<?= e(content_anchor($group)) ?>"><?= e($group) ?></h2>
    <?php if ($group === 'Reading Room'): ?>
      <p class="soft">Shelf names, shelf lines, and article text are edited under Reading. Each one is already its own record.</p>
    <?php elseif ($group === 'Waypoints'): ?>
      <p class="soft">A chair’s own name, description, picture, sitting line, Food for Thought introduction, discussion prompt, button, and empty line are edited on that waypoint. The lines here are used when a chair leaves a field blank.</p>
    <?php elseif ($group === 'Seeds'): ?>
      <p class="soft">Support preference names stay tied to gardens members have already saved, so those names are not rewritten from here. Skill examples below are display copy.</p>
    <?php elseif ($group === 'Policies'): ?>
      <p class="soft">A link appears in the footer when its label has words. Clear the label to hide that link.</p>
    <?php endif; ?>
    <?php foreach ($fields as $key => $label): ?>
      <?php $value = site_text($key); ?>
      <label><span><?= e($label) ?></span>
        <?php if (long_setting($key, $value)): ?>
          <textarea name="<?= e($key) ?>" maxlength="8000"><?= e($value) ?></textarea>
        <?php else: ?>
          <input type="text" name="<?= e($key) ?>" maxlength="500" value="<?= e($value) ?>">
        <?php endif; ?>
      </label>
    <?php endforeach; ?>
    <?php if ($group === 'Homepage'): ?>
      <?php
      $steps = json_decode(site_text('how_steps'), true);
      if (!is_array($steps)) {
          $steps = [];
      }
      for ($i = 0; $i < 4; $i++):
          $step = $steps[$i] ?? ['name' => '', 'body' => '', 'href' => ''];
      ?>
        <label><span>Step <?= e((string) ($i + 1)) ?> name</span><input type="text" name="how_<?= e((string) ($i + 1)) ?>_name" maxlength="80" value="<?= e((string) ($step['name'] ?? '')) ?>"></label>
        <label><span>Step <?= e((string) ($i + 1)) ?> text</span><input type="text" name="how_<?= e((string) ($i + 1)) ?>_body" maxlength="300" value="<?= e((string) ($step['body'] ?? '')) ?>"></label>
        <label><span>Step <?= e((string) ($i + 1)) ?> link</span><input type="text" name="how_<?= e((string) ($i + 1)) ?>_href" maxlength="120" value="<?= e((string) ($step['href'] ?? '')) ?>"></label>
      <?php endfor; ?>
    <?php endif; ?>
  <?php endforeach; ?>
  <button type="submit">Save the words</button>
</form>
<?php include __DIR__ . '/close.php'; ?>
