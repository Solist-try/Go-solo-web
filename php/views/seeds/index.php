<section class="frame section">
  <h1><?= e(site_text('seeds_title')) ?></h1>
  <p class="lede"><?= e(site_text('seeds_line')) ?></p>
  <p><?= e(site_text('seeds_support')) ?></p>
  <p><?= e(site_text('seeds_accountability')) ?></p>
  <p><?= e(site_text('seeds_learning')) ?></p>
  <p><?= e(site_text('seeds_small')) ?></p>
  <div class="cards two">
    <article class="card sage seed-card">
      <h2>SAME</h2>
      <p class="soft meaning">Support, accountability, mutual empowerment.</p>
      <p>Looking for someone on a similar path? A steward can introduce you. There is no rush.</p>
      <div class="examples">
        <p class="kicker">Support you can ask for</p>
        <p class="examples-label">Examples</p>
        <ul class="chips">
          <?php foreach (support_choices() as $choice): ?><li><?= e($choice) ?></li><?php endforeach; ?>
        </ul>
      </div>
      <p class="card-action"><a class="button" href="<?= e(url('/seeds/same')) ?>">Ask for an introduction</a></p>
    </article>
    <article class="card sage seed-card">
      <h2>Skill Swap</h2>
      <p>Learn something. Teach something.</p>
      <div class="examples">
        <p class="kicker">Examples</p>
        <ul class="chips">
          <?php foreach (['Teach crochet', 'Learn Spanish', 'Teach gardening', 'Learn budgeting', 'Teach writing', 'Learn DIY'] as $example): ?>
            <li><?= e($example) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <p class="card-action"><a class="button" href="<?= e(url('/seeds/skill-swap')) ?>">Offer or request a skill</a></p>
    </article>
  </div>
  <?php if ($seeds): ?>
    <h2>See What's Growing</h2>
    <ul class="list">
      <?php foreach ($seeds as $seed): ?>
        <li><a href="<?= e(url('/seeds/' . $seed['slug'])) ?>"><?= e($seed['title']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>
