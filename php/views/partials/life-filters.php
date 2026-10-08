<form method="get" action="<?= e(url($filterAction)) ?>">
  <?php if (!empty($filterTypes)): ?>
    <label><span><?= e(site_text('life_filter_type')) ?></span>
      <select name="type">
        <option value=""><?= e(site_text('life_filter_any')) ?></option>
        <?php foreach ($filterTypes as $code => $label): ?>
          <option value="<?= e((string) $code) ?>"<?= (($filterType ?? '') === (string) $code) ? ' selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  <?php endif; ?>
  <label><span><?= e(site_text('life_filter_search')) ?></span><input type="search" name="q" value="<?= e((string) ($filters['q'] ?? '')) ?>"></label>
  <?php if (!empty($filterStatuses)): ?>
    <label><span><?= e(site_text('life_filter_status')) ?></span>
      <select name="status">
        <option value=""><?= e(site_text('life_filter_any')) ?></option>
        <?php foreach ($filterStatuses as $code => $label): ?>
          <option value="<?= e((string) $code) ?>"<?= (($filters['status'] ?? '') === (string) $code) ? ' selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  <?php endif; ?>
  <label><span><?= e(site_text('life_filter_from')) ?></span><input type="date" name="from" value="<?= e((string) ($filters['from'] ?? '')) ?>"></label>
  <label><span><?= e(site_text('life_filter_to')) ?></span><input type="date" name="to" value="<?= e((string) ($filters['to'] ?? '')) ?>"></label>
  <button type="submit"><?= e(site_text('life_filter_apply')) ?></button>
</form>
