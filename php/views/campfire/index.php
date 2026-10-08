<?php $pageTitle = 'Campfire · ' . site_text('site_title'); ?>
<section class="band clay">
  <div class="frame section">
  <h1>Campfire</h1>
  <?php if (!$posts): ?>
    <figure>
      <img class="photo story-photo" src="<?= e(media(site_text('campfire_image'))) ?>" alt="<?= e(site_text('campfire_image_alt')) ?>">
    </figure>
    <p class="lede" style="font-size:1.8rem"><?= e(site_text('campfire_waiting')) ?></p>
    <p><?= e(site_text('campfire_empty')) ?></p>
  <?php else: ?>
    <p><?= e(site_text('campfire_empty')) ?></p>
  <?php endif; ?>
  <?php if ($currentUser): ?>
    <p style="margin-top:1.4rem"><a class="button sage" href="<?= e(url('/campfire/new')) ?>">Pull up a chair</a></p>
  <?php endif; ?>
  </div>
</section>
<?php if ($posts): ?>
<section class="frame section">
    <div class="stack">
      <?php foreach ($posts as $post): ?>
        <article class="card">
          <h2><a href="<?= e(url('/campfire/' . $post['id'])) ?>"><?= e($post['title']) ?></a></h2>
          <p class="soft"><?= e($post['display_name'] ?: 'A member') ?> · <?= e(nice_date($post['created_at'])) ?></p>
          <p><?= e(writing_plain((string) $post['body'], 240)) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
