<?php $talkOffer = $talkOffer ?? ['mode' => 'none']; ?>
<?php if (($talkOffer['mode'] ?? '') === 'paused'): ?>
  <p class="soft"><?= e(site_text('talk_paused_other')) ?></p>
<?php elseif (($talkOffer['mode'] ?? '') === 'link' && ($talkOffer['href'] ?? '') !== ''): ?>
  <p><a class="button quiet small" href="<?= e(url((string) $talkOffer['href'])) ?>"><?= e((string) $talkOffer['label']) ?></a></p>
<?php endif; ?>
