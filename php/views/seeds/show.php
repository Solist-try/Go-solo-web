<section class="frame section">
  <?php
    $kindLabel = ['same' => 'SAME', 'skill-swap' => 'Skill Swap', 'practice' => 'A seed'][$seed['kind']] ?? 'A seed';
  ?>
  <p class="kicker"><?= e($kindLabel) ?></p>
  <h1><?= e($seed['title']) ?></h1>
  <p><?= e($seed['description']) ?></p>
  <div class="card sage">
    <p class="kicker">What you are growing</p>
    <p class="lede" style="font-size:1.8rem"><?= e($seed['prompt']) ?></p>
  </div>
  <?php if (!$currentUser): ?>
    <p>Take a look around. <a href="<?= e(url('/join')) ?>">Join Go Solo</a> if you want to plant this seed.</p>
  <?php elseif ($seed['kind'] === 'same'): ?>
    <div class="card">
      <h2>A SAME partner</h2>
      <p>Name what you would like to grow. Being open does not pair you with anyone. A steward suggests a partner when there is a fit.</p>
      <?php if (!empty($partnered)): ?>
        <p>A steward suggested a partner. It is on your profile, whenever you want to look.</p>
        <p><a href="<?= e(url('/profile')) ?>">Go to your profile</a></p>
      <?php elseif ($open): ?>
        <p>You are open. A match waits for a steward.</p>
        <p><?= e($open['note']) ?></p>
      <?php else: ?>
        <form method="post" action="<?= e(url('/seeds/' . $seed['slug'] . '/open')) ?>">
          <?= csrf_field() ?>
          <label><span>What would you like to grow?</span><textarea name="note" maxlength="1000"></textarea></label>
          <button type="submit">I am open to a partner</button>
        </form>
      <?php endif; ?>
    </div>
  <?php elseif ($seed['slug'] === 'skill-swap' || $seed['kind'] === 'skill-swap' && $seed['slug'] === 'skill-swap'): ?>
    <?php include __DIR__ . '/board.php'; ?>
  <?php else: ?>
    <div class="card">
      <?php if ($planted): ?>
        <p>You are with this seed. Missing a day is allowed.</p>
      <?php else: ?>
        <form method="post" action="<?= e(url('/seeds/' . $seed['slug'] . '/begin')) ?>">
          <?= csrf_field() ?>
          <button type="submit">Plant this seed</button>
        </form>
      <?php endif; ?>
      <?php if ($seed['kind'] === 'skill-swap'): ?>
        <p><a href="<?= e(url('/seeds/skill-swap')) ?>">Offer help, or ask for it</a></p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>
