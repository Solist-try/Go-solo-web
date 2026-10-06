<?php $pageTitle = 'Out There · ' . copy('site_title'); ?>
<section class="frame section">
  <h1>Out There</h1>
  <?php if (!$stories): ?>
    <figure>
      <img class="photo story-photo" src="<?= e(media(copy('out_there_image'))) ?>" alt="<?= e(copy('out_there_image_alt')) ?>">
    </figure>
    <p class="lede" style="font-size:1.7rem"><?= e(copy('out_there_empty')) ?></p>
  <?php else: ?>
    <p>Real experiences, and what someone learned from them.</p>
    <div class="stack">
      <?php foreach ($stories as $story): ?>
        <article class="card">
          <h2><a href="<?= e(url('/out-there/' . $story['id'])) ?>"><?= e($story['title']) ?></a></h2>
          <p class="soft"><?= e($story['display_name'] ?: 'A member') ?> · <?= e(nice_date($story['created_at'])) ?></p>
          <p><?= e(clip((string) $story['what_happened'], 220)) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php if ($currentUser): ?>
    <p style="margin-top:1.4rem"><a class="button sage" href="<?= e(url('/out-there/new')) ?>">Share something that happened</a></p>
  <?php else: ?>
    <p>When you are a member, you can share something that actually happened.</p>
  <?php endif; ?>
</section>
