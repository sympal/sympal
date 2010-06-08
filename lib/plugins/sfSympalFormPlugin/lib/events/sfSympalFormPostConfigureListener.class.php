<?php
/**
 * Listener on form.post_configure
 * 
 * @package     sfSympalPlugin
 * @subpackage  events
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-27
 * @version     svn:$Id$ $Author$
 */
class sfSympalFormPostConfigureListener extends sfSympalListener
{
  public function getEventName()
  {
    return 'form.post_configure';
  }

  public function run(sfEvent $event)
  {
    $form = $event->getSubject();
    if ($form instanceof sfFormDoctrine)
    {
      sfSympalFormToolkit::embedI18n($form->getObject(), $form);

      if (sfSympalConfig::get('remove_timestampable_from_forms', null, true))
      {
        unset($form['created_at'], $form['updated_at']);
      }
    }

    if ($form->hasRecaptcha())
    {
      sfSympalFormToolkit::embedRecaptcha($form);
    }

    $this->configureWidgetSchema($form);

    // Converts date fields to rich date fields
    $this->setupRichDateFields($form);
  }

  /**
   * Configures some things on the sfWidgetSchema of the form
   */
  protected function configureWidgetSchema(sfForm $form)
  {
    $widgetSchema = $form->getWidgetSchema();
    $requiredFields = $form->getRequiredFields();
    $widgetSchema->addOption('required_fields', $requiredFields);
    
    $formFormatters = sfSympalConfig::get('form', 'form_formatters', array());
    $catalogue = $widgetSchema->getFormFormatter()->getTranslationCatalogue();
    foreach ($formFormatters as $name => $class)
    {
      $formFormatter = new $class($widgetSchema);

      // persist the translation catalogue
      if ($catalogue)
      {
        $formFormatter->setTranslationCatalogue($catalogue);
      }

      $widgetSchema->addFormFormatter($name, $formFormatter);
    }
  }

  /**
   * Converts some fields to rich date fields
   */
  protected function setupRichDateFields(sfForm $form)
  {
    $richDateForms = sfSympalConfig::get('form', 'rich_date_forms', array());
    $formClass = get_class($form);
    $fields = isset($richDateForms[$formClass]) ? $richDateForms[$formClass] : array();
    
    foreach ($fields as $name)
    {
      $widget = $form[$name]->getWidget();
      if ($widget instanceof sfWidgetFormDateTime || $widget instanceof sfWidgetFormDate)
      {
        sfSympalFormToolkit::changeDateWidget($name, $form);
      }
    }
  }
}