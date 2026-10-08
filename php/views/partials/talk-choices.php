<?php $talkSelected = (string) ($talkSelected ?? ''); ?>
<div class="choices">
  <label>
    <input type="radio" name="conversations_pref" value="anyone" required<?= $talkSelected === 'anyone' ? ' checked' : '' ?>>
    <span class="choice-title"><?= e(site_text('talk_pref_anyone')) ?></span>
    <p class="soft"><?= e(site_text('talk_pref_anyone_note')) ?></p>
  </label>
  <label>
    <input type="radio" name="conversations_pref" value="context"<?= $talkSelected === 'context' ? ' checked' : '' ?>>
    <span class="choice-title"><?= e(site_text('talk_pref_context')) ?> <span class="soft"><?= e(site_text('talk_pref_recommended')) ?></span></span>
    <p class="soft"><?= e(site_text('talk_pref_context_note')) ?></p>
  </label>
  <label>
    <input type="radio" name="conversations_pref" value="none"<?= $talkSelected === 'none' ? ' checked' : '' ?>>
    <span class="choice-title"><?= e(site_text('talk_pref_none')) ?></span>
    <p class="soft"><?= e(site_text('talk_pref_none_note')) ?></p>
  </label>
</div>
