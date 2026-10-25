<?php $unreadCount = (int) ($conversation['unread_count'] ?? 0); ?>
<?php if ($unreadCount > 0): ?><span class="unread-count"><?= e(site_text('talk_unread')) ?> <?= e((string) $unreadCount) ?></span><?php endif; ?>
