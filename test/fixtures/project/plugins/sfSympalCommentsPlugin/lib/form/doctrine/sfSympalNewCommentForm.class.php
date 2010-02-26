<?php

class sfSympalNewCommentForm extends sfSympalCommentForm
{
  public function setup()
  {
    parent::setup();
    
    $this->useFields(array(
      'name',
      'email_address',
      'website',
      'body',
      'user_id',
    ));

    $this->widgetSchema['body']->setAttribute('style', 'width: 400px; height: 200px;');
    $this->widgetSchema->setHelp('body', 'Markdown syntax is enabled.');
    
    $this->validatorSchema['website'] = new sfValidatorUrl(array('required' => false));
    $this->validatorSchema['website']->setMessage('invalid', 'Please enter a valid url (e.g. http://www.sympalphp.org)');
    
    $this->validatorSchema['email_address']->setOption('required', true);
    $this->validatorSchema['body']->setOption('required', true);
    
    // if auth is required, the User will be set, the name and email aren't needed
    if (sfContext::getInstance()->getUser()->isAuthenticated())
    {
      unset($this['name'], $this['email_address']);
      $this->widgetSchema['user_id'] = new sfWidgetFormInputHidden();
    }
    else
    {
      unset($this['user_id']);
    }

    $this->widgetSchema['content_id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['content_id'] = new sfValidatorDoctrineChoice(array('model' => 'sfSympalContent'));
  }
}