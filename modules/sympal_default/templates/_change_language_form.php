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