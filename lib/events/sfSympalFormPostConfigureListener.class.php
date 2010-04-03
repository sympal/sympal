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
    $widgetSchema = $form->getWidgetSchema();
    $requiredFields = $form->getRequiredFields();
    $widgetSchema->addOption('required_fields', $requiredFields);
    $widgetSchema->addFormFormatter('table', new sfSympalWidgetFormSchemaFormatterTable($widgetSchema));

    if ($form->hasRecaptcha())
    {
      sfSympalFormToolkit::embedRecaptcha($form);
    }

    if (isset($form['template']))
    {
      sfSympalFormToolkit::changeTemplateWidget($form);
    }

    if (isset($form['module']))
    {
      sfSympalFormToolkit::changeModuleWidget($form);
    }

    if (isset($form['content_id']) || isset($form['content_list']))
    {
      sfSympalFormToolkit::changeContentWidget($form);
    }

    $richDateForms = sfSympalConfig::get('rich_date_forms');
    $formClass = get_class($form);
    if (isset($richDateForms[$formClass]))
    {
      foreach ($form as $name => $field)
      {
        $widget = $field->getWidget();
        if (in_array($name, $richDateForms[$formClass]) && ($widget instanceof sfWidgetFormDateTime || $widget instanceof sfWidgetFormDate))
        {
          sfSympalFormToolkit::changeDateWidget($name, $form);
        }
      }
    }
  }
}