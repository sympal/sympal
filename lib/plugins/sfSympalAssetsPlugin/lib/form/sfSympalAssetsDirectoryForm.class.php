<?php

class sfSympalAssetsDirectoryForm extends sfForm
{
  protected $_parentDir;

  public function configure()
  {
    $this->setWidgets(array(
      'name'      => new sfWidgetFormInput(),
      'directory' => new sfWidgetFormInputHidden(array('default' => $this->_parentDir)),
    ));

    $this->widgetSchema->setNameFormat('directory[%s]');

    $this->setValidators(array(
      'name'      => new sfValidatorString(array('trim' => true)),
      'directory' => new sfValidatorString(array('required' => false)),
    ));
    
    $this->getValidatorSchema()->setPostValidator(
      new sfValidatorCallback(array('callback' => array($this, 'postValidator')))
    );
  }

  public function postValidator($validator, $values)
  {
    $values['name'] = Doctrine_Inflector::urlize($values['name']);
    return $values;
  }

  public function setParentDirectory($parentDir)
  {
    $this->_parentDir = $parentDir;
  }
}