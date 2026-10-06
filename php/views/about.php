<section class="frame section">
  <p class="kicker"><?= e(copy('nav_about')) ?></p>
  <h1><?= e(copy('about_heading')) ?></h1>
  <div class="support"><?= paragraphs(copy('about_intro')) ?></div>
  <div class="split two" style="margin-top:2rem">
    <figure>
      <img class="photo" src="<?= e(media('/assets/images/founder-chair.svg')) ?>" alt="A quiet drawing of someone sitting with a cup">
    </figure>
    <div>
      <h2><?= e(copy('about_founder_heading')) ?></h2>
      <?= paragraphs(copy('about_founder_body')) ?>
    </div>
  </div>
  <h2><?= e(copy('about_belief_heading')) ?></h2>
  <?= paragraphs(copy('about_belief_body')) ?>
  <div class="card sage">
    <h2><?= e(copy('about_hello_heading')) ?></h2>
    <?= paragraphs(copy('about_hello_body')) ?>
    <p class="philosophy" style="font-size:2rem;margin-bottom:0.2rem"><?= e(copy('founder_name')) ?></p>
    <p><a href="mailto:<?= e(copy('founder_email')) ?>"><?= e(copy('founder_email')) ?></a></p>
  </div>
  <h2><?= e(copy('about_close_heading')) ?></h2>
  <p><?= e(copy('about_close_body')) ?></p>
</section>
