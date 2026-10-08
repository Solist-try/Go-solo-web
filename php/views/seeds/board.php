<div class="cards two">
  <article class="card">
    <h2><?= e(site_text('board_offers')) ?></h2>
    <?php if (!$offers): ?><p class="soft"><?= e(site_text('empty_offers')) ?></p><?php endif; ?>
    <ul class="list">
      <?php foreach ($offers as $offer): ?>
        <li>
          <strong><?= e($offer['title']) ?></strong>
          <span class="soft"> · <?= e($offer['display_name'] ?: site_text('garden_member')) ?><?php if (life_ready()): ?> · <?= e(life_skill_label((string) ($offer['listing_status'] ?? 'open'), life_skill_in_progress('offer', (int) $offer['id']))) ?><?php endif; ?></span>
          <?php if ($offer['detail']): ?><p><?= e($offer['detail']) ?></p><?php endif; ?>
          <?php if ($currentUser && (int) $offer['user_id'] !== (int) $currentUser['id'] && life_skill_accepts_interest($offer)): ?>
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
          <span class="soft"> · <?= e($request['display_name'] ?: site_text('garden_member')) ?><?php if (life_ready()): ?> · <?= e(life_skill_label((string) ($request['listing_status'] ?? 'open'), life_skill_in_progress('request', (int) $request['id']))) ?><?php endif; ?></span>
          <?php if ($request['detail']): ?><p><?= e($request['detail']) ?></p><?php endif; ?>
          <?php if ($currentUser && (int) $request['user_id'] !== (int) $currentUser['id'] && life_skill_accepts_interest($request)): ?>
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
  <?php if (life_ready() && !empty($mySkills)): ?>
    <h2><?= e(site_text('life_skill_mine')) ?></h2>
    <?php foreach ($mySkills as $skill): ?>
      <article class="card">
        <p><?= e((string) $skill['title']) ?> <span class="soft">· <?= e(life_skill_label((string) $skill['listing_status'], (bool) $skill['in_progress'])) ?></span></p>
        <div class="row-actions">
          <?php if (life_skill_transition((string) $skill['listing_status'], 'paused')): ?>
            <form method="post" action="<?= e(url('/seeds/skill-swap')) ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="pause">
              <input type="hidden" name="kind" value="<?= e((string) $skill['kind']) ?>">
              <input type="hidden" name="id" value="<?= e((string) $skill['id']) ?>">
              <p class="soft"><?= e(site_text('life_skill_pause_note')) ?></p>
              <button class="quiet small" type="submit"><?= e(site_text('life_skill_pause')) ?></button>
            </form>
          <?php endif; ?>
          <?php if (in_array((string) $skill['listing_status'], ['paused', 'completed', 'archived'], true) && life_skill_transition((string) $skill['listing_status'], 'open')): ?>
            <form method="post" action="<?= e(url('/seeds/skill-swap')) ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="<?= (string) $skill['listing_status'] === 'archived' ? 'restore' : 'resume' ?>">
              <input type="hidden" name="kind" value="<?= e((string) $skill['kind']) ?>">
              <input type="hidden" name="id" value="<?= e((string) $skill['id']) ?>">
              <button class="quiet small" type="submit"><?= e((string) $skill['listing_status'] === 'archived' ? site_text('life_skill_restore') : site_text('life_skill_resume')) ?></button>
            </form>
          <?php endif; ?>
          <?php if (life_skill_transition((string) $skill['listing_status'], 'completed')): ?>
            <form method="post" action="<?= e(url('/seeds/skill-swap')) ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="complete">
              <input type="hidden" name="kind" value="<?= e((string) $skill['kind']) ?>">
              <input type="hidden" name="id" value="<?= e((string) $skill['id']) ?>">
              <p class="soft"><?= e(site_text('life_skill_complete_note')) ?></p>
              <label><input type="checkbox" name="confirm" value="1" required> <?= e(site_text('life_skill_confirm')) ?></label>
              <button class="quiet small" type="submit"><?= e(site_text('life_skill_complete')) ?></button>
            </form>
          <?php endif; ?>
          <?php if (life_skill_transition((string) $skill['listing_status'], 'archived')): ?>
            <form method="post" action="<?= e(url('/seeds/skill-swap')) ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="archive">
              <input type="hidden" name="kind" value="<?= e((string) $skill['kind']) ?>">
              <input type="hidden" name="id" value="<?= e((string) $skill['id']) ?>">
              <p class="soft"><?= e(site_text('life_skill_archive_note')) ?></p>
              <label><input type="checkbox" name="confirm" value="1" required> <?= e(site_text('life_skill_confirm')) ?></label>
              <button class="quiet small" type="submit"><?= e(site_text('life_skill_archive')) ?></button>
            </form>
          <?php endif; ?>
        </div>
        <?php if ((string) $skill['listing_status'] === 'completed'): ?>
          <?php $outcomeType = 'skill-' . $skill['kind']; $outcomeId = (int) $skill['id']; $outcome = life_outcome_of((int) $currentUser['id'], $outcomeType, (int) $skill['id']); $back = '/seeds/skill-swap'; include __DIR__ . '/../partials/life-outcome.php'; ?>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
<?php endif; ?>
