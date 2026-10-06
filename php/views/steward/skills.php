<?php $pageTitle = 'Skill Swap · Steward Desk'; $desk = 'skills'; include __DIR__ . '/open.php'; ?>
<h1>Skill Swap</h1>
<h2>Connect two people</h2>
<form method="post" action="<?= e(url('/steward/skills/connect')) ?>">
  <?= csrf_field() ?>
  <label><span>Offer</span>
    <select name="offer_id" required>
      <option value="">Choose an offer</option>
      <?php foreach ($offers as $offer): if ($offer['archived']) continue; ?>
        <option value="<?= e((string) $offer['id']) ?>"><?= e(($offer['display_name'] ?: 'A member') . ' · ' . $offer['title']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label><span>Request</span>
    <select name="request_id" required>
      <option value="">Choose a request</option>
      <?php foreach ($requests as $request): if ($request['archived']) continue; ?>
        <option value="<?= e((string) $request['id']) ?>"><?= e(($request['display_name'] ?: 'A member') . ' · ' . $request['title']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label><span>Note</span><textarea name="note" maxlength="500"></textarea></label>
  <button type="submit">Connect them</button>
</form>

<h2>Offers</h2>
<?php foreach ($offers as $offer): ?>
  <article class="card">
    <p><strong><?= e($offer['title']) ?></strong> · <?= e($offer['display_name'] ?: 'A member') ?><?php if ($offer['archived']): ?> <span class="soft">archived</span><?php endif; ?></p>
    <?php if ($offer['detail']): ?><p><?= e($offer['detail']) ?></p><?php endif; ?>
    <?php if (!$offer['archived']): ?>
      <form method="post" action="<?= e(url('/steward/skills/archive')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="kind" value="offer">
        <input type="hidden" name="id" value="<?= e((string) $offer['id']) ?>">
        <button class="quiet small" type="submit">Archive</button>
      </form>
    <?php endif; ?>
  </article>
<?php endforeach; ?>

<h2>Requests</h2>
<?php foreach ($requests as $request): ?>
  <article class="card">
    <p><strong><?= e($request['title']) ?></strong> · <?= e($request['display_name'] ?: 'A member') ?><?php if ($request['archived']): ?> <span class="soft">archived</span><?php endif; ?></p>
    <?php if ($request['detail']): ?><p><?= e($request['detail']) ?></p><?php endif; ?>
    <?php if (!$request['archived']): ?>
      <form method="post" action="<?= e(url('/steward/skills/archive')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="kind" value="request">
        <input type="hidden" name="id" value="<?= e((string) $request['id']) ?>">
        <button class="quiet small" type="submit">Archive</button>
      </form>
    <?php endif; ?>
  </article>
<?php endforeach; ?>

<h2>Connections</h2>
<?php if (!$links): ?><p class="soft">None yet.</p><?php endif; ?>
<ul class="list">
  <?php foreach ($links as $link): ?>
    <li><?= e($link['name_from']) ?> and <?= e($link['name_to']) ?><?php if ($link['archived']): ?> <span class="soft">archived</span><?php endif; ?><?php if ($link['note']): ?> — <?= e($link['note']) ?><?php endif; ?></li>
  <?php endforeach; ?>
</ul>
<?php include __DIR__ . '/close.php'; ?>
