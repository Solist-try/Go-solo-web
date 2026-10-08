<?php
$pageTitle = ($person['display_name'] ?: 'Profile') . ' · ' . site_text('site_title');
$name = $person['display_name'] ?: site_text('garden_member');
?>
<section class="frame section">
  <p class="kicker"><?= e($isSelf ? site_text('garden_kicker_mine') : site_text('garden_kicker_other')) ?></p>
  <h1><?= e($name) ?></h1>
  <?php if (!empty($person['avatar_path'])): ?>
    <img class="avatar" src="<?= e(media($person['avatar_path'])) ?>" alt="">
  <?php endif; ?>
  <?php if ($showLocation && trim((string) $person['location']) !== ''): ?>
    <p class="soft"><?= e($person['location']) ?></p>
  <?php endif; ?>
  <?php if ($isSelf): ?>
    <p class="row-actions">
      <a class="button quiet small" href="<?= e(url('/profile/edit')) ?>"><?= e(site_text('cta_tend')) ?></a>
      <a class="button quiet small" href="<?= e(url('/notices')) ?>"><?= e(site_text('cta_notes')) ?></a>
      <a class="button quiet small" href="<?= e(url('/account')) ?>"><?= e(site_text('cta_account')) ?></a>
    </p>
  <?php endif; ?>

  <?php if ($isSelf && conversations_ready()): ?>
    <h2 id="communication"><?= e(site_text('talk_communication')) ?></h2>
    <p><?= e(site_text('talk_pref_intro')) ?></p>
    <?php if (!talk_choice_made((int) $person['id'])): ?>
      <p class="soft"><?= e(site_text('talk_pref_prompt')) ?></p>
    <?php endif; ?>
    <?php if (talk_held((int) $person['id'])): ?>
      <p class="soft"><?= e(site_text('talk_held')) ?></p>
    <?php endif; ?>
    <form method="post" action="<?= e(url('/profile/conversations')) ?>">
      <?= csrf_field() ?>
      <?php $talkSelected = talk_pref((int) $person['id']); include __DIR__ . '/../partials/talk-choices.php'; ?>
      <button type="submit"><?= e(site_text('talk_pref_save')) ?></button>
    </form>
    <?php if ($talkRequests): ?>
      <h2><?= e(site_text('talk_requests')) ?></h2>
      <ul class="list">
        <?php foreach ($talkRequests as $conversation): ?>
          <li>
            <a href="<?= e(url('/conversations/' . $conversation['id'])) ?>"><?= e($conversation['context_label'] ?: site_text('talk_request_heading')) ?></a>
            <?php if ($conversation['other_name']): ?><span class="soft"> · <?= e($conversation['other_name']) ?></span><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php if ($talkWaiting): ?>
      <h2><?= e(site_text('talk_waiting')) ?></h2>
      <ul class="list">
        <?php foreach ($talkWaiting as $conversation): ?>
          <li>
            <a href="<?= e(url('/conversations/' . $conversation['id'])) ?>"><?= e($conversation['context_label'] ?: site_text('talk_heading')) ?></a>
            <?php if ($conversation['other_name']): ?><span class="soft"> · <?= e($conversation['other_name']) ?></span><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <h2 id="conversations"><?= e(site_text('talk_active')) ?></h2>
    <?php if (!$conversations): ?>
      <p class="soft"><?= e(site_text('talk_empty')) ?></p>
    <?php else: ?>
      <ul class="list">
        <?php foreach ($conversations as $conversation): ?>
          <li>
            <a href="<?= e(url('/conversations/' . $conversation['id'])) ?>"><?= e($conversation['context_label'] ?: site_text('talk_heading')) ?></a>
            <?php if ($conversation['other_name']): ?><span class="soft"> · <?= e($conversation['other_name']) ?></span><?php endif; ?>
            <?php if ((int) $conversation['closed'] === 1): ?><span class="soft"> · <?= e(site_text('talk_closed')) ?></span><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php if ($talkBlocks): ?>
      <h2><?= e(site_text('talk_blocks')) ?></h2>
      <ul class="list">
        <?php foreach ($talkBlocks as $block): ?>
          <li>
            <?= e($block['display_name'] ?: site_text('garden_member')) ?>
            <form method="post" action="<?= e(url('/profile/blocks')) ?>" class="inline">
              <?= csrf_field() ?>
              <input type="hidden" name="person_id" value="<?= e((string) $block['blocked_id']) ?>">
              <button class="quiet small" type="submit"><?= e(site_text('talk_unblock')) ?></button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  <?php elseif (!$isSelf): ?>
    <?php $talkOffer = talk_profile_offer($currentUser, (int) $person['id']); include __DIR__ . '/../partials/talk-offer.php'; ?>
  <?php endif; ?>

  <h2><?= e(site_text('garden_bio')) ?></h2>
  <?php if (trim((string) $person['bio']) === ''): ?>
    <p class="soft"><?= e(site_text('empty_bio')) ?></p>
  <?php else: ?>
    <?= paragraphs((string) $person['bio']) ?>
  <?php endif; ?>

  <?php if ($isSelf && $matches): ?>
    <h2><?= e(site_text('garden_similar')) ?></h2>
    <?php foreach ($matches as $match): ?>
      <article class="card sage">
        <p><?= e(site_line('garden_match', ['name' => $match['other_name']])) ?></p>
        <p><a href="<?= e(url('/members/' . $match['other_id'])) ?>"><?= e($match['other_name']) ?></a></p>
        <?php if ($match['note']): ?><p><?= e($match['note']) ?></p><?php endif; ?>
        <?php foreach ($sameNotes[$match['id']] ?? [] as $note): ?>
          <p><strong><?= e($note['display_name'] ?: site_text('garden_member')) ?></strong> <span class="soft"><?= e(nice_date($note['created_at'])) ?></span><br><?= e($note['body']) ?></p>
        <?php endforeach; ?>
        <?php if (conversations_ready()): ?>
          <?php $talkOffer = talk_offer($currentUser, (int) $match['other_id'], true, '/profile', ['type' => 'introduction', 'kind' => '', 'id' => (int) $match['id'], 'person' => 0], site_text('talk_hello')); include __DIR__ . '/../partials/talk-offer.php'; ?>
        <?php else: ?>
        <form method="post" action="<?= e(url('/notes')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="context_type" value="same">
          <input type="hidden" name="context_id" value="<?= e((string) $match['id']) ?>">
          <label><span><?= e(site_text('garden_note_label')) ?></span><textarea name="body" maxlength="2000"></textarea></label>
          <button type="submit"><?= e(site_text('cta_leave_note')) ?></button>
        </form>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if ($isSelf && $skillLinks): ?>
    <h2><?= e(site_text('garden_skill')) ?></h2>
    <?php foreach ($skillLinks as $link): ?>
      <article class="card clay">
        <p><?= e(site_line('garden_skill_match', ['name' => $link['other_name']])) ?></p>
        <?php if ($link['title']): ?><p><?= e($link['title']) ?></p><?php endif; ?>
        <p><a href="<?= e(url('/members/' . $link['other_id'])) ?>"><?= e($link['other_name']) ?></a></p>
        <?php foreach ($skillNotes[$link['id']] ?? [] as $note): ?>
          <p><strong><?= e($note['display_name'] ?: site_text('garden_member')) ?></strong> <span class="soft"><?= e(nice_date($note['created_at'])) ?></span><br><?= e($note['body']) ?></p>
        <?php endforeach; ?>
        <?php if (conversations_ready()): ?>
          <?php $talkOffer = talk_offer($currentUser, (int) $link['other_id'], true, '/profile', ['type' => 'skill', 'kind' => 'link', 'id' => (int) $link['id'], 'person' => 0], site_text('talk_hello')); include __DIR__ . '/../partials/talk-offer.php'; ?>
        <?php else: ?>
        <form method="post" action="<?= e(url('/notes')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="context_type" value="skill">
          <input type="hidden" name="context_id" value="<?= e((string) $link['id']) ?>">
          <label><span><?= e(site_text('garden_note_label')) ?></span><textarea name="body" maxlength="2000"></textarea></label>
          <button type="submit"><?= e(site_text('cta_leave_note')) ?></button>
        </form>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>

  <h2><?= e(site_text('garden_waypoints')) ?></h2>
  <?php if (!$waypoints): ?><p class="soft"><?= e(site_text('empty_chair')) ?></p><?php else: ?>
    <ul class="chips">
      <?php foreach ($waypoints as $waypoint): ?>
        <li><a href="<?= e(url('/waypoints/' . $waypoint['slug'])) ?>"><?= e($waypoint['title']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <h2><?= e(site_text('garden_growing')) ?></h2>
  <?php if (!$growing && !$planted): ?><p class="soft"><?= e(site_text('empty_planted')) ?></p><?php endif; ?>
  <ul class="list">
    <?php foreach ($planted as $seed): ?>
      <li><a href="<?= e(url('/seeds/' . $seed['slug'])) ?>"><?= e($seed['title']) ?></a></li>
    <?php endforeach; ?>
    <?php foreach ($growing as $seed): ?>
      <li>
        <?= e($seed['title']) ?><?php if ($seed['status'] === 'resting'): ?> <span class="soft">· <?= e(site_text('garden_resting')) ?></span><?php endif; ?><?php if (!empty($seed['looking_for_support'])): ?> <span class="soft">· <?= e(site_text('garden_open_help')) ?></span><?php endif; ?>
        <?php if (!$isSelf && ($seed['status'] ?? '') === 'active' && !empty($seed['looking_for_support'])): ?>
          <?php $talkOffer = talk_offer($currentUser, (int) $person['id'], true, '/members/' . $person['id'], ['type' => 'seed', 'kind' => 'growing', 'id' => (int) $seed['id'], 'person' => 0], site_text('talk_offer')); include __DIR__ . '/../partials/talk-offer.php'; ?>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>

  <h2><?= e(site_text('garden_help')) ?></h2>
  <?php if (!$helpRequests): ?><p class="soft"><?= e(site_text('empty_requests')) ?></p><?php else: ?>
    <ul class="list">
      <?php foreach ($helpRequests as $request): ?>
        <li>
          <?= e($request['title']) ?>
          <?php if (!$isSelf): ?>
            <?php $talkOffer = talk_offer($currentUser, (int) $person['id'], true, '/members/' . $person['id'], ['type' => 'seed', 'kind' => 'help', 'id' => (int) $request['id'], 'person' => 0], site_text('talk_offer')); include __DIR__ . '/../partials/talk-offer.php'; ?>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <h2><?= e(site_text('garden_offers')) ?></h2>
  <?php if (!$helpOffers): ?><p class="soft"><?= e(site_text('empty_offers')) ?></p><?php else: ?>
    <ul class="chips"><?php foreach ($helpOffers as $title): ?><li><?= e($title) ?></li><?php endforeach; ?></ul>
  <?php endif; ?>

  <h2><?= e(site_text('garden_support')) ?></h2>
  <?php if (!$supports): ?><p class="soft"><?= e(site_text('empty_rhythm')) ?></p><?php else: ?>
    <ul class="chips"><?php foreach ($supports as $choice): ?><li><?= e($choice) ?></li><?php endforeach; ?></ul>
  <?php endif; ?>

  <h2><?= e(site_text('garden_frequency')) ?></h2>
  <p><?= e($person['contact_frequency'] ?: site_text('garden_frequency_any')) ?></p>

  <h2><?= e(site_text('garden_style')) ?></h2>
  <p><?= e($person['check_in_style'] ?: site_text('garden_style_any')) ?></p>

  <h2><?= e(site_text('garden_stories')) ?></h2>
  <?php if (!$stories): ?><p class="soft"><?= e(site_text('empty_stories')) ?></p><?php else: ?>
    <ul class="list">
      <?php foreach ($stories as $story): ?>
        <li>
          <a href="<?= e(url('/out-there/' . $story['id'])) ?>"><?= e($story['title']) ?></a>
          <span class="soft"> · <?= e(nice_date($story['created_at'])) ?></span>
          <?php if (!empty($story['hidden'])): ?><span class="soft"> · <?= e(site_text('garden_hidden')) ?></span><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <?php if ($isSelf && $warnings): ?>
    <h2><?= e(site_text('garden_warning')) ?></h2>
    <?php foreach ($warnings as $warning): ?>
      <article class="card clay">
        <p><?= e($warning['note']) ?></p>
        <p class="soft"><?= e(nice_date($warning['created_at'])) ?></p>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if ($currentUser && !$isSelf): ?>
    <form method="post" action="<?= e(url('/members/' . $person['id'] . '/report')) ?>">
      <?= csrf_field() ?>
      <label><span><?= e(site_text('label_report')) ?></span><textarea name="reason" maxlength="1000" required></textarea></label>
      <button class="quiet small" type="submit"><?= e(site_text('cta_send_report')) ?></button>
    </form>
  <?php endif; ?>
</section>
