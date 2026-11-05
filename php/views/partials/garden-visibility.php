<?php if (function_exists('garden_visibility_ready') && garden_visibility_ready()): ?>
  <?php $gardenVisible = !isset($gardenVisible) || $gardenVisible; ?>
  <fieldset class="choices-set">
    <legend><?= e(site_text('garden_visibility')) ?></legend>
    <p class="soft"><?= e(site_text('garden_visibility_note')) ?></p>
    <div class="choices">
      <label>
        <input type="radio" name="garden_visible" value="visible"<?= $gardenVisible ? ' checked' : '' ?>>
        <span class="choice-title"><?= e(site_text('garden_members_visible')) ?></span>
      </label>
      <label>
        <input type="radio" name="garden_visible" value="hidden"<?= $gardenVisible ? '' : ' checked' ?>>
        <span class="choice-title"><?= e(site_text('garden_members_hidden')) ?></span>
      </label>
    </div>
  </fieldset>
<?php endif; ?>
