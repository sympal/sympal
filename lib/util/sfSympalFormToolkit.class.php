<?php

class sfSympalFormToolkit
{
  public static function embedI18n($name, sfFormDoctrine $form)
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

  public static function embedRichDateWidget($name, sfFormDoctrine $form)
  {
    sfSympalToolkit::useJQuery(array('ui'));

    $widgetSchema = $form->getWidgetSchema();
    $widgetSchema[$name] = new sfWidgetFormJQueryDate();
  }

  public static function embedRecaptcha(sfFormDoctrine $form)
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

  public static function bindFormRecaptcha($form, $recaptcha = false)
  {
    $request = sfContext::getInstance()->getRequest();

    if ($recaptcha)
    {
      $captcha = array(
        'recaptcha_challenge_field' => $request->getParameter('recaptcha_challenge_field'),
        'recaptcha_response_field'  => $request->getParameter('recaptcha_response_field'),
      );
      $form->bind(array_merge($request->getParameter($form->getName()), array('captcha' => $captcha)));
    } else {
      $form->bind($request->getParameter($form->getName())); 
    }
  }

  public static function changeContentSlotValueWidget($contentSlot, $form)
  {
    if ($contentSlot->is_column)
    {
      return;
    }

    $widgetSchema = $form->getWidgetSchema();
    $validatorSchema = $form->getValidatorSchema();

    $contentSlotTypes = sfSympalConfig::get('content_slot_types', null, array());
    $options = $contentSlotTypes[$contentSlot->type];

    $widgetClass = isset($options['widget_class']) ? $options['widget_class'] : 'sfWidgetFormSympal'.$contentSlot->type;
    $widgetClass = class_exists($widgetClass) ? $widgetClass : 'sfWidgetFormInput';
    $widgetOptions = isset($options['widget_options']) ? $options['widget_options'] : array();

    $validatorClass = isset($options['validator_class']) ? $options['validator_class'] : 'sfValidatorFormSympal'.$contentSlot->type;
    $validatorClass = class_exists($validatorClass) ? $validatorClass : 'sfValidatorPass';
    $validatorOptions = isset($options['validator_options']) ? $options['validator_options'] : array();

    $widgetSchema['value'] = new $widgetClass($widgetOptions);
    $validatorSchema['value'] = new $validatorClass($validatorOptions);
  }

  public static function changeLayoutWidget($form)
  {
    $array = self::getLayoutWidgetAndValidator();

    $form->setWidget('layout', $array['widget']);
    $form->setValidator('layout', $array['validator']);
  }

  public static function getLayoutWidgetAndValidator()
  {
    $all = sfContext::getInstance()->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration()->getLayouts();
    $layouts = array('' => '');
    foreach ($all as $path => $name)
    {
      $info = pathinfo($path);
      $layouts[$info['filename']] = sfInflector::humanize($name);
    }
    $widget = new sfWidgetFormChoice(array(
      'choices'   => $layouts
    ));
    $validator = new sfValidatorChoice(array(
      'choices'   => array_keys($layouts),
      'required' => false
    ));
    return array('widget' => $widget, 'validator' => $validator);
  }
}