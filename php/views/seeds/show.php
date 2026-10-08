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
      <h2>A similar path</h2>
      <p>Say what you are moving through. A steward can introduce you to someone on a similar stretch. Nothing happens until you ask, and there is no rush.</p>
      <?php if (!empty($partnered)): ?>
        <p>A steward has an introduction for you. It is on your profile, whenever you want to look.</p>
        <p><a href="<?= e(url('/profile')) ?>">Go to your profile</a></p>
      <?php elseif ($open): ?>
        <p>You have asked. A steward will introduce you when someone is on a similar path.</p>
        <p><?= e($open['note']) ?></p>
      <?php else: ?>
        <form method="post" action="<?= e(url('/seeds/' . $seed['slug'] . '/open')) ?>">
          <?= csrf_field() ?>
          <label><span>What would you like company for?</span><textarea name="note" maxlength="1000"></textarea></label>
          <button type="submit">Ask for an introduction</button>
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
        <p><a href="<?= e(url('/seeds/skill-swap')) ?>">Offer or request a skill</a></p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>
