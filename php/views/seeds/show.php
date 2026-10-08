<section class="frame section">
  <?php
    $kindLabel = ['same' => site_text('seeds_same_title'), 'skill-swap' => site_text('seeds_skill_title'), 'practice' => site_text('seeds_kind_practice')][$seed['kind']] ?? site_text('seeds_kind_practice');
  ?>
  <p class="kicker"><?= e($kindLabel) ?></p>
  <h1><?= e($seed['title']) ?></h1>
  <p><?= e($seed['description']) ?></p>
  <div class="card sage">
    <p class="kicker"><?= e(site_text('seeds_growing_label')) ?></p>
    <p class="lede" style="font-size:1.8rem"><?= e($seed['prompt']) ?></p>
  </div>
  <?php if (!$currentUser): ?>
    <p><?= e(site_text('seeds_look')) ?> <a href="<?= e(url('/join')) ?>"><?= e(site_text('cta_join')) ?></a> <?= e(site_text('seeds_join_rest')) ?></p>
  <?php elseif ($seed['kind'] === 'same'): ?>
    <div class="card">
      <h2><?= e(site_text('seeds_similar_title')) ?></h2>
      <p><?= e(site_text('seeds_similar_body')) ?></p>
      <?php if (!empty($partnered)): ?>
        <p><?= e(site_text('seeds_match_ready')) ?></p>
        <p><a href="<?= e(url('/profile')) ?>"><?= e(site_text('seeds_go_profile')) ?></a></p>
      <?php elseif ($open): ?>
        <p><?= e(site_text('seeds_asked')) ?></p>
        <p><?= e($open['note']) ?></p>
      <?php else: ?>
        <form method="post" action="<?= e(url('/seeds/' . $seed['slug'] . '/open')) ?>">
          <?= csrf_field() ?>
          <label><span><?= e(site_text('seeds_company_label')) ?></span><textarea name="note" maxlength="1000"></textarea></label>
          <button type="submit"><?= e(site_text('cta_ask_intro')) ?></button>
        </form>
      <?php endif; ?>
    </div>
  <?php elseif ($seed['slug'] === 'skill-swap' || $seed['kind'] === 'skill-swap' && $seed['slug'] === 'skill-swap'): ?>
    <?php include __DIR__ . '/board.php'; ?>
  <?php else: ?>
    <div class="card">
      <?php if ($planted): ?>
        <p><?= e(site_text('seeds_with')) ?></p>
      <?php else: ?>
        <form method="post" action="<?= e(url('/seeds/' . $seed['slug'] . '/begin')) ?>">
          <?= csrf_field() ?>
          <button type="submit"><?= e(site_text('cta_plant_seed')) ?></button>
        </form>
      <?php endif; ?>
      <?php if ($seed['kind'] === 'skill-swap'): ?>
        <p><a href="<?= e(url('/seeds/skill-swap')) ?>"><?= e(site_text('cta_offer_skill')) ?></a></p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>
