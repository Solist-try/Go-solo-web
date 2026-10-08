<?php $notesHeading = $notesHeading ?? site_text('notices_title'); ?>
<h2><?= e($notesHeading) ?></h2>
<?php if (!$comments): ?><p class="soft"><?= e($notesEmpty ?? site_text('empty_notes')) ?></p><?php endif; ?>
<div class="stack">
  <?php foreach ($comments as $comment): ?>
    <article class="card">
      <?php if (!empty($comment['hidden'])): ?><p class="soft"><?= e(site_text('note_hidden')) ?></p><?php endif; ?>
      <p class="soft"><a href="<?= e(url('/members/' . $comment['user_id'])) ?>"><?= e($comment['display_name'] ?: site_text('garden_member')) ?></a> · <?= e(nice_date($comment['created_at'])) ?></p>
      <?php if (!empty($talkPost) && $currentUser && empty($comment['hidden']) && (int) $currentUser['id'] === (int) $talkPost['author'] && (int) $currentUser['id'] !== (int) $comment['user_id']): ?>
        <?php $talkOffer = talk_offer($currentUser, (int) $comment['user_id'], true, (string) $talkPost['back'], ['type' => $talkPost['type'], 'kind' => 'post', 'id' => (int) $talkPost['id'], 'person' => (int) $comment['user_id']], site_text('talk_hello')); include __DIR__ . '/talk-offer.php'; ?>
      <?php endif; ?>
      <?= render_writing((string) $comment['body']) ?>
      <?php $images = $comment['images'] ?? []; include __DIR__ . '/photos.php'; ?>
      <?php if ($currentUser && (int) $currentUser['id'] === (int) $comment['user_id']): ?>
        <form method="post" action="<?= e(url('/comments/' . $comment['id'] . '/delete')) ?>">
          <?= csrf_field() ?>
          <button class="quiet small" type="submit"><?= e(site_text('cta_remove_note')) ?></button>
        </form>
      <?php endif; ?>
      <?php if (is_steward()): ?>
        <form method="post" action="<?= e(url('/steward/comments/' . $comment['id'])) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= !empty($comment['hidden']) ? 'show' : 'hide' ?>">
          <button class="quiet small" type="submit"><?= !empty($comment['hidden']) ? 'Show this note' : 'Hide this note' ?></button>
        </form>
      <?php endif; ?>
      <?php if ($currentUser && (int) $currentUser['id'] !== (int) $comment['user_id']): ?>
        <form method="post" action="<?= e(url('/comments/' . $comment['id'] . '/report')) ?>">
          <?= csrf_field() ?>
          <label><span><?= e(site_text('label_report_note')) ?></span><textarea name="reason" maxlength="1000" required></textarea></label>
          <button class="quiet small" type="submit"><?= e(site_text('cta_send_report')) ?></button>
        </form>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</div>
<?php if (!empty($canReply) && !empty($editor)): ?>
  <?php include __DIR__ . '/editor.php'; ?>
<?php elseif (!empty($closedNote)): ?>
  <p class="soft"><?= e($closedNote) ?></p>
<?php endif; ?>
