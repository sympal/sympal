<?php

class sfWidgetFormSympalVersion extends sfWidgetFormDoctrineChoice
{
  protected $_object;

  public function __construct($options = array(), $attributes = array())
  {
    if (!isset($options['object']))
    {
      throw new sfException('You must pass an object option with an instance of the object to retrieve the versions for.');
    }

    $this->_object = $options['object'];
    unset($options['object']);

    $options['add_empty'] = true;
    $options['model'] = 'Version';

    $q = Doctrine::getTable('Version')
      ->createQuery('v')
      ->leftJoin('v.Changes c')
      ->leftJoin('v.CreatedBy u')
      ->andWhere('v.record_type = ?', get_class($this->_object))
      ->andWhere('v.record_id = ?', $this->_object['id']);

    $options['query'] = $q;

    parent::__construct($options, $attributes);
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $id = str_replace(array('[', ']'), array('_', ''), $name);
    $url = sfContext::getInstance()->getController()->genUrl('@sympal_revert_data?id=VERSION_ID');
    $js = "javascript: var url = '".$url."'; url = url.replace('VERSION_ID', document.getElementById('".$id."').value); location.href = url;";

    return parent::render($name, $value, $attributes, $errors).' <a href="#" onClick="'.$js.'">'.image_tag('/sfSympalPlugin/images/revert.png').'</a> Current version is #'.$this->_object->version;
  }
}