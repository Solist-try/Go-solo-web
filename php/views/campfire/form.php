<?php $pageTitle = 'Start a conversation · ' . copy('site_title'); ?>
<section class="frame section narrow">
  <h1>Start a conversation</h1>
  <p>A question, an experience, an ordinary day, or something you are figuring out.</p>
  <form method="post" action="<?= e(url('/campfire')) ?>">
    <?= csrf_field() ?>
    <label><span>Title</span><input type="text" name="title" maxlength="160" required value="<?= e($title ?? '') ?>"></label>
    <label><span>What is on your mind?</span><textarea name="body" maxlength="5000" required><?= e($body ?? '') ?></textarea></label>
    <?php if (!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <button type="submit">Put it by the fire</button>
  </form>
</section>
