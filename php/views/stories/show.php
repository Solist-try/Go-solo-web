<?php $pageTitle = ($story['title'] ?? 'Out There') . ' · ' . site_text('site_title'); ?>
<section class="frame section">
  <p class="kicker"><a href="<?= e(url('/out-there')) ?>">Out There</a></p>
  <h1><?= e($story['title']) ?></h1>
  <p class="soft"><a href="<?= e(url('/members/' . $story['user_id'])) ?>"><?= e($story['display_name'] ?: 'A member') ?></a> · <?= e(nice_date($story['created_at'])) ?></p>
  <?php if (!empty($story['hidden'])): ?><p class="flash">This story is hidden from the house. You can still see it.</p><?php endif; ?>
  <?php if (!empty($story['image_path'])): ?>
    <img class="photo story-photo" src="<?= e(media($story['image_path'])) ?>" alt="">
  <?php endif; ?>
  <h2>What I did</h2>
  <?= paragraphs((string) $story['what_i_did']) ?>
  <?php if (trim((string) $story['expectations']) !== ''): ?>
    <h2>What I expected</h2>
    <?= paragraphs((string) $story['expectations']) ?>
  <?php endif; ?>
  <h2>What happened</h2>
  <?= paragraphs((string) $story['what_happened']) ?>
  <?php if (trim((string) $story['would_do_again']) !== ''): ?>
    <h2>Would I do it again</h2>
    <?= paragraphs((string) $story['would_do_again']) ?>
  <?php endif; ?>
  <?php if ($currentUser && (int) $currentUser['id'] !== (int) $story['user_id']): ?>
    <form method="post" action="<?= e(url('/out-there/' . $story['id'] . '/report')) ?>">
      <?= csrf_field() ?>
      <label><span>If something here is not right, tell the steward</span><textarea name="reason" maxlength="1000" required></textarea></label>
      <button class="quiet small" type="submit">Send a report</button>
    </form>
  <?php endif; ?>
</section>
