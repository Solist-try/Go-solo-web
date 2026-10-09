<section class="frame section">
  <h1><?= e(site_text('seeds_title')) ?></h1>
  <p class="lede"><?= e(site_text('seeds_line')) ?></p>
  <p><?= e(site_text('seeds_support')) ?></p>
  <p><?= e(site_text('seeds_accountability')) ?></p>
  <p><?= e(site_text('seeds_learning')) ?></p>
  <p><?= e(site_text('seeds_small')) ?></p>
  <div class="cards two">
    <article class="card sage seed-card">
      <h2><?= e(site_text('seeds_same_title')) ?></h2>
      <p class="soft meaning"><?= e(site_text('seeds_same_meaning')) ?></p>
      <p><?= e(site_text('seeds_same_body')) ?></p>
      <div class="examples">
        <p class="kicker"><?= e(site_text('seeds_support_heading')) ?></p>
        <p class="examples-label"><?= e(site_text('seeds_examples_label')) ?></p>
        <ul class="chips">
          <?php foreach (support_choices() as $choice): ?><li><?= e($choice) ?></li><?php endforeach; ?>
        </ul>
      </div>
      <p class="card-action"><a class="button" href="<?= e(url('/seeds/same')) ?>"><?= e(site_text('cta_ask_intro')) ?></a></p>
    </article>
    <article class="card sage seed-card">
      <h2><?= e(site_text('seeds_skill_title')) ?></h2>
      <p><?= e(site_text('seeds_skill_body')) ?></p>
      <div class="examples">
        <p class="kicker"><?= e(site_text('seeds_skill_heading')) ?></p>
        <p class="examples-label"><?= e(site_text('seeds_examples_label')) ?></p>
        <ul class="chips">
          <?php foreach (site_lines('seeds_skill_examples') as $example): ?>
            <li><?= e($example) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <p class="card-action"><a class="button" href="<?= e(url('/seeds/skill-swap')) ?>"><?= e(site_text('cta_offer_skill')) ?></a></p>
    </article>
  </div>
  <?php if ($seeds): ?>
    <h2><?= e(site_text('seeds_growing_title')) ?></h2>
    <ul class="list">
      <?php foreach ($seeds as $seed): ?>
        <li><a href="<?= e(url('/seeds/' . $seed['slug'])) ?>"><?= e($seed['title']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>
