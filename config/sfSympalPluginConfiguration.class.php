<?php

class sfSympalPluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '1.0.0-DEV';

  public static
    $dependencies = array(
      'sfDoctrineGuardPlugin',
      'sfFormExtraPlugin',
      'sfTaskExtraPlugin',
      'sfFeed2Plugin',
      'sfWebBrowserPlugin',
      'sfJqueryReloadedPlugin',
      'sfThumbnailPlugin',
      'sfImageTransformPlugin',
      'sfSympalMenuPlugin',
      'sfSympalPluginManagerPlugin',
      'sfSympalPagesPlugin',
      'sfSympalContentListPlugin',
      'sfSympalDataGridPlugin',
      'sfSympalUserPlugin',
      'sfSympalInstallPlugin',
      'sfSympalUpgradePlugin',
      'sfSympalRenderingPlugin',
      'sfSympalAdminPlugin',
      'sfSympalFrontendEditorPlugin',
      'sfSympalAssetsPlugin'
    );

  public
    $sympalConfiguration;

  public function initialize()
  {
    $this->sympalConfiguration = new sfSympalConfiguration($this->dispatcher, $this->configuration);

    $this->dispatcher->connect('form.post_configure', array($this, 'formPostConfigure'));
  }

  public static function enableSympalPlugins(ProjectConfiguration $configuration)
  {
    require_once(dirname(__FILE__).'/../lib/core/sfSympalPluginEnabler.class.php');

    $enabler = new sfSympalPluginEnabler($configuration);
    $enabler->enableSympalPlugins();
  }

  public function getSympalConfiguration()
  {
    return $this->sympalConfiguration;
  }

  public function formPostConfigure(sfEvent $event)
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
    $requiredFields = $this->_getValidatorSchemaRequiredFields($form->getValidatorSchema(), $widgetSchema->getNameFormat());
    $widgetSchema->addOption('required_fields', $requiredFields);
    $widgetSchema->addFormFormatter('table', new sfSympalWidgetFormSchemaFormatterTable($widgetSchema));
  }

  protected function _getValidatorSchemaRequiredFields(sfValidatorSchema $validatorSchema = null, $format = null)
  {
    $fields = array();

    foreach ($validatorSchema->getFields() as $name => $validator)
    {
      $field = sprintf($format, $name);
      if ($validator instanceof sfValidatorSchema)
      {
        // recur
        $fields = array_merge(
          $fields,
          $this->_getValidatorSchemaRequiredFields($validator, $field.'[%s]')
        );
      }
      else if ($validator->getOption('required'))
      {
        // this field is required
        $fields[] = $field;
      }
    }

    return $fields;
  }
}