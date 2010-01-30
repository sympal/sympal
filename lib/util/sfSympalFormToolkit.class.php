<?php

class sfSympalFormToolkit
{
  public static function embedI18n($name, sfForm $form)
  {
    if (sfSympalConfig::isI18nEnabled($name))
    {
      $context = sfContext::getInstance();
      $culture = $context->getUser()->getCulture();
      $form->embedI18n(array(strtolower($culture)));
      $widgetSchema = $form->getWidgetSchema();
      $context->getConfiguration()->loadHelpers(array('Helper'));

      $c = sfCultureInfo::getInstance($culture);
      $languages = $c->getLanguages();
      $language = isset($languages[$culture]) ? $languages[$culture] : '';
      $widgetSchema[$culture]->setLabel($language);
    }
  }

  public static function changeContentSlotTypeWidget(sfForm $form, $blank = false)
  {
    $widgetSchema = $form->getWidgetSchema();
    $validatorSchema = $form->getValidatorSchema();
    $slotTypes = (sfSympalConfig::get('content_slot_types', null, array()));
    $choices = array();
    if ($blank)
    {
      $choices[''] = '';
    }
    foreach ($slotTypes as $key => $value)
    {
      $choices[$key] = $value['label'];
    }
    $widgetSchema['type'] = new sfWidgetFormChoice(array('choices' => $choices));
    $validatorSchema['type'] = new sfValidatorChoice(array('required' => false, 'choices' => array_keys($choices)));
  }

  public static function changeContentWidget(sfForm $form, $add = null)
  {
    $widgetSchema = $form->getWidgetSchema();
    $validatorSchema = $form->getValidatorSchema();
    if (is_null($add))
    {
      $key = isset($widgetSchema['content_id']) ? 'content_id' : 'content_list';
    } else {
      $key = $add;
    }
    if ((isset($widgetSchema[$key]) && $widgetSchema[$key] instanceof sfWidgetFormDoctrineChoice) || $add)
    {
      $q = Doctrine_Core::getTable('sfSympalContent')
        ->createQuery('c')
        ->leftJoin('c.Type t')
        ->leftJoin('c.MenuItem m')
        ->where('c.site_id = ?', sfSympalContext::getInstance()->getSite()->getId())
        ->orderBy('m.root_id, m.lft');

      if ($add)
      {
        $widgetSchema[$key] = new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContent'));
        $validatorSchema[$key] = new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfSympalContent', 'required' => false));
      }

      $widgetSchema[$key]->setOption('query', $q);
      $widgetSchema[$key]->setOption('method', 'getIndented');
    }
  }

  public static function changeModuleWidget(sfForm $form)
  {
    $modules = sfContext::getInstance()->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration()->getModules();
    $options = array('' => '');
    foreach ($modules as $module)
    {
      $options[$module] = $module;
    }
    $widgetSchema = $form->getWidgetSchema();
    $widgetSchema['module'] = new sfWidgetFormChoice(array(
      'choices'   => $options
    ));
    $validatorSchema = $form->getValidatorSchema();
    $validatorSchema['module'] = new sfValidatorChoice(array(
      'choices'   => array_keys($options),
      'required' => false
    ));
  }

  public static function changeDateWidget($name, sfForm $form)
  {
    sfSympalToolkit::useJQuery(array('ui'));
    sfSympalToolkit::useStylesheet('/sfSympalPlugin/css/jqueryui/jquery-ui.css');

    $widgetSchema = $form->getWidgetSchema();
    $widgetSchema[$name] = new sfWidgetFormJQueryDate();
  }

  public static function embedRecaptcha(sfForm $form)
  {
    $publicKey = sfSympalConfig::get('recaptcha_public_key');
    $privateKey = sfSympalConfig::get('recaptcha_private_key');

    if (!$publicKey || !$privateKey) {
      throw new sfException('You must specify the recaptcha public and private key in your sympal configuration');
    }

    $widgetSchema = $form->getWidgetSchema();
    $validatorSchema = $form->getValidatorSchema();

    $widgetSchema['captcha'] = new sfWidgetFormReCaptcha(array(
      'public_key' => $publicKey
    ));

    $validatorSchema['captcha'] = new sfValidatorReCaptcha(array(
      'private_key' => $privateKey
    ));
  }

  public static function changeContentSlotValueWidget(sfSympalContentSlot $contentSlot, sfForm $form)
  {
    if ($contentSlot->is_column)
    {
      return;
    }

    $type = $contentSlot->type ? $contentSlot->type : 'Text';

    $widgetSchema = $form->getWidgetSchema();
    $validatorSchema = $form->getValidatorSchema();

    $contentSlotTypes = sfSympalConfig::get('content_slot_types', null, array());
    $options = isset($contentSlotTypes[$type]) ? $contentSlotTypes[$type] : array();

    $widgetClass = isset($options['widget_class']) ? $options['widget_class'] : 'sfWidgetFormSympal'.$type;
    $widgetOptions = isset($options['widget_options']) ? $options['widget_options'] : array();

    $validatorClass = isset($options['validator_class']) ? $options['validator_class'] : 'sfValidatorFormSympal'.$type;
    $validatorOptions = isset($options['validator_options']) ? $options['validator_options'] : array();
    $validatorOptions['required'] = false;

    $widgetSchema['value'] = new $widgetClass($widgetOptions);
    $validatorSchema['value'] = new $validatorClass($validatorOptions);
  }

  public static function changeThemeWidget(sfForm $form)
  {
    $array = self::getThemeWidgetAndValidator();

    $form->setWidget('theme', $array['widget']);
    $form->setValidator('theme', $array['validator']);
  }

  public static function changeTemplateWidget(sfForm $form)
  {
    $array = self::getTemplateWidgetAndValidator($form);

    $form->setWidget('template', $array['widget']);
    $form->setValidator('template', $array['validator']);
  }

  public static function getTemplateWidgetAndValidator(sfForm $form)
  {
    $object = $form->getObject();
    if ($object instanceof sfSympalContent)
    {
      $type = $object->getType()->getSlug();
    } else if ($object instanceof sfSympalContentType) {
      $type = $object->getSlug();
    } else {
      throw new InvalidArgumentException('Form must be an instance of sfSympalContentForm or sfSympalContentTypeForm');
    }

    $templates = sfContext::getInstance()->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration()->getContentTemplates($type);
    $options = array('' => '');
    foreach ($templates as $name => $template)
    {
      $options[$name] = sfInflector::humanize($name);
    }
    $widget = new sfWidgetFormChoice(array(
      'choices'   => $options
    ));
    $validator = new sfValidatorChoice(array(
      'choices'   => array_keys($options),
      'required' => false
    ));
    return array('widget' => $widget, 'validator' => $validator);
  }

  public static function getThemeWidgetAndValidator()
  {
    $themes = sfContext::getInstance()->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration()->getThemes();
    $options = array('' => '');
    foreach ($themes as $name => $theme)
    {
      $options[$name] = sfInflector::humanize($name);
    }
    $widget = new sfWidgetFormChoice(array(
      'choices'   => $options
    ));
    $validator = new sfValidatorChoice(array(
      'choices'   => array_keys($options),
      'required' => false
    ));
    return array('widget' => $widget, 'validator' => $validator);
  }
}