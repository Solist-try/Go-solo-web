<section class="frame section">
  <h1><?= e(copy('seeds_title')) ?></h1>
  <p class="lede"><?= e(copy('seeds_line')) ?></p>
  <p><?= e(copy('seeds_support')) ?></p>
  <p><?= e(copy('seeds_accountability')) ?></p>
  <p><?= e(copy('seeds_learning')) ?></p>
  <p><?= e(copy('seeds_small')) ?></p>
  <div class="cards two">
    <article class="card sage">
      <h2>SAME</h2>
      <p>Support<br>Accountability<br>Mutual<br>Empowerment</p>
      <p>You can be open to someone on a similar stretch. A steward suggests a match. You can take your time.</p>
      <p class="kicker">Support you can ask for</p>
      <ul class="chips">
        <?php foreach (support_choices() as $choice): ?><li><?= e($choice) ?></li><?php endforeach; ?>
      </ul>
      <p><a class="button sage" href="<?= e(url('/seeds/same')) ?>">Be open to a partner</a></p>
    </article>
    <article class="card sage">
      <h2>Skill Swap</h2>
      <p>Learn something.</p>
      <p>Teach something.</p>
      <ul class="chips">
        <?php foreach (['Teach crochet', 'Learn Spanish', 'Teach gardening', 'Learn budgeting', 'Teach writing', 'Learn DIY'] as $example): ?>
          <li><?= e($example) ?></li>
        <?php endforeach; ?>
      </ul>
      <p><a class="button sage" href="<?= e(url('/seeds/skill-swap')) ?>">Offer help, or ask for it</a></p>
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
