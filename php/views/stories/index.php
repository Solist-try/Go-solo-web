<?php $pageTitle = site_text('out_there_title') . ' · ' . site_text('site_title'); ?>
<section class="frame section">
  <h1><?= e(site_text('out_there_title')) ?></h1>
  <?php if (!$stories): ?>
    <figure>
      <img class="photo story-photo" src="<?= e(media(site_text('out_there_image'))) ?>" alt="<?= e(site_text('out_there_image_alt')) ?>">
    </figure>
    <p class="lede" style="font-size:1.7rem"><?= e(site_text('out_there_empty')) ?></p>
  <?php else: ?>
    <p><?= e(site_text('out_there_empty')) ?></p>
    <div class="stack">
      <?php foreach ($stories as $story): ?>
        <article class="card">
          <h2><a href="<?= e(url('/out-there/' . $story['id'])) ?>"><?= e($story['title']) ?></a></h2>
          <p class="soft"><?= e($story['display_name'] ?: site_text('garden_member')) ?> · <?= e(nice_date($story['created_at'])) ?></p>
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
