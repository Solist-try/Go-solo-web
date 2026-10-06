<section class="frame hero">
  <div>
    <h1><?= e(copy('hero_title')) ?></h1>
    <p class="lede"><?= e(copy('hero_subhead')) ?></p>
    <p class="support"><?= e(copy('hero_support')) ?></p>
    <div class="actions">
      <a class="button sage" href="<?= e(url('/join')) ?>"><?= e(copy('hero_primary')) ?></a>
      <a class="button quiet" href="#how"><?= e(copy('hero_secondary')) ?></a>
    </div>
    <p class="philosophy"><?= e(copy('hero_philosophy')) ?></p>
  </div>
  <figure>
    <img class="photo hero-photo" src="<?= e(media(copy('hero_image'))) ?>" alt="<?= e(copy('hero_image_alt')) ?>">
  </figure>
</section>

<section class="frame section split two">
  <div>
    <h2><?= e(copy('freedom_heading')) ?></h2>
    <p><?= e(copy('freedom_body')) ?></p>
  </div>
  <figure>
    <img class="photo" src="<?= e(media(copy('freedom_image'))) ?>" alt="<?= e(copy('freedom_image_alt')) ?>">
  </figure>
</section>

<section class="frame section" id="how">
  <h2><?= e(copy('how_title')) ?></h2>
  <div class="cards two">
    <?php
    $steps = json_decode(copy('how_steps'), true);
    if (!is_array($steps) || $steps === []) {
        $steps = [
            ['name' => 'Find a Seed', 'body' => 'A seed is a tiny future, not a task. Plant something small.', 'href' => '/seeds'],
            ['name' => 'Go Out There', 'body' => 'Try it in ordinary life. A trip counts. A Tuesday counts.', 'href' => '/out-there'],
            ['name' => 'Return to Campfire', 'body' => 'Talk about what happened, and what you are still figuring out.', 'href' => '/campfire'],
            ['name' => 'Visit a Waypoint', 'body' => 'Sit with people navigating a similar part of life.', 'href' => '/waypoints'],
        ];
    }
    $tones = ['sage', 'clay', 'gold', 'mist'];
    foreach ($steps as $i => $step):
    ?>
      <article class="card <?= e($tones[$i % 4]) ?>">
        <h3><?= e((string) ($step['name'] ?? '')) ?></h3>
        <p><?= e((string) ($step['body'] ?? '')) ?></p>
        <?php if (!empty($step['href'])): ?>
          <p><a href="<?= e(url((string) $step['href'])) ?>">Come this way</a></p>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="frame section">
  <h2><?= e(copy('home_seeds_title')) ?></h2>
  <p class="soft"><?= e(copy('home_seeds_line')) ?></p>
  <div class="cards two">
    <a class="card sage stretch" href="<?= e(url('/seeds/same')) ?>"><h3>SAME</h3><p>Find somebody growing a similar future.</p></a>
    <a class="card clay stretch" href="<?= e(url('/seeds/skill-swap')) ?>"><h3>Skill Swap</h3><p>Learn something. Teach something.</p></a>
  </div>
</section>
