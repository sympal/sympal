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
    $response = sfContext::getInstance()->getResponse();
    $response->addStylesheet('http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/themes/ui-lightness/jquery-ui.css');
    $response->addStylesheet('http://static.jquery.com/ui/css/demo-docs-theme/ui.theme.css');

    $response->addJavascript('http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js');
    $response->addJavascript('http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/jquery-ui.min.js');
    $response->addJavascript('http://jquery-ui.googlecode.com/svn/tags/latest/external/bgiframe/jquery.bgiframe.min.js');
    $response->addJavascript('http://jquery-ui.googlecode.com/svn/tags/latest/ui/minified/i18n/jquery-ui-i18n.min.js');

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

  public static function changeLayoutWidget($form)
  {
    $layouts = sfContext::getInstance()->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration()->getLayouts();
    array_unshift($layouts, '');
    $form->setWidget('layout', new sfWidgetFormChoice(array(
      'choices'   => $layouts
    )));

    $form->setValidator('layout', new sfValidatorChoice(array(
      'choices'   => array_keys($layouts)
    )));
  }
}