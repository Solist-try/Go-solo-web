<?php $pageTitle = site_text('life_desk_seasons') . ' · Steward Desk'; $desk = 'community'; include __DIR__ . '/open.php'; ?>
<h1><?= e(site_text('life_desk_seasons')) ?></h1>
<p><a href="<?= e(url('/steward/community')) ?>"><?= e(site_text('life_desk_community')) ?></a></p>
<?php foreach ($seasons as $season): ?>
  <article class="card">
    <p><?= e((string) $season['label']) ?><?php if ((int) $season['archived'] === 1): ?> <span class="soft">· <?= e(site_text('life_seed_archived')) ?></span><?php endif; ?></p>
    <form method="post" action="<?= e(url('/steward/community/seasons')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= e((string) $season['id']) ?>">
      <input type="hidden" name="action" value="<?= (int) $season['archived'] === 1 ? 'restore' : 'archive' ?>">
      <button class="quiet small" type="submit"><?= e((int) $season['archived'] === 1 ? site_text('life_seed_restore') : site_text('life_seed_archive')) ?></button>
    </form>
  </article>
<?php endforeach; ?>
<form method="post" action="<?= e(url('/steward/community/seasons')) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="add">
  <label><span><?= e(site_text('label_title')) ?></span><input type="text" name="label" maxlength="120" required></label>
  <label><span><?= e(site_text('life_filter_status')) ?></span><input type="number" name="sort_order" value="0"></label>
  <button type="submit"><?= e(site_text('cta_save')) ?></button>
</form>
<?php include __DIR__ . '/close.php'; ?>
