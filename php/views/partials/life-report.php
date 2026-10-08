<?php if (table_has_column('reports', 'category')): ?>
  <label><span><?= e(site_text('life_report_category')) ?> <span class="soft"><?= e(site_text('life_optional')) ?></span></span>
    <select name="category">
      <option value=""><?= e(site_text('life_report_unset')) ?></option>
      <option value="concern"><?= e(site_text('life_report_concern')) ?></option>
      <option value="safety"><?= e(site_text('life_report_safety')) ?></option>
      <option value="other"><?= e(site_text('life_report_other')) ?></option>
    </select>
  </label>
<?php endif; ?>
