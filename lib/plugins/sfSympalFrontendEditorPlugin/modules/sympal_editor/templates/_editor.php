<?php if ($menu = $menu->render()): ?>
  <div id="sympal_editor">
    <?php echo $menu ?>
  </div>
<?php endif; ?>

<div class="sympal_inline_edit_bar sympal_form">
  <div class="sympal_inline_edit_bar_container">
    <ul>
      <li><input type="button" class="toggle_editor_menu" name="toggle_editor_menu" value="Editor Menu" /></li>
      <li><?php echo button_to('Go to My Dashboard', '@sympal_dashboard', array('class' => 'sympal_dashboard')) ?></li>
      <li><input type="button" class="toggle_edit_mode" value="Enable Edit Mode" /></li>
    </ul>

    <ul class="sympal_inline_edit_bar_buttons">
      <li><input type="button" class="sympal_save_content_slots" name="save" value="Save" /></li>
      <li><input type="button" class="sympal_preview_content_slots" name="preview" value="Preview" /></li>
      <li><?php echo button_to('Edit in Backend', $content->getEditRoute()) ?></li>
      <li><input type="button" class="sympal_disable_edit_mode" name="disable_edit_mode" value="Disable Edit Mode" /></li>
    </ul>
  </div>

  <?php if (sfSympalConfig::isI18nEnabled()): ?>
    <div class="sympal_inline_edit_bar_change_language">
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
    </div>
  <?php endif; ?>
</div>