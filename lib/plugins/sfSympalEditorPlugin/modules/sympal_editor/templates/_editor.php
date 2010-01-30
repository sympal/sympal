<style type="text/css">
<?php if ($sf_request->getCookie('sympal_inline_edit_mode') == 'true'): ?>
  .sympal_inline_edit_bar_buttons
  {
    display: normal;
  }
  input.toggle_edit_mode
  {
    display: none;
  }
<?php else: ?>
  .sympal_inline_edit_bar_buttons
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
  
    <div class="sympal_inline_edit_bar_publish">
      <?php if ($sf_sympal_content->getIsPublished()): ?>
        <?php echo link_to(image_tag('/sfSympalPlugin/images/published_icon.png', 'title='.__('Published on %date%', array('%date%' => format_date($sf_sympal_content->getDatePublished(), 'g'))).'. '.__('Click to unpublish content.')), '@sympal_unpublish_content?id='.$sf_sympal_content['id']) ?>
      <?php elseif ($sf_sympal_content->getIsPublishedInTheFuture()): ?>
        <?php echo link_to(image_tag('/sfSympalPlugin/images/future_published_icon.png', 'title='.__('Will publish on %date%', array('%date%' => format_date($sf_sympal_content->getDatePublished(), 'g'))).'. '.__('Click to unpublish content.')), '@sympal_unpublish_content?id='.$sf_sympal_content['id']) ?>
      <?php else: ?>
        <?php echo link_to(image_tag('/sfSympalPlugin/images/unpublished_icon.png', 'title='.__('Has not been published yet.').' '.__('Click to publish content.')), '@sympal_publish_content?id='.$sf_sympal_content['id']) ?>
      <?php endif; ?>
    </div>

    <div class="sympal_inline_edit_signout">
      <?php echo link_to(image_tag('/sfSympalPlugin/images/signout.png', 'title='.__('Signout')), '@sympal_signout', 'confirm='.__('Are you sure you want to signout?')) ?>
    </div>

    <ul class="sympal_inline_edit_bar_big_buttons">
      <?php if (sfSympalConfig::isI18nEnabled()): ?>
        <li>
          <?php
          $user = sfContext::getInstance()->getUser();
          $form = new sfFormLanguage($user, array('languages' => sfSympalConfig::getLanguageCodes()));
          unset($form[$form->getCSRFFieldName()]);
          $widgetSchema = $form->getWidgetSchema();
          $widgetSchema['language']->setAttribute('onChange', "this.form.submit();");
          ?>

          <?php echo $form->renderFormTag(url_for('@sympal_change_language_form')) ?>
            <?php echo $form['language'] ?>
          </form>
        </li>
      <?php endif; ?>
      <li><input type="button" class="toggle_dashboard_menu" value="<?php echo __('Dashboard') ?>" rel="<?php echo url_for('@sympal_dashboard') ?>" /></li>

      <?php if ($sf_sympal_content->getEditableSlotsExistOnPage()): ?>
        <li><input type="button" class="toggle_edit_mode" value="<?php echo __('Enable Edit Mode') ?>" /></li>
      <?php endif; ?>
    </ul>

    <ul class="sympal_inline_edit_bar_big_buttons sympal_inline_edit_bar_buttons">
      <li><input type="button" class="toggle_sympal_assets" name="assets" rel="<?php echo url_for('@sympal_assets_select') ?>" value="<?php echo __('Assets') ?>" /></li>
      <li><input type="button" class="toggle_sympal_links" name="links" rel="<?php echo url_for('@sympal_editor_links') ?>" value="<?php echo __('Links') ?>" /></li>
      <li><input type="button" class="toggle_sympal_objects" name="objects" rel="<?php echo url_for('@sympal_editor_objects') ?>" value="<?php echo __('Objects') ?>" /></li>

      <?php if ($sf_sympal_content->getEditableSlotsExistOnPage()): ?>
        <li><input type="button" class="sympal_save_content_slots" name="save" value="<?php echo __('Save') ?>" /></li>
        <li><input type="button" class="sympal_preview_content_slots" name="preview" value="<?php echo __('Preview') ?>" /></li>
        <li><input type="button" class="sympal_disable_edit_mode" name="disable_edit_mode" value="<?php echo __('Disable Edit Mode') ?>" /></li>
      <?php endif; ?>
    </ul>
  </div>
</div>

<div id="sympal_assets"></div>
<div id="sympal_links"></div>
<div id="sympal_objects"></div>
<div id="sympal_dashboard"></div>
<div id="sympal_slot_errors"></div>
<div id="sympal_slot_errors_icon"></div>