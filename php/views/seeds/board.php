<div class="cards two">
  <article class="card">
    <h2><?= e(site_text('board_offers')) ?></h2>
    <?php if (!$offers): ?><p class="soft"><?= e(site_text('empty_offers')) ?></p><?php endif; ?>
    <ul class="list">
      <?php foreach ($offers as $offer): ?>
        <li>
          <strong><?= e($offer['title']) ?></strong>
          <span class="soft"> · <?= e($offer['display_name'] ?: site_text('garden_member')) ?></span>
          <?php if ($offer['detail']): ?><p><?= e($offer['detail']) ?></p><?php endif; ?>
          <?php if ($currentUser && (int) $offer['user_id'] !== (int) $currentUser['id']): ?>
            <?php $talkOffer = talk_offer($currentUser, (int) $offer['user_id'], true, '/seeds/skill-swap', ['type' => 'skill', 'kind' => 'offer', 'id' => (int) $offer['id'], 'person' => 0], site_text('talk_interested')); include __DIR__ . '/../partials/talk-offer.php'; ?>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </article>
  <article class="card">
    <h2><?= e(site_text('board_requests')) ?></h2>
    <?php if (!$requests): ?><p class="soft"><?= e(site_text('empty_requests')) ?></p><?php endif; ?>
    <ul class="list">
      <?php foreach ($requests as $request): ?>
        <li>
          <strong><?= e($request['title']) ?></strong>
          <span class="soft"> · <?= e($request['display_name'] ?: site_text('garden_member')) ?></span>
          <?php if ($request['detail']): ?><p><?= e($request['detail']) ?></p><?php endif; ?>
          <?php if ($currentUser && (int) $request['user_id'] !== (int) $currentUser['id']): ?>
            <?php $talkOffer = talk_offer($currentUser, (int) $request['user_id'], true, '/seeds/skill-swap', ['type' => 'skill', 'kind' => 'request', 'id' => (int) $request['id'], 'person' => 0], site_text('talk_interested')); include __DIR__ . '/../partials/talk-offer.php'; ?>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </article>
</div>
<?php if ($currentUser): ?>
  <form method="post" action="<?= e(url('/seeds/skill-swap')) ?>">
    <?= csrf_field() ?>
    <h2><?= e(site_text('board_add')) ?></h2>
    <label><span><?= e(site_text('board_would')) ?></span>
      <select name="kind">
        <option value="offer"><?= e(site_text('board_offer')) ?></option>
        <option value="request"><?= e(site_text('board_ask')) ?></option>
      </select>
    </label>
    <?php $skillExamples = site_lines('seeds_skill_examples'); ?>
    <label><span><?= e(site_text('label_title')) ?></span><input type="text" name="title" maxlength="120" placeholder="<?= e($skillExamples[0] ?? '') ?>" required></label>
    <label><span><?= e(site_text('board_about')) ?></span><textarea name="detail" maxlength="1000"></textarea></label>
    <button type="submit"><?= e(site_text('cta_save')) ?></button>
  </form>
<?php endif; ?>
