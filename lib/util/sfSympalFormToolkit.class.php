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
    sfApplicationConfiguration::getActive()->loadHelpers('jQuery');

    $response = sfContext::getInstance()->getResponse();
    $response->addStylesheet('/sfSympalPlugin/jquery/css/jquery-ui.css');
    $response->addStylesheet('/sfSympalPlugin/jquery/css/ui.theme.css');
    $response->addJavascript('/sfSympalPlugin/jquery/js/jquery-ui.min.js');
    $response->addJavascript('/sfSympalPlugin/jquery/js/jquery.bgiframe.min.js');
    $response->addJavascript('/sfSympalPlugin/jquery/js/jquery-ui-i18n.min.js');

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
    $type = $contentSlot->Type;

    $class = 'sfWidgetFormSympal'.$type->name;

    if (!class_exists($class))
    {
      $class = 'sfWidgetFormInput';
    }

    $widget = new $class();

    $class = 'sfValidatorFormSympal'.$type->name;

    if (!class_exists($class))
    {
      $class = 'sfValidatorPass';
    }

    $validator = new $class;

    $widget->setAttribute('id', 'content_slot_value_' . $contentSlot['id']);
    $widget->setAttribute('onKeyUp', "edit_on_key_up('".$contentSlot['id']."');");

    $widgetSchema['value'] = $widget;
    $validatorSchema['value'] = $validator;
  }

  public static function changeThemeWidget($form)
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