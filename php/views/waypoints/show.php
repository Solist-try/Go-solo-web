<?php $pageTitle = ($waypoint['title'] ?? site_text('waypoints_title')) . ' · ' . site_text('site_title'); ?>
<section class="band mist">
  <div class="frame section">
  <p class="kicker"><a href="<?= e(url('/waypoints')) ?>"><?= e(site_text('waypoints_title')) ?></a></p>
  <h1><?= e($waypoint['title']) ?></h1>
  <?php $sitting = waypoint_sitting((string) $waypoint['slug'], $waypoint); ?>
  <?php if ($sitting !== ''): ?><p class="kicker"><?= e(site_text('waypoints_sit_label')) ?></p><p><?= e($sitting) ?></p><?php endif; ?>
  <?php if ($waypoint['cover_path']): ?>
    <img class="photo story-photo" src="<?= e(media($waypoint['cover_path'])) ?>" alt="">
  <?php endif; ?>
  <?= paragraphs((string) $waypoint['description']) ?>
  <?php if ($currentUser && empty($waypoint['archived'])): ?>
    <?php if ($joined): ?>
      <form method="post" action="<?= e(url('/waypoints/' . $waypoint['slug'] . '/leave')) ?>">
        <?= csrf_field() ?>
        <button class="quiet" type="submit"><?= e(site_text('cta_leave_chair')) ?></button>
      </form>
    <?php else: ?>
      <form method="post" action="<?= e(url('/waypoints/' . $waypoint['slug'] . '/join')) ?>">
        <?= csrf_field() ?>
        <button type="submit"><?= e(site_text('cta_sit')) ?></button>
      </form>
    <?php endif; ?>
  <?php elseif (!$currentUser): ?>
    <p><a href="<?= e(url('/join')) ?>"><?= e(site_text('cta_join')) ?></a> <?= e(site_text('waypoints_guest_rest')) ?></p>
  <?php endif; ?>

  <h2><?= e(site_text('waypoints_food_title')) ?></h2>
  <?php $foodIntro = waypoint_line($waypoint, 'food_intro', 'waypoints_food_intro'); ?>
  <?php if ($foodIntro !== ''): ?><p><?= e($foodIntro) ?></p><?php endif; ?>
  <?php if (empty($readings)): ?>
    <p class="soft"><?= e(site_text('empty_food')) ?></p>
  <?php else: ?>
    <div class="previews">
      <?php foreach ($readings as $article): ?>
        <a href="<?= e(url('/reading/' . $article['slug'])) ?>">
          <strong><?= e($article['title']) ?></strong>
          <?php if (trim((string) $article['standfirst']) !== ''): ?><span><?= e($article['standfirst']) ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h2><?= e(site_text('waypoints_discussions_title')) ?></h2>
  <?php if (empty($discussions) && empty($welcome) && empty($pinAside)): ?>
    <p class="soft"><?= e(waypoint_line($waypoint, 'discussion_empty', 'empty_discussion')) ?></p>
  <?php else: ?>
    <div class="stack">
      <?php if (!empty($welcome)): ?>
        <?php
          $pin = [
              'id' => (int) $welcome['id'],
              'href' => '/waypoints/' . $waypoint['slug'] . '/discussions/' . (int) $welcome['id'],
              'title' => (string) $welcome['title'],
              'meta' => trim((string) ($welcome['display_name'] ?: site_text('garden_member'))) . ' · ' . nice_date((string) $welcome['created_at']),
              'excerpt' => writing_plain((string) $welcome['body'], 220),
          ];
          $pinType = 'waypoint';
          $pinBack = '/waypoints/' . $waypoint['slug'];
          $pinKicker = 'pin_start';
          $pinHide = 'pin_hide';
          $pinManage = '/steward/waypoint-posts/' . (int) $welcome['id'];
          $pinEdit = '/steward/waypoint-posts/' . (int) $welcome['id'] . '/edit';
          include __DIR__ . '/../partials/pin-card.php';
        ?>
      <?php endif; ?>
      <?php $pinType = 'waypoint'; $pinBack = '/waypoints/' . $waypoint['slug']; $pinAsideId = (int) ($pinAsideId ?? 0); include __DIR__ . '/../partials/pin-aside.php'; ?>
      <?php foreach ($discussions as $discussion): ?>
        <article class="card">
          <h3><a href="<?= e(url('/waypoints/' . $waypoint['slug'] . '/discussions/' . $discussion['id'])) ?>"><?= e($discussion['title']) ?></a></h3>
          <p class="soft">
            <?= e($discussion['display_name'] ?: site_text('garden_member')) ?>
            · <?= e(nice_date($discussion['created_at'])) ?>
            <?php if ((int) $discussion['replies'] > 0): ?> · <?= e((string) $discussion['replies']) ?> <?= e((int) $discussion['replies'] === 1 ? site_text('waypoints_note_one') : site_text('waypoints_note_many')) ?><?php endif; ?>
            <?php if (!empty($discussion['pinned']) && empty($welcome)): ?> · <?= e(site_text('pin_by')) ?><?php endif; ?>
            <?php if (!empty($discussion['last_reply'])): ?> · <?= e(site_text('waypoints_last_note')) ?> <?= e(nice_date($discussion['last_reply'])) ?><?php endif; ?>
          </p>
          <p><?= e(writing_plain((string) $discussion['body'], 220)) ?></p>
          <?php if (is_steward()): ?>
            <div class="row-actions">
              <form method="post" action="<?= e(url('/steward/waypoint-posts/' . $discussion['id'])) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="<?= !empty($discussion['pinned']) ? 'unpin' : 'pin' ?>">
                <button class="quiet small" type="submit"><?= e(!empty($discussion['pinned']) ? site_text('pin_unpin') : (!empty($welcome) ? site_text('pin_replace') : site_text('pin_pin'))) ?></button>
              </form>
              <?php if (function_exists('pin_ready') && pin_ready()): ?>
                <a class="button quiet small" href="<?= e(url('/steward/waypoint-posts/' . $discussion['id'] . '/edit')) ?>"><?= e(site_text('pin_edit')) ?></a>
              <?php endif; ?>
              <form method="post" action="<?= e(url('/steward/waypoint-posts/' . $discussion['id'])) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="<?= !empty($discussion['hidden']) ? 'show' : 'hide' ?>">
                <button class="quiet small" type="submit"><?= !empty($discussion['hidden']) ? 'Show' : 'Hide' ?></button>
              </form>
              <form method="post" action="<?= e(url('/steward/waypoint-posts/' . $discussion['id'])) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="<?= !empty($discussion['locked']) ? 'unlock' : 'lock' ?>">
                <button class="quiet small" type="submit"><?= !empty($discussion['locked']) ? 'Open notes' : 'Let it rest' ?></button>
              </form>
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h2><?= e(site_text('waypoints_start_title')) ?></h2>
  <?php if (!rooms_ready()): ?>
    <?php if (is_steward()): ?><p class="soft">Import sql/update-rooms.sql to open discussions here.</p><?php endif; ?>
  <?php elseif (!empty($canSpeak)): ?>
    <?php include __DIR__ . '/../partials/editor.php'; ?>
  <?php elseif ($currentUser): ?>
    <p class="soft"><?= e(site_text('waypoints_sit_first')) ?></p>
  <?php else: ?>
    <p class="soft"><?= e(site_text('waypoints_join_first')) ?></p>
  <?php endif; ?>

  <h2><?= e(site_text('waypoints_people_title')) ?></h2>
  <?php if (!$people): ?>
    <p class="soft"><?= e(site_text('empty_people')) ?></p>
  <?php else: ?>
    <ul class="list">
      <?php foreach ($people as $person): ?>
        <li>
          <a href="<?= e(url('/members/' . $person['id'])) ?>"><?= e($person['display_name'] ?: site_text('garden_member')) ?></a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  </div>
</section>
