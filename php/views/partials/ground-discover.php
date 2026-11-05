<?php if (function_exists('ground_discoverable_ready') && ground_discoverable_ready()): ?>
  <?php $discoverable = !isset($discoverable) || $discoverable; ?>
  <fieldset class="choices-set">
    <legend><?= e(site_text('ground_legend')) ?></legend>
    <p class="soft"><?= e(site_text('ground_note')) ?></p>
    <div class="choices">
      <label>
        <input type="radio" name="discoverable" value="open"<?= $discoverable ? ' checked' : '' ?>>
        <span class="choice-title"><?= e(site_text('ground_open')) ?></span>
      </label>
      <label>
        <input type="radio" name="discoverable" value="quiet"<?= $discoverable ? '' : ' checked' ?>>
        <span class="choice-title"><?= e(site_text('ground_quiet')) ?></span>
      </label>
    </div>
  </fieldset>
<?php endif; ?>
