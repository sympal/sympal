<?php

class sfSympalInstallForm extends BaseForm
{
  public function setup()
  {
    $this->embedForm('setup', new sfSympalSetupInstallForm());
    $this->embedForm('user', new sfSympalUserInstallForm());
    $this->embedForm('database', new sfSympalDatabaseInstallForm());

    $this->widgetSchema->setNameFormat('install[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }  
}

class sfSympalSetupInstallForm extends BaseForm
{
  public function formatPluginName($name)
  {
    return sfInflector::humanize(sfInflector::underscore(sfSympalPluginToolkit::getShortPluginName($name)));
  }

  public function getWidgetPluginChoices()
  {
    $plugins = sfSympalContext::getInstance()->getSympalConfiguration()->getAddonPlugins();
    $plugins = array_combine($plugins, $plugins);
    $plugins = array_map(array($this, 'formatPluginName'), $plugins);

    return $plugins;
  }

  public function setup()
  {
    $plugins = $this->getWidgetPluginChoices();

    $this->setWidgets(array(
      'plugins'           => new sfSympalInstallPluginsChoiceWidget(array('multiple' => true, 'expanded' => true, 'choices' => $plugins)),
    ));

    $this->setValidators(array(
      'plugins'           => new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys($plugins))),
    ));

    $this->widgetSchema->setNameFormat('install[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->mergePostValidator(new sfValidatorSchemaCompare('password', sfValidatorSchemaCompare::EQUAL, 'password_again', array(), array('invalid' => 'The two passwords must be the same.')));

    parent::setup();
  }
}

class sfSympalInstallPluginsChoiceWidget extends sfWidgetFormChoice
{
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if ($this->getOption('multiple'))
    {
      $attributes['multiple'] = 'multiple';
  
      if ('[]' != substr($name, -2))
      {
        $name .= '[]';
      }
    }

    $choices = $this->getOption('choices');
    if ($choices instanceof sfCallable)
    {
      $choices = $choices->call();
    }

    $html = '<table>';
    $html .= '<thead><tr><th></th><th>Plugin</th><th>Author</th></tr>';
    $html .= '<tbody>';
    foreach ($choices as $pluginName => $title)
    {
      $info = new sfSympalPluginInfo($pluginName);
      $html .= '<tr><td><input type="checkbox" name="'.$name.'" value="'.$pluginName.'" '.(in_array($pluginName, (array) $value) ? 'checked="checked"' : null).' /></td><td><strong>'.link_to($info->getTitle(), $info->getUrl(), 'target=_BLANK').'</strong><br/><small>'.$info->getDescription().'</small></td><td>'.$info->getAuthor().'</td></tr?>';
    }
    $html .= '</tbody>';
    $html .= '</table>';
    return $html;
  }
}

class sfSympalUserInstallForm extends sfForm
{
  public function setup()
  {
    $this->setWidgets(array(
      'first_name'          => new sfWidgetFormInput(),
      'last_name'           => new sfWidgetFormInput(),
      'email_address'       => new sfWidgetFormInput(),
      'username'            => new sfWidgetFormInput(),
      'password'            => new sfWidgetFormInputPassword(),
    ));

    $this->setValidators(array(
      'first_name'          => new sfValidatorString(array('max_length' => 255)),
      'last_name'           => new sfValidatorString(array('max_length' => 255)),
      'email_address'       => new sfValidatorString(array('max_length' => 255)),
      'username'            => new sfValidatorString(array('max_length' => 255)),
      'password'            => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema['password'] = new sfWidgetFormInputPassword();
    $this->widgetSchema['password_again'] = new sfWidgetFormInputPassword();
    $this->validatorSchema['password_again'] = clone $this->validatorSchema['password'];

    $this->widgetSchema->moveField('password_again', 'after', 'password');

    $this->widgetSchema->setNameFormat('install[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->mergePostValidator(new sfValidatorSchemaCompare('password', sfValidatorSchemaCompare::EQUAL, 'password_again', array(), array('invalid' => 'The two passwords must be the same.')));

    parent::setup();
  }
}

class sfSympalDatabaseInstallForm extends sfForm
{
  public function setup()
  {
    $typeChoices = array(
      '' => '',
      'mysql' => 'MySQL',
      'sqlite' => 'Sqlite',
      'pgsql' => 'PostgreSQL',
      'oracle' => 'Oracle Adapter',
      'oci' => 'Oracle PDO',
      'mssql' => 'MsSQL'
    );

    $this->setWidgets(array(
      'type'       => new sfWidgetFormChoice(array('choices' => $typeChoices)),
      'host'       => new sfWidgetFormInput(),
      'name'       => new sfWidgetFormInput(),
      'username'   => new sfWidgetFormInput(),
      'password'   => new sfWidgetFormInputPassword(),
    ));

    $this->setValidators(array(
      'type'        => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($typeChoices))),
      'host'        => new sfValidatorString(array('required' => false)),
      'name'        => new sfValidatorString(array('required' => false)),
      'username'    => new sfValidatorString(array('required' => false)),
      'password'    => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setLabel('name', 'Database Name');
    $this->widgetSchema['password'] = new sfWidgetFormInputPassword();
    $this->widgetSchema['password_again'] = new sfWidgetFormInputPassword();
    $this->validatorSchema['password_again'] = clone $this->validatorSchema['password'];

    $this->widgetSchema->setNameFormat('database[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->mergePostValidator(new sfValidatorSchemaCompare('password', sfValidatorSchemaCompare::EQUAL, 'password_again', array(), array('invalid' => 'The two passwords must be the same.')));

    parent::setup();
  }
}