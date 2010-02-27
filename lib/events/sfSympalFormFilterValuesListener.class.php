<?php

class sfSympalFormFilterValuesListener extends sfSympalListener
{
  public function getEventName()
  {
    return 'form.filter_values';
  }

  public function run(sfEvent $event, $values)
  {
    $form = $event->getSubject();
    if ($form->hasRecaptcha())
    {
      $request = $this->_invoker->getSymfonyContext()->getRequest();
      $captcha = array(
        'recaptcha_challenge_field' => $request->getParameter('recaptcha_challenge_field'),
        'recaptcha_response_field'  => $request->getParameter('recaptcha_response_field'),
      );
      $values = array_merge($values, array('captcha' => $captcha));
    }

    return $values;
  }
}