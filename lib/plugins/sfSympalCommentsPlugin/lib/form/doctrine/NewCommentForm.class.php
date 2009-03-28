<?php
class NewCommentForm extends CommentForm
{
  public function setup()
  {
    parent::setup();

    unset($this['id'], $this['content_list'], $this['status'], $this['created_at'], $this['updated_at'], $this['blog_posts_list'], $this['users_list'], $this['pages_list']);

    $this->widgetSchema['subject']->setAttribute('style', 'width: 300px');
    $this->widgetSchema['body']->setAttribute('style', 'width: 400px; height: 200px;');
    $this->widgetSchema->setHelp('body', 'Markdown syntax is enabled.');

    if (sfSympalConfig::get('Comments', 'requires_auth'))
    {
      unset($this['name'], $this['email_address']);
      $this->widgetSchema['user_id'] = new sfWidgetFormInputHidden();
    } else {
      unset($this['user_id']);
    }

    $this->widgetSchema['content_id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['content_id'] = new sfValidatorDoctrineChoice(array('model' => 'Content'));

    if (sfSympalConfig::get('sfSympalCommentsPlugin', 'enable_recaptcha'))
    {
      sfSympalTools::embedRecaptcha($this);
    }
  }
}