<style type="text/css">
<?php if ($sf_request->getCookie('sympal_inline_edit_mode') == 'true'): ?>
  .sympal_inline_edit_bar_edit_buttons
  {
    display: normal;
  }
  input.toggle_edit_mode
  {
    display: none;
  }
<?php else: ?>
  .sympal_inline_edit_bar_edit_buttons
  {
    display: none;
  }
  input.toggle_edit_mode
  {
    display: normal;
  }
<?php endif; ?>
</style>

<input type="hidden" id="sympal_base_url" value="<?php echo url_for('@homepage', 'absolute=true') ?>" />
<input type="hidden" id="sympal_save_slots_url" value="<?php echo url_for('@sympal_save_content_slots?content_id='.$sf_sympal_content->getId()) ?>" />

<div class="sympal_inline_edit_bar_bottom_background"></div>

<div class="sympal_inline_edit_bar_container">
  <div class="sympal_inline_edit_bar sympal_form">

    <?php if ($sf_user->hasCredential('PublishContent')): ?>
      <div class="sympal_inline_edit_bar_publish">
        <?php if ($sf_sympal_content->getIsPublished()): ?>
          <?php echo link_to(image_tag('/sfSympalPlugin/images/published_icon.png', 'title='.__('Published on %date%', array('%date%' => format_date($sf_sympal_content->getDatePublished(), 'g'))).'. '.__('Click to unpublish content.')), '@sympal_unpublish_content?id='.$sf_sympal_content['id']) ?>
        <?php elseif ($sf_sympal_content->getIsPublishedInTheFuture()): ?>
          <?php echo link_to(image_tag('/sfSympalPlugin/images/future_published_icon.png', 'title='.__('Will publish on %date%', array('%date%' => format_date($sf_sympal_content->getDatePublished(), 'g'))).'. '.__('Click to unpublish content.')), '@sympal_unpublish_content?id='.$sf_sympal_content['id']) ?>
        <?php else: ?>
          <?php echo link_to(image_tag('/sfSympalPlugin/images/unpublished_icon.png', 'title='.__('Has not been published yet.').' '.__('Click to publish content.')), '@sympal_publish_content?id='.$sf_sympal_content['id']) ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php echo get_sympal_inline_edit_bar_buttons() ?>
  </div>
</div>

<div id="sympal_assets"></div>
<div id="sympal_links"></div>
<div id="sympal_objects"></div>

<div id="sympal_slot_errors"></div>
<div id="sympal_slot_errors_icon"></div>