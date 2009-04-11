<?php

class sfSympalI18nGenerator extends Doctrine_I18n
{
  protected $_invoker;

  public function setInvoker($invoker)
  {
    $this->_invoker = $invoker;
  }

  public function setTableDefinition()
  {
    parent::setTableDefinition();

    if (sfSympalConfig::isVersioningEnabled($this->_table->getOption('name')))
    {
      $this->actAs(new sfSympalVersionable());
    }
  }

  public function generateClass(array $definition = array())
  {
      $definition['className'] = $this->_options['className'];

      $builder = new Doctrine_Import_Builder();

      if ($this->_options['generateFiles']) {
          if (isset($this->_options['generatePath']) && $this->_options['generatePath']) {
              $builder->setTargetPath($this->_options['generatePath']);
              $builderOptions = isset($this->_options['builderOptions']) ? (array) $this->_options['builderOptions']:array();
              $builder->setOptions($builderOptions);
              $builder->buildRecord($definition);
          } else {
              throw new Doctrine_Record_Exception('If you wish to generate files then you must specify the path to generate the files in.');
          }
      } else {
          $def = $builder->buildDefinition($definition);
          $def = str_replace('extends Doctrine_Record', 'extends sfSympalDoctrineRecord', $def);

          eval($def);
      }
  }
}