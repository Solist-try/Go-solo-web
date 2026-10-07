<section class="frame hero">
  <div>
    <h1><?= e(site_text('hero_title')) ?></h1>
    <p class="lede"><?= e(site_text('hero_subhead')) ?></p>
    <p class="support"><?= e(site_text('hero_support')) ?></p>
    <div class="actions">
      <a class="button sage" href="<?= e(url('/join')) ?>"><?= e(site_text('hero_primary')) ?></a>
      <a class="button quiet" href="#how"><?= e(site_text('hero_secondary')) ?></a>
    </div>
    <p class="philosophy"><?= e(site_text('hero_philosophy')) ?></p>
  </div>
  <figure>
    <img class="photo hero-photo" src="<?= e(media(site_text('hero_image'))) ?>" alt="<?= e(site_text('hero_image_alt')) ?>">
  </figure>
</section>

<section class="band white">
  <div class="frame section split two">
    <div>
      <h2><?= e(site_text('freedom_heading')) ?></h2>
      <p class="callout"><?= e(site_text('freedom_body')) ?></p>
    </div>
    <figure>
      <img class="photo" src="<?= e(media(site_text('freedom_image'))) ?>" alt="<?= e(site_text('freedom_image_alt')) ?>">
    </figure>
  </div>
</section>

<section class="frame section" id="how">
  <h2><?= e(site_text('how_title')) ?></h2>
  <div class="cards two">
    <?php
    $steps = json_decode(site_text('how_steps'), true);
    if (!is_array($steps) || $steps === []) {
        $steps = [
            ['name' => 'Find a Seed', 'body' => 'A seed is a tiny future, not a task. Plant something small.', 'href' => '/seeds'],
            ['name' => 'Go Out There', 'body' => 'Try it in ordinary life. Take the trip. A Tuesday counts.', 'href' => '/out-there'],
            ['name' => 'Return to Campfire', 'body' => 'Talk about a question, an ordinary day, or something you are figuring out.', 'href' => '/campfire'],
            ['name' => 'Visit a Waypoint', 'body' => 'Sit with people in a similar part of life. Take your time.', 'href' => '/waypoints'],
        ];
    }
    $stepLinks = [
        '/seeds' => 'Explore Seeds',
        '/out-there' => 'Take The Trip',
        '/campfire' => 'Pull Up A Chair',
        '/waypoints' => 'Pull Up A Chair',
        '/reading' => 'Read The Story',
    ];
    foreach ($steps as $step):
        $href = (string) ($step['href'] ?? '');
    ?>
      <article class="card">
        <h3><?= e((string) ($step['name'] ?? '')) ?></h3>
        <p><?= e((string) ($step['body'] ?? '')) ?></p>
        <?php if ($href !== ''): ?>
          <p><a href="<?= e(url($href)) ?>"><?= e($stepLinks[$href] ?? 'Learn More') ?></a></p>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="frame section">
  <h2><?= e(site_text('home_seeds_title')) ?></h2>
  <p class="soft"><?= e(site_text('home_seeds_line')) ?></p>
  <div class="cards two">
    <a class="card sage stretch" href="<?= e(url('/seeds/same')) ?>"><h3>SAME</h3><p>Someone on a similar path. A steward can introduce you. There is no rush.</p></a>
    <a class="card sage stretch" href="<?= e(url('/seeds/skill-swap')) ?>"><h3>Skill Swap</h3><p>Learn something. Teach something.</p></a>
  </div>
  <p><a href="<?= e(url('/seeds')) ?>">Explore Seeds</a></p>
</section>

<section class="band mist">
  <div class="frame section">
    <h2><?= e(site_text('nav_reading')) ?></h2>
    <p>Practical notes for ordinary days. A trip, a meal, a question, a first try.</p>
    <p><a class="button" href="<?= e(url('/reading')) ?>">Read The Story</a></p>
  </div>
</section>

<section class="band clay">
  <div class="frame section">
    <h2><?= e(site_text('contact_headline')) ?></h2>
    <p>A question, an idea, or a story is welcome.</p>
    <p><a class="button" href="<?= e(url('/contact')) ?>"><?= e(site_text('contact_card_title')) ?></a></p>
  </div>
</section>
