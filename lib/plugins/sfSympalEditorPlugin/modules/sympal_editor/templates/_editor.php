<input type="hidden" id="sympal_base_url" value="<?php echo url_for('@homepage', 'absolute=true') ?>" />
<input type="hidden" id="sympal_save_slots_url" value="<?php echo url_for('@sympal_save_content_slots?content_id='.$sf_sympal_content->getId()) ?>" />

<div class="sympal_inline_edit_bar_bottom_background"></div>

<div class="sympal_inline_edit_bar_container">
  <div class="sympal_inline_edit_bar sympal_form">
    <?php use_helper('SympalContentSlotEditor') ?>
    <?php echo get_sympal_inline_edit_bar_buttons() ?>
  </div>
</div>

<div id="sympal_chooser_container" class="sympal_inline_edit_bar_area"></div>
