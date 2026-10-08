<div class="cards two">
  <article class="card">
    <h2>Offers</h2>
    <?php if (!$offers): ?><p class="soft">No offers yet.</p><?php endif; ?>
    <ul class="list">
      <?php foreach ($offers as $offer): ?>
        <li>
          <strong><?= e($offer['title']) ?></strong>
          <span class="soft"> · <?= e($offer['display_name'] ?: 'A member') ?></span>
          <?php if ($offer['detail']): ?><p><?= e($offer['detail']) ?></p><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </article>
  <article class="card">
    <h2>Requests</h2>
    <?php if (!$requests): ?><p class="soft">No requests yet.</p><?php endif; ?>
    <ul class="list">
      <?php foreach ($requests as $request): ?>
        <li>
          <strong><?= e($request['title']) ?></strong>
          <span class="soft"> · <?= e($request['display_name'] ?: 'A member') ?></span>
          <?php if ($request['detail']): ?><p><?= e($request['detail']) ?></p><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </article>
</div>
<?php if ($currentUser): ?>
  <form method="post" action="<?= e(url('/seeds/skill-swap')) ?>">
    <?= csrf_field() ?>
    <h2>Add yours</h2>
    <label><span>I would like to</span>
      <select name="kind">
        <option value="offer">Offer help</option>
        <option value="request">Ask for help</option>
      </select>
    </label>
    <label><span>Title</span><input type="text" name="title" maxlength="120" placeholder="Crochet" required></label>
    <label><span>A little about it</span><textarea name="detail" maxlength="1000"></textarea></label>
    <button type="submit">Save</button>
  </form>
<?php endif; ?>
