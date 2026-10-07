<?php $pageTitle = 'Pull up a chair · ' . site_text('site_title'); ?>
<section class="frame section narrow">
  <h1>Pull up a chair</h1>
  <p>Talk about something you're wondering about, something that happened, or something you're still working through.</p>
  <form method="post" action="<?= e(url('/campfire')) ?>">
    <?= csrf_field() ?>
    <label><span>Title</span><input type="text" name="title" maxlength="160" required value="<?= e($title ?? '') ?>"></label>
    <label><span>What is on your mind?</span><textarea name="body" maxlength="5000" required><?= e($body ?? '') ?></textarea></label>
    <?php if (!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <button type="submit">Put it by the fire</button>
  </form>
</section>
