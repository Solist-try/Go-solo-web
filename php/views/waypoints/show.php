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
  <?php if (empty($discussions)): ?>
    <p class="soft"><?= e(waypoint_line($waypoint, 'discussion_empty', 'empty_discussion')) ?></p>
  <?php else: ?>
    <div class="stack">
      <?php foreach ($discussions as $discussion): ?>
        <article class="card">
          <h3><a href="<?= e(url('/waypoints/' . $waypoint['slug'] . '/discussions/' . $discussion['id'])) ?>"><?= e($discussion['title']) ?></a></h3>
          <p class="soft">
            <?= e($discussion['display_name'] ?: site_text('garden_member')) ?>
            · <?= e(nice_date($discussion['created_at'])) ?>
            <?php if ((int) $discussion['replies'] > 0): ?> · <?= e((string) $discussion['replies']) ?> <?= e((int) $discussion['replies'] === 1 ? site_text('waypoints_note_one') : site_text('waypoints_note_many')) ?><?php endif; ?>
            <?php if (!empty($discussion['pinned'])): ?> · <?= e(site_text('waypoints_pinned')) ?><?php endif; ?>
            <?php if (!empty($discussion['last_reply'])): ?> · <?= e(site_text('waypoints_last_note')) ?> <?= e(nice_date($discussion['last_reply'])) ?><?php endif; ?>
          </p>
          <p><?= e(writing_plain((string) $discussion['body'], 220)) ?></p>
          <?php if (is_steward()): ?>
            <div class="row-actions">
              <form method="post" action="<?= e(url('/steward/waypoint-posts/' . $discussion['id'])) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="<?= !empty($discussion['pinned']) ? 'unpin' : 'pin' ?>">
                <button class="quiet small" type="submit"><?= !empty($discussion['pinned']) ? 'Unpin' : 'Pin' ?></button>
              </form>
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
          <?php if (!empty($joined) && talk_can_start($currentUser, (int) $person['id']) && sits_with((int) $currentUser['id'], (int) $waypoint['id']) && sits_with((int) $person['id'], (int) $waypoint['id'])): ?>
            <?php $talkType = 'waypoint'; $talkKind = ''; $talkId = (int) $waypoint['id']; $talkPerson = (int) $person['id']; $talkLabel = site_text('talk_hello'); $talkBack = '/waypoints/' . $waypoint['slug']; include __DIR__ . '/../partials/talk-start.php'; ?>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  </div>
</section>
