<section class="band clay">
  <div class="frame section">
  <h1><?= e(site_text('contact_headline')) ?></h1>
  <div class="split two">
    <?php if (!empty($sent)): ?>
      <div class="card sage">
        <h2>I have it.</h2>
        <p>Thank you for writing. I'll read it.</p>
      </div>
    <?php else: ?>
      <form method="post" action="<?= e(url('/contact')) ?>">
        <?= csrf_field() ?>
        <label><span>Your name</span><input type="text" name="name" maxlength="80" required value="<?= e($name ?? '') ?>"></label>
        <label><span>Email</span><input type="email" name="email" maxlength="190" required value="<?= e($email ?? '') ?>"></label>
        <label><span>Note</span><textarea name="body" maxlength="4000" required><?= e($body ?? '') ?></textarea></label>
        <?php if (!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
        <button type="submit">Send the note</button>
      </form>
    <?php endif; ?>
    <aside class="card">
      <h2><?= e(site_text('contact_card_title')) ?></h2>
      <?= paragraphs(site_text('contact_body')) ?>
      <p class="philosophy" style="font-size:2.2rem"><?= e(site_text('founder_name')) ?></p>
      <p><a href="mailto:<?= e(site_text('founder_email')) ?>"><?= e(site_text('founder_email')) ?></a></p>
    </aside>
  </div>
  </div>
</section>
