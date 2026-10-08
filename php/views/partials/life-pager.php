<?php if (($pages ?? 1) > 1): ?>
  <p class="row-actions">
    <?php if ($page > 1): ?><a class="button quiet small" href="<?= e(url($pagerBase . '?' . http_build_query(array_merge($_GET, ['page' => $page - 1])))) ?>"><?= e(site_text('life_filter_prev')) ?></a><?php endif; ?>
    <span><?= e(site_text('life_filter_page')) ?> <?= e((string) $page) ?> / <?= e((string) $pages) ?></span>
    <?php if ($page < $pages): ?><a class="button quiet small" href="<?= e(url($pagerBase . '?' . http_build_query(array_merge($_GET, ['page' => $page + 1])))) ?>"><?= e(site_text('life_filter_next')) ?></a><?php endif; ?>
  </p>
<?php endif; ?>
