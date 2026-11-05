<?php if (!empty($groundPeople)): ?>
  <h2><?= e(site_text($groundTitle ?? 'ground_who')) ?></h2>
  <?php foreach ($groundPeople as $ground): ?>
    <article class="card">
      <h3><a href="<?= e(url('/members/' . $ground['id'])) ?>"><?= e($ground['name']) ?></a></h3>
      <?php if (($ground['intro'] ?? '') !== ''): ?><p><?= e($ground['intro']) ?></p><?php endif; ?>
      <?php if (($ground['waypoint'] ?? '') !== ''): ?>
        <p class="soft"><?= e(site_text('garden_waypoint_now')) ?> · <?= e($ground['waypoint']) ?></p>
      <?php endif; ?>
      <?php if (!empty($ground['themes'])): ?>
        <p><?= e(implode(' · ', $ground['themes'])) ?></p>
      <?php endif; ?>
      <div class="row-actions">
        <a class="button quiet" href="<?= e(url('/members/' . $ground['id'])) ?>"><?= e(site_text('ground_view_garden')) ?></a>
        <?php if (!empty($ground['talk'])): ?>
          <?php $talkOffer = $ground['talk']; include __DIR__ . '/talk-offer.php'; ?>
        <?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
<?php endif; ?>
