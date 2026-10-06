<?php $pageTitle = 'Join · ' . copy('site_title'); ?>
<section class="frame section narrow">
  <h1>Join Go Solo</h1>
  <p>A chair is here if you want it. Nothing starts until you do.</p>
  <form method="post" action="<?= e(url('/join')) ?>">
    <?= csrf_field() ?>
    <label><span>What should we call you?</span><input type="text" name="name" maxlength="80" required value="<?= e($name ?? '') ?>"></label>
    <label><span>Email</span><input type="email" name="email" maxlength="190" required value="<?= e($email ?? '') ?>"></label>
    <label><span>Password</span><input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
    <p class="soft">At least 8 characters. You can change it later.</p>
    <?php if (!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <button type="submit">Join Go Solo</button>
  </form>
  <p>Already have a chair? <a href="<?= e(url('/login')) ?>">Log in</a></p>
</section>
