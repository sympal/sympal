<?php
class NewCommentForm extends CommentForm
{
  public function setup()
  {
    parent::setup();

    unset($this['id'], $this['status'], $this['created_at'], $this['updated_at'], $this['blog_posts_list'], $this['users_list'], $this['pages_list']);

    $this->widgetSchema['subject']->setAttribute('style', 'width: 300px');
    $this->widgetSchema['body']->setAttribute('style', 'width: 600px; height: 200px;');
    $this->widgetSchema->setHelp('body', 'Markdown syntax is enabled.');

    if (sfSympalConfig::get('Comments', 'requires_auth'))
    {
      unset($this['name'], $this['email_address']);
      $this->widgetSchema['user_id'] = new sfWidgetFormInputHidden();
    } else {
      unset($this['user_id']);
    }

    $this->widgetSchema['entity_id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['entity_id'] = new sfValidatorDoctrineChoice(array('model' => 'Entity'));

    if (sfSympalConfig::get('Comments', 'enable_recaptcha'))
    {
      $settings = sfConfig::get('app_sympal_settings_Comments');
      $publicKey = sfSympalConfig::get('Comments', 'recaptcha_public_key');
      $privateKey = sfSympalConfig::get('Comments', 'recaptcha_private_key');
  
      if (!$publicKey || !$privateKey) {
        throw new sfException('You must specify the recaptcha public and private key in your app.yml');
      }

      $this->widgetSchema['captcha'] = new sfWidgetFormReCaptcha(array(
        'public_key' => $publicKey
      ));

      $this->validatorSchema['captcha'] = new sfValidatorReCaptcha(array(
        'private_key' => $privateKey
      ));
    }
  }
}