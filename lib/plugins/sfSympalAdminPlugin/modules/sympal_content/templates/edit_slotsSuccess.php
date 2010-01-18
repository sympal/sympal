<?php sympal_use_stylesheet('/sfSympalAdminPlugin/css/editor.css', 'last') ?>

<div id="sf_admin_container">
  <h1>Editing "<?php echo $sf_sympal_content ?>" Slots</h1>

  <div id="sf_admin_content">
    <div class="sf_admin_form">
      <?php foreach ($sf_sympal_content->getSlots() as $slot): ?>
        <?php if (!$slot->is_column): ?>
          <fieldset id="sf_fieldset_<?php echo $slot->getName() ?>">
            <h2><?php echo $slot ?></h2>
            <div class="sf_admin_form_row">
              <?php echo get_sympal_content_slot_editor($sf_sympal_content, $slot) ?>
            </div>
          </fieldset>
        <?php endif; ?>
      <?php endforeach; ?>

      <div class="sympal_inline_edit_bar sympal_form">
        <ul class="sympal_inline_edit_bar_buttons">
          <li><input type="button" class="sympal_save_content_slots" name="save" value="Save" /></li>
        </ul>
        <ul>
          <li><?php echo button_to('Go back to Editing Content', $sf_sympal_content->getEditRoute()) ?></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php echo get_sympal_editor() ?>