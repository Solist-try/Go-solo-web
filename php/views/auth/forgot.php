<?php $pageTitle = 'Forgot password · ' . copy('site_title'); ?>
<section class="frame section narrow">
  <h1>Forgot password</h1>
  <p>Write the email on your account. If it is here, a reset note will be sent. It lasts for two hours.</p>
  <form method="post" action="<?= e(url('/forgot')) ?>">
    <?= csrf_field() ?>
    <label><span>Email</span><input type="email" name="email" maxlength="190" required value="<?= e($email ?? '') ?>"></label>
    <button type="submit">Send a reset note</button>
  </form>
  <p><a href="<?= e(url('/login')) ?>">Back to log in</a></p>
</section>
