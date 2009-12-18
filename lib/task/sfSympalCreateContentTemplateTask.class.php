<?php

class sfSympalCreateContentTemplateTask extends sfTaskExtraBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The site/application title.'),
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The content template name.'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'create-content-template';
    $this->briefDescription = 'Create a Sympal content template.';

    $this->detailedDescription = <<<EOF
The [sympal:create-content-template|INFO] task will create a new Sympal content
template:

  [./sympal:create-content-template sympal "View UserProfile" UserProfile --partial-path="sympal_user_profile/view"|INFO]

The above command will create a ContentTemplate in the database named "View UserProfile"
for the ContentType named UserProfile. It will use the partial in the sympal_user_profile
module named _view.php.
EOF;
  }

  public function askForm(sfForm $form)
  {
    $object = $form->getObject();

    $this->logSection('sympal', "Prompting for ".get_class($object)." information:\n");

    $widgetSchema = $form->getWidgetSchema();
    $validatorSchema = $form->getValidatorSchema();

    $validatorSchema->setOption('allow_extra_fields', true);
    $validatorSchema->setOption('filter_extra_fields', false);

    $values = $object->toArray();
    foreach ($form as $key => $value)
    {
      if ($value->isHidden() || $object->$key)
      {
        continue;
      }
      $validator = $validatorSchema[$key];
      $label = $widgetSchema->getLabel($key);
      $label = $label ? $label : sfInflector::humanize($key);
      $values[$key] = $this->askAndValidate($label, $validator);
    }
    $form->bind($values);
    if ($form->isValid())
    {
      
      $this->logBlock("\nConfirm before saving:\n", 'INFO');
      $array = $form->getValues();
      $data = array();
      foreach ($array as $key => $value)
      {
        if ($value)
        {
          $data[$key] = $value;
        }
      }
      $e = explode("\n", sfYaml::dump($data));
      foreach ($e as $value)
      {
        $this->logBlock("  ".$value, 'COMMENT');
      }
      $this->askConfirmation(array("Finished! Do you want to save the above information to the database?", 'Are you sure you want to proceed? (y/N)'), 'QUESTION_LARGE', false);
      $form->save();

      $this->logSection('sympal', 'Saved successfully!');
    } else {
      $this->logBlock('The form had some errors:', 'ERROR');
      $this->logBlock((string) $form, 'ERROR');
    }
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfForm::disableCSRFProtection();

    $databaseManager = new sfDatabaseManager($this->configuration);

    $site = Doctrine_Core::getTable('Site')->findOneBySlug($arguments['application']);
    if (!$site)
    {
      throw new InvalidArgumentException(sprintf('Could not find site "%s"', $arguments['application']));
    }

    $name = $arguments['name'];
    $contentTemplate = Doctrine_Core::getTable('ContentTemplate')
      ->createQuery('t')
      ->andWhere('t.name = ?', $name)
      ->fetchOne();

    if ($contentTemplate)
    {
      throw new InvalidArgumentException(sprintf('Content template named "%s" already exists', $name));
    }

    $contentTemplate = new ContentTemplate();
    $contentTemplate->name = $name;
    $form = new ContentTemplateForm($contentTemplate);
    $this->askForm($form);
  }
}