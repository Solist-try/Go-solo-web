<?php $pageTitle = ($waypoint['title'] ?? 'Waypoint') . ' · ' . site_text('site_title'); ?>
<section class="band mist">
  <div class="frame section">
  <p class="kicker"><a href="<?= e(url('/waypoints')) ?>">Waypoints</a></p>
  <h1><?= e($waypoint['title']) ?></h1>
  <?php $sitting = waypoint_sitting((string) $waypoint['slug']); ?>
  <?php if ($sitting !== ''): ?><p class="kicker">People sit with</p><p><?= e($sitting) ?></p><?php endif; ?>
  <?php if ($waypoint['cover_path']): ?>
    <img class="photo story-photo" src="<?= e(media($waypoint['cover_path'])) ?>" alt="">
  <?php endif; ?>
  <?= paragraphs((string) $waypoint['description']) ?>
  <?php if ($currentUser && empty($waypoint['archived'])): ?>
    <?php if ($joined): ?>
      <form method="post" action="<?= e(url('/waypoints/' . $waypoint['slug'] . '/leave')) ?>">
        <?= csrf_field() ?>
        <button class="quiet" type="submit">Leave this chair</button>
      </form>
    <?php else: ?>
      <form method="post" action="<?= e(url('/waypoints/' . $waypoint['slug'] . '/join')) ?>">
        <?= csrf_field() ?>
        <button type="submit">Sit with this waypoint</button>
      </form>
    <?php endif; ?>
  <?php elseif (!$currentUser): ?>
    <p><a href="<?= e(url('/join')) ?>">Join Go Solo</a> if you want to sit here.</p>
  <?php endif; ?>

  <h2>Food for Thought</h2>
  <?php if (empty($readings)): ?>
    <p class="soft">Nothing from the reading room has been set here yet.</p>
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

  <h2>Current Discussions</h2>
  <?php if (empty($discussions)): ?>
    <p class="soft">No one has started a conversation in this chair yet.</p>
  <?php else: ?>
    <div class="stack">
      <?php foreach ($discussions as $discussion): ?>
        <article class="card">
          <h3><a href="<?= e(url('/waypoints/' . $waypoint['slug'] . '/discussions/' . $discussion['id'])) ?>"><?= e($discussion['title']) ?></a></h3>
          <p class="soft">
            <?= e($discussion['display_name'] ?: 'A member') ?>
            · <?= e(nice_date($discussion['created_at'])) ?>
            <?php if ((int) $discussion['replies'] > 0): ?> · <?= e((string) $discussion['replies']) ?> <?= (int) $discussion['replies'] === 1 ? 'note' : 'notes' ?><?php endif; ?>
            <?php if (!empty($discussion['pinned'])): ?> · pinned<?php endif; ?>
            <?php if (!empty($discussion['last_reply'])): ?> · last note <?= e(nice_date($discussion['last_reply'])) ?><?php endif; ?>
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

  <h2>Start a Discussion</h2>
  <?php if (!rooms_ready()): ?>
    <?php if (is_steward()): ?><p class="soft">Import sql/update-rooms.sql to open discussions here.</p><?php endif; ?>
  <?php elseif (!empty($canSpeak)): ?>
    <?php include __DIR__ . '/../partials/editor.php'; ?>
  <?php elseif ($currentUser): ?>
    <p class="soft">Sit with this waypoint when you want to start a conversation here.</p>
  <?php else: ?>
    <p class="soft">Join Go Solo, then sit with this waypoint, when you want to start a conversation.</p>
  <?php endif; ?>

  <h2>People sitting here</h2>
  <?php if (!$people): ?>
    <p class="soft">The chairs are empty. You can sit down.</p>
  <?php else: ?>
    <ul class="list">
      <?php foreach ($people as $person): ?>
        <li><a href="<?= e(url('/members/' . $person['id'])) ?>"><?= e($person['display_name'] ?: 'A member') ?></a></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  </div>
</section>
