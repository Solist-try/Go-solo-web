<?php $pageTitle = site_text('waypoints_title') . ' · ' . site_text('site_title'); ?>
<section class="band mist">
  <div class="frame section">
  <h1><?= e(site_text('waypoints_title')) ?></h1>
  <p><?= e(site_text('waypoints_intro')) ?></p>
  <?php if (!$waypoints): ?>
    <p><?= e(site_text('empty_waypoints')) ?></p>
  <?php endif; ?>
  <?php foreach ($waypoints as $index => $waypoint): ?>
    <?php
      $image = trim((string) ($waypoint['cover_path'] ?? ''));
      $shelfClass = 'reading-shelf waypoint-shelf' . ($image !== '' ? ' has-photo' : ' is-text');
      if ($image !== '' && ((int) $index % 2) === 1) {
          $shelfClass .= ' image-left';
      }
      $sitting = waypoint_sitting((string) $waypoint['slug'], $waypoint);
      $foodIntro = trim((string) ($waypoint['food_intro'] ?? ''));
      $food = $waypoint['food'] ?? [];
      $discussions = $waypoint['discussions'] ?? [];
    ?>
    <article class="<?= e($shelfClass) ?>">
      <div class="shelf-copy">
        <h2><a href="<?= e(url('/waypoints/' . $waypoint['slug'])) ?>"><?= e($waypoint['title']) ?></a></h2>
        <?php if ($sitting !== ''): ?><p class="shelf-line"><?= e($sitting) ?></p><?php endif; ?>
        <?php if (trim((string) $waypoint['description']) !== ''): ?>
          <div class="waypoint-description"><?= paragraphs((string) $waypoint['description']) ?></div>
        <?php endif; ?>
        <?php if ($foodIntro !== '' || $food): ?>
          <h3><?= e(site_text('waypoints_food_title')) ?></h3>
          <?php if ($foodIntro !== ''): ?><p><?= e($foodIntro) ?></p><?php endif; ?>
          <?php if ($food): ?>
            <div class="previews">
              <?php foreach ($food as $article): ?>
                <a href="<?= e(url('/reading/' . $article['slug'])) ?>">
                  <strong><?= e($article['title']) ?></strong>
                  <?php if (trim((string) $article['standfirst']) !== ''): ?><span><?= e($article['standfirst']) ?></span><?php endif; ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
        <?php if ($discussions): ?>
          <h3><?= e(site_text('waypoints_discussions_title')) ?></h3>
          <div class="previews">
            <?php foreach ($discussions as $discussion): ?>
              <a href="<?= e(url('/waypoints/' . $waypoint['slug'] . '/discussions/' . $discussion['id'])) ?>">
                <strong><?= e($discussion['title']) ?></strong>
                <?php if (trim((string) ($discussion['excerpt'] ?? '')) !== ''): ?><span><?= e($discussion['excerpt']) ?></span><?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <div class="shelf-action">
          <?php if ($currentUser && empty($waypoint['joined']) && empty($waypoint['archived'])): ?>
            <form method="post" action="<?= e(url('/waypoints/' . $waypoint['slug'] . '/join')) ?>">
              <?= csrf_field() ?>
              <button type="submit"><?= e(site_text('cta_sit')) ?></button>
            </form>
          <?php elseif ($currentUser && !empty($waypoint['joined'])): ?>
            <p class="soft"><?= e(site_text('waypoints_sitting_here')) ?></p>
          <?php else: ?>
            <p><a class="button" href="<?= e(url('/waypoints/' . $waypoint['slug'])) ?>"><?= e(site_text('cta_sit')) ?></a></p>
          <?php endif; ?>
        </div>
      </div>
      <?php if ($image !== ''): ?>
        <img class="photo shelf-photo" src="<?= e(media($image)) ?>" alt="<?= e($waypoint['title']) ?>">
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
  </div>
</section>
