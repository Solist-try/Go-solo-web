<?php $pageTitle = site_text('campfire_title') . ' · ' . site_text('site_title'); ?>
<section class="band clay">
  <div class="frame section">
  <h1><?= e(site_text('campfire_title')) ?></h1>
  <?php if (!$posts && empty($welcome)): ?>
    <figure>
      <img class="photo story-photo" src="<?= e(media(site_text('campfire_image'))) ?>" alt="<?= e(site_text('campfire_image_alt')) ?>">
    </figure>
    <p class="lede" style="font-size:1.8rem"><?= e(site_text('campfire_waiting')) ?></p>
    <p><?= e(site_text('campfire_empty')) ?></p>
  <?php else: ?>
    <p><?= e(site_text('campfire_empty')) ?></p>
  <?php endif; ?>
  <?php if ($currentUser): ?>
    <p style="margin-top:1.4rem"><a class="button sage" href="<?= e(url('/campfire/new')) ?>"><?= e(site_text('campfire_form_title')) ?></a></p>
  <?php endif; ?>
  </div>
</section>
<?php if ($posts || !empty($welcome) || !empty($pinAside)): ?>
<section class="frame section">
    <div class="stack">
      <?php if (!empty($welcome)): ?>
        <?php
          $pin = [
              'id' => (int) $welcome['id'],
              'href' => '/campfire/' . (int) $welcome['id'],
              'title' => (string) $welcome['title'],
              'meta' => trim((string) ($welcome['display_name'] ?: site_text('garden_member'))) . ' · ' . nice_date((string) $welcome['created_at']),
              'excerpt' => writing_plain((string) $welcome['body'], 240),
          ];
          $pinType = 'campfire';
          $pinBack = '/campfire';
          $pinKicker = 'pin_welcome';
          $pinHide = 'pin_hide_welcome';
          $pinManage = '/steward/campfire/' . (int) $welcome['id'];
          $pinEdit = '/steward/campfire/' . (int) $welcome['id'] . '/edit';
          include __DIR__ . '/../partials/pin-card.php';
        ?>
      <?php endif; ?>
      <?php $pinType = 'campfire'; $pinBack = '/campfire'; $pinAsideId = (int) ($pinAsideId ?? 0); include __DIR__ . '/../partials/pin-aside.php'; ?>
      <?php foreach ($posts as $post): ?>
        <article class="card">
          <h2><a href="<?= e(url('/campfire/' . $post['id'])) ?>"><?= e($post['title']) ?></a></h2>
          <p class="soft"><?= e($post['display_name'] ?: site_text('garden_member')) ?> · <?= e(nice_date($post['created_at'])) ?></p>
          <p><?= e(writing_plain((string) $post['body'], 240)) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
