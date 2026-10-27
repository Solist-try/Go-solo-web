<?php $pageTitle = site_text('out_there_title') . ' · ' . site_text('site_title'); ?>
<section class="frame section">
  <h1><?= e(site_text('out_there_title')) ?></h1>
  <?php $hasStories = $stories || !empty($welcome) || !empty($pinAside); ?>
  <?php if (!$hasStories): ?>
    <figure>
      <img class="photo story-photo" src="<?= e(media(site_text('out_there_image'))) ?>" alt="<?= e(site_text('out_there_image_alt')) ?>">
    </figure>
    <p class="lede" style="font-size:1.7rem"><?= e(site_text('out_there_empty')) ?></p>
  <?php else: ?>
    <p><?= e(site_text('out_there_empty')) ?></p>
    <div class="stack">
      <?php if (!empty($welcome)): ?>
        <?php
          $pin = [
              'id' => (int) $welcome['id'],
              'href' => '/out-there/' . (int) $welcome['id'],
              'title' => (string) $welcome['title'],
              'author_id' => (int) ($welcome['user_id'] ?? 0),
              'author_name' => (string) ($welcome['display_name'] ?: site_text('garden_member')),
              'meta' => nice_date((string) $welcome['created_at']),
              'excerpt' => story_excerpt($welcome),
          ];
          $pinType = 'story';
          $pinBack = '/out-there';
          $pinKicker = 'pin_by';
          $pinHide = 'pin_hide';
          $pinManage = '/steward/stories/' . (int) $welcome['id'];
          $pinEdit = '/steward/stories/' . (int) $welcome['id'] . '/edit';
          include __DIR__ . '/../partials/pin-card.php';
        ?>
      <?php endif; ?>
      <?php $pinType = 'story'; $pinBack = '/out-there'; $pinAsideId = (int) ($pinAsideId ?? 0); include __DIR__ . '/../partials/pin-aside.php'; ?>
      <?php foreach ($stories as $story): ?>
        <article class="card">
          <h2><a href="<?= e(url('/out-there/' . $story['id'])) ?>"><?= e($story['title']) ?></a></h2>
          <p class="soft"><?php if ((int) ($story['user_id'] ?? 0) > 0): ?><a href="<?= e(url('/members/' . $story['user_id'])) ?>"><?= e($story['display_name'] ?: site_text('garden_member')) ?></a><?php else: ?><?= e($story['display_name'] ?: site_text('garden_member')) ?><?php endif; ?> · <?= e(nice_date($story['created_at'])) ?></p>
          <p><?= e(story_excerpt($story)) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php if ($currentUser): ?>
    <p style="margin-top:1.4rem"><a class="button sage" href="<?= e(url('/out-there/new')) ?>"><?= e(site_text('cta_tell_story')) ?></a></p>
  <?php else: ?>
    <p><?= e(site_text('out_there_guest')) ?></p>
  <?php endif; ?>
</section>
