<?php $pageTitle = ($post['title'] ?? 'Campfire') . ' · ' . site_text('site_title'); ?>
<section class="frame section">
  <p class="kicker"><a href="<?= e(url('/campfire')) ?>">Campfire</a></p>
  <h1><?= e($post['title']) ?></h1>
  <p class="soft"><a href="<?= e(url('/members/' . $post['user_id'])) ?>"><?= e($post['display_name'] ?: 'A member') ?></a> · <?= e(nice_date($post['created_at'])) ?></p>
  <?php if (!empty($post['hidden'])): ?><p class="flash">This conversation is hidden from the house. You can still see it.</p><?php endif; ?>
  <?= paragraphs((string) $post['body']) ?>
  <h2>Around the table</h2>
  <?php if (!$comments): ?><p class="soft">No one has pulled a chair closer yet.</p><?php endif; ?>
  <div class="stack">
    <?php foreach ($comments as $comment): ?>
      <article class="card">
        <?php if (!empty($comment['hidden'])): ?><p class="soft">Hidden from the house.</p><?php endif; ?>
        <p class="soft"><a href="<?= e(url('/members/' . $comment['user_id'])) ?>"><?= e($comment['display_name'] ?: 'A member') ?></a> · <?= e(nice_date($comment['created_at'])) ?></p>
        <?= paragraphs((string) $comment['body']) ?>
        <?php if ($currentUser && (int) $currentUser['id'] !== (int) $comment['user_id']): ?>
          <form method="post" action="<?= e(url('/comments/' . $comment['id'] . '/report')) ?>">
            <?= csrf_field() ?>
            <label><span>Report this note</span><textarea name="reason" maxlength="1000" required></textarea></label>
            <button class="quiet small" type="submit">Send a report</button>
          </form>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
  <?php if ($currentUser && empty($post['locked'])): ?>
    <form method="post" action="<?= e(url('/campfire/' . $post['id'] . '/comment')) ?>">
      <?= csrf_field() ?>
      <label><span>Add to the conversation</span><textarea name="body" maxlength="4000" required></textarea></label>
      <button type="submit">Share it</button>
    </form>
  <?php elseif (!empty($post['locked'])): ?>
    <p class="soft">This conversation is resting. New notes are closed.</p>
  <?php endif; ?>
  <?php if ($currentUser && (int) $currentUser['id'] !== (int) $post['user_id']): ?>
    <form method="post" action="<?= e(url('/campfire/' . $post['id'] . '/report')) ?>">
      <?= csrf_field() ?>
      <label><span>If something here is not right, tell the steward</span><textarea name="reason" maxlength="1000" required></textarea></label>
      <button class="quiet small" type="submit">Send a report</button>
    </form>
  <?php endif; ?>
</section>
