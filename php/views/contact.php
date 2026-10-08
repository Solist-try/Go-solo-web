<section class="band clay">
  <div class="frame section">
  <h1><?= e(site_text('contact_headline')) ?></h1>
  <div class="split two">
    <?php if (!empty($sent)): ?>
      <div class="card sage">
        <h2><?= e(site_text('contact_thanks_title')) ?></h2>
        <p><?= e(site_text('contact_thanks_body')) ?></p>
      </div>
    <?php else: ?>
      <form method="post" action="<?= e(url('/contact')) ?>">
        <?= csrf_field() ?>
        <label><span><?= e(site_text('contact_name')) ?></span><input type="text" name="name" maxlength="80" required value="<?= e($name ?? '') ?>"></label>
        <label><span><?= e(site_text('contact_email')) ?></span><input type="email" name="email" maxlength="190" required value="<?= e($email ?? '') ?>"></label>
        <label><span><?= e(site_text('contact_note')) ?></span><textarea name="body" maxlength="4000" required><?= e($body ?? '') ?></textarea></label>
        <?php if (!empty($error)): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
        <button type="submit"><?= e(site_text('cta_send_note')) ?></button>
      </form>
    <?php endif; ?>
    <aside class="card">
      <h2><?= e(site_text('contact_card_title')) ?></h2>
      <?= paragraphs(site_text('contact_body')) ?>
      <p><?= e(site_text('contact_reach')) ?><br><a href="mailto:<?= e(site_text('founder_email')) ?>"><?= e(site_text('founder_email')) ?></a></p>
      <p class="founder"><?= e(site_text('label_founder')) ?><br><?= e(site_text('founder_name')) ?></p>
    </aside>
  </div>
  </div>
</section>
