<?php if ($menu = $menu->render()): ?>
  <div id="sympal_editor">
    <?php echo $menu ?>
    <a class="sympal_close_menu">close</a>
  </div>
<?php endif; ?>

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

<div class="sympal_inline_edit_bar_background"></div>

<div class="sympal_inline_edit_bar_container">
  <div class="sympal_inline_edit_bar sympal_form">

    <?php if (!$sf_sympal_context->isAdminModule()): ?>
      <div class="sympal_inline_edit_admin_menu">
        <?php echo get_sympal_admin_menu() ?>
      </div>
    <?php endif; ?>

    <ul class="sympal_inline_edit_bar_big_buttons">
      <?php if (sfSympalConfig::isI18nEnabled()): ?>
        <li>
          <?php
          $user = sfContext::getInstance()->getUser();
          $form = new sfFormLanguage($user, array('languages' => sfSympalConfig::get('language_codes', null, array($user->getCulture()))));
          unset($form[$form->getCSRFFieldName()]);
          $widgetSchema = $form->getWidgetSchema();
          $widgetSchema['language']->setAttribute('onChange', "this.form.submit();");
          ?>

          <?php echo $form->renderFormTag(url_for('@sympal_change_language_form')) ?>
            <?php echo $form['language'] ?>
          </form>
        </li>
      <?php endif; ?>
      <li><input type="button" class="toggle_editor_menu" name="toggle_editor_menu" value="<?php echo __('Editor Menu') ?>" title="<?php echo __('Click to toggle Sympal editor menu') ?>" /></li>
      <li><input type="button" class="toggle_dashboard_menu" value="<?php echo __('Dashboard') ?>" rel="<?php echo url_for('@sympal_dashboard') ?>" /></li>
      <li><input type="button" class="toggle_edit_mode" value="<?php echo __('Enable Edit Mode') ?>" /></li>
    </ul>

    <ul class="sympal_inline_edit_bar_big_buttons sympal_inline_edit_bar_buttons">
      <li><input type="button" class="toggle_sympal_assets" name="assets" rel="<?php echo url_for('@sympal_assets_select') ?>" value="<?php echo __('Assets') ?>" /></li>
      <li><input type="button" class="toggle_sympal_links" name="links" rel="<?php echo url_for('@sympal_editor_links') ?>" value="<?php echo __('Links') ?>" /></li>
      <li><input type="button" class="sympal_save_content_slots" name="save" value="<?php echo __('Save') ?>" /></li>
      <li><input type="button" class="sympal_preview_content_slots" name="preview" value="<?php echo __('Preview') ?>" /></li>
      <li><input type="button" class="sympal_disable_edit_mode" name="disable_edit_mode" value="<?php echo __('Disable Edit Mode') ?>" /></li>
    </ul>
  </div>
</div>

<div id="sympal_assets"></div>
<div id="sympal_links"></div>
<div id="sympal_dashboard"></div>