<section class="frame section">
  <p class="kicker"><?= e(site_text('nav_about')) ?></p>
  <h1><?= e(site_text('about_heading')) ?></h1>
  <div class="support"><?= paragraphs(site_text('about_intro')) ?></div>
  <div class="split two" style="margin-top:2rem">
    <figure>
      <img class="photo" src="<?= e(media('/assets/images/founder-chair.svg')) ?>" alt="A quiet drawing of someone sitting with a cup">
    </figure>
    <div>
      <h2><?= e(site_text('about_founder_heading')) ?></h2>
      <?= paragraphs(site_text('about_founder_body')) ?>
    </div>
  </div>
  <h2><?= e(site_text('about_belief_heading')) ?></h2>
  <?= paragraphs(site_text('about_belief_body')) ?>
  <div class="card clay">
    <h2><?= e(site_text('about_hello_heading')) ?></h2>
    <?= paragraphs(site_text('about_hello_body')) ?>
    <p class="philosophy" style="font-size:2rem;margin-bottom:0.2rem"><?= e(site_text('founder_name')) ?></p>
    <p><a href="mailto:<?= e(site_text('founder_email')) ?>"><?= e(site_text('founder_email')) ?></a></p>
  </div>
  <h2><?= e(site_text('about_close_heading')) ?></h2>
  <p><?= e(site_text('about_close_body')) ?></p>
</section>
