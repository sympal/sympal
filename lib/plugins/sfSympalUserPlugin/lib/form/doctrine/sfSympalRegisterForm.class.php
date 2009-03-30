<?php

class sfSympalRegisterForm extends BasesfSympalRegisterForm
{
  protected static $_instance;
  
  public function configure()
  {
    parent::configure();

    unset(
      $this['is_active'],
      $this['is_super_admin'],
      $this['updated_at'],
      $this['groups_list'],
      $this['permissions_list']
    );

    $this->validatorSchema['password']->setOption('required', true);

    if (sfSympalConfig::get('sfSympalRegisterPlugin', 'enable_recaptcha'))
    {
      sfSympalFormToolkit::embedRecaptcha($this);
    }
  }

  public static function getInstance($record = null)
  {
    if (!self::$_instance)
    {
      self::$_instance = new self($record);
    }
    return self::$_instance;
  }
}