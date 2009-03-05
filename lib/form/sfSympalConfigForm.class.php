<?php

class sfSympalConfigForm extends sfForm
{
  protected
    $_settings = array(),
    $_path;

  public function setup()
  {
    sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.load_settings_form'));

    $otherSettings = array();
    foreach ($this->_settings as $group => $settings)
    {
      if (!is_numeric($group))
      {
        $form = new sfForm();
        foreach ($settings as $setting)
        {
          $setting['widget']->setLabel($setting['label']);

          $form->setWidget($setting['name'], $setting['widget']);
          $form->setValidator($setting['name'], $setting['validator']);
        }
        $this->embedForm($group, $form);
      } else {
        $otherSettings[] = $settings;
      }
    }

    foreach ($otherSettings as $setting)
    {
      $this->setWidget($setting['name'], $setting['widget']);
      $setting['widget']->setLabel($setting['label']);
      $this->setValidator($setting['name'], $setting['validator']);
    }

    $path = sfConfig::get('sf_config_dir').'/app.yml';
    $array = sfYaml::load($path);
    if (!$array)
    {
      $path = dirname(__FILE__) . '/../../config/app.yml';
      $array = sfYaml::load($path);
    }
    if (isset($array['all']['sympal_settings']) && is_array($array['all']['sympal_settings']))
    {
      $this->setDefaults($array['all']['sympal_settings']);
    }

    $this->widgetSchema->setNameFormat('settings[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function addSetting($group, $name, $label, $widget = 'Input', $validator = 'String')
  {
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

  public function save()
  {
    $array = $this->_buildArrayToWrite();

    sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.settings_form.save'), array('values' => $this->getValues()));

    file_put_contents($this->_path, sfYaml::dump($array, 4));
  }

  protected function _buildArrayToWrite()
  {
    $this->_path = sfConfig::get('sf_config_dir').'/app.yml';

    $array = sfYaml::load($this->_path);
    $arr = array();
    $arr['all']['sympal_settings'] = $this->getValues();
    $array = sfToolkit::arrayDeepMerge($array, $arr);

    return $array;
  }
}