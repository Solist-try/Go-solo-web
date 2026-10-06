<?php $pageTitle = 'Share an experience · ' . copy('site_title'); ?>
<section class="frame section narrow">
  <h1>Something that happened</h1>
  <p>Out There is for real experiences. Write what you did, what you expected, what happened, and whether you would do it again.</p>
  <form method="post" action="<?= e(url('/out-there')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <label><span>Title</span><input type="text" name="title" maxlength="160" required value="<?= e($title ?? '') ?>"></label>
    <label><span>What I did</span><textarea name="what_i_did" maxlength="5000" required><?= e($what_i_did ?? '') ?></textarea></label>
    <label><span>What I expected</span><textarea name="expectations" maxlength="5000"><?= e($expectations ?? '') ?></textarea></label>
    <label><span>What happened</span><textarea name="what_happened" maxlength="5000" required><?= e($what_happened ?? '') ?></textarea></label>
    <label><span>Would I do it again</span><textarea name="would_do_again" maxlength="5000"><?= e($would_do_again ?? '') ?></textarea></label>
    <label><span>A photograph, if you have one</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>
    <?php if (!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <button type="submit">Share it</button>
  </form>
</section>
