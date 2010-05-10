<?php

/**
 * Form that allows you to edit app.yml values and then writes them out
 * to the application's app.yml
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  form
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalConfigForm extends BaseForm
{
  protected
    $_settings = array(),
    $_path;

  /**
   * Overridden to allow the app to add to the form and then to process
   * those additions and turn them into one giant, embedded form
   */
  public function setup()
  {
    // allow the application to add fields to the form
    self::$dispatcher->notify(new sfEvent($this, 'sympal.load_config_form'));

    $otherSettings = array();
    foreach ($this->_settings as $group => $settings)
    {
      if (!is_numeric($group))
      {
        $form = new BaseForm();
        foreach ($settings as $setting)
        {
          $form->setWidget($setting['name'], $setting['widget']);
          $form->getWidgetSchema()->setLabel($setting['name'], $setting['label']);
          
          $form->setValidator($setting['name'], $setting['validator']);
        }
        $this->embedForm($group, $form);
      }
      else
      {
        $otherSettings[] = $settings;
      }
    }

    foreach ($otherSettings as $setting)
    {
      $this->setWidget($setting['name'], $setting['widget']);
      $this->getWidgetSchema()->setLabel($setting['name'], $setting['label']);

      $this->setValidator($setting['name'], $setting['validator']);
    }

    $defaults = $this->getDefaults();
    foreach ($this as $key => $value)
    {
      if ($value instanceof sfFormFieldSchema)
      {
        foreach ($value as $k => $v)
        {
          $defaults[$key][$k] = sfSympalConfig::get($key, $k);
        }
      } else {
        $defaults[$key] = sfSympalConfig::get($key);
      }
    }

    $this->setDefaults($defaults);

    $this->widgetSchema->setNameFormat('settings[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  /**
   * Adds a setting to the form. This is the main interface for adding
   * to the config form
   * 
   * @param string $group  The config group (can be null of at root of sympal_config)
   * @param string $name   The name of the config
   * @param string $label  The label to use in the form
   * @param string $widget The sfWidgetForm%%% class to use for the widget
   * @param string $validator The sfValidator%%% class to use as the validator
   */
  public function addSetting($group, $name, $label = null, $widget = 'Input', $validator = 'String')
  {
    if ($label === null)
    {
      $label = sfInflector::humanize($name);
    }

    if (!is_object($widget))
    {
      $widgetClass = 'sfWidgetForm' . $widget;
      $widget = new $widgetClass();
    }

    if (!is_object($validator))
    {
      $validatorClass = 'sfValidator' . $validator;
      $validator = new $validatorClass();
    }

    $validator->setOption('required', false);

    $node = array(
      'name' => $name,
      'label' => $label,
      'widget' => $widget,
      'validator' => $validator
    );

    if (!$group)
    {
      $this->_settings[] = $node;
    } else {
      $this->_settings[$group][] = $node;
    }
  }

  /**
   * Actually builds an array of yaml and writes to the appropriate yaml file
   */
  public function save()
  {
    $array = $this->_buildArrayToWrite();

    $this->_path = sfConfig::get('sf_app_dir').'/config/app.yml';

    file_put_contents($this->_path, sfYaml::dump($array, 4));

    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfCacheClearTask(sfApplicationConfiguration::getActive()->getEventDispatcher(), new sfFormatter());
    $task->run(array(), array('type' => 'config'));
  }

  /**
   * Builds the array of config values from the cleaned values that will
   * be used to dump to yaml.
   */
  protected function _buildArrayToWrite()
  {
    $old = $this->getDefaults();
    $new = $this->getValues();

    $array = array();
    $array['all']['sympal_config'] = array();

    // Add only the values that have changed from the old default values
    foreach ($new as $key => $value)
    {
      if ($value != $old[$key])
      {
        $array['all']['sympal_config'][$key] = $value;
      }
    }

    // Merge in existing values from the current app.yml file
    $array = sfToolkit::arrayDeepMerge(
      sfYaml::load(sfConfig::get('sf_app_dir').'/config/app.yml'),
      $array
    );

    // Remove values that don't exist anymore
    foreach ($array['all']['sympal_config'] as $key => $value)
    {
      if (!array_key_exists($key, $new))
      {
        unset($array['all']['sympal_config'][$key]);
      }
    }

    return $array;
  }

  /**
   * Returns an array of the named groups in this form, including "General",
   * which is a pseudo-group containing all non-embedded fields
   * 
   * @return array
   */
  public function getGroups()
  {
    $groups = array('General');
    foreach ($this as $key => $value)
    {
      if ($value instanceof sfFormFieldSchema)
      {
        $groups[] = $key;
      }
    }
    return $groups;
  }

  /**
   * Returns an array of the field names that live beneath the given group
   * 
   * @param string $name The name of the group to get the fields/settings for
   * @return array An array of the field names in the given group (which is an embedded form)
   */
  public function getGroupSettings($name)
  {
    $settings = array();
    foreach ($this[$name] as $key => $value)
    {
      $settings[] = $key;
    }
    
    return $settings;
  }

  /**
   * Called by the view to render an entire config group form
   * 
   * @param string $name The name of the group to render
   */
  public function renderGroup($name)
  {
    if ($name == 'General')
    {
      $settings = array();
      foreach ($this as $key => $value)
      {
        if (!$value instanceof sfFormFieldSchema)
        {
          $settings[] = $key;
        }
      }
      $html = $this->renderFieldSet($this, $settings);
    }
    else
    {
      $settings = $this->getGroupSettings($name);
      $html = $this->renderFieldSet($this[$name], $settings);
    }
    return $html;
  }

  /**
   * Renders a particular fieldset.
   * 
   * Just dumps out an array of fields with the needed markup
   * 
   * @param mixed $form   Either an sfForm or sfFormFieldSchema (embedded form) object
   * @param array $fields The array of fields to render on the above
   * 
   * @return string The rendered html
   */
  public function renderFieldSet($form, $fields)
  {
    $html = '';
    foreach ($fields as $field)
    {
      if (!$form[$field]->isHidden())
      {
        $html .= '<div class="sf_admin_form_row">';
        $html .= $form[$field]->renderLabel();
        $html .= $form[$field];
        $html .= $form[$field]->renderHelp();
        $html .= '</div>';
      }
    }
    
    return $html;
  }
}