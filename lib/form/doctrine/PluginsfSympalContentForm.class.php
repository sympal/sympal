<?php

/**
 * PluginContent form.
 *
 * @package    form
 * @subpackage sfSympalContent
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalContentForm extends BasesfSympalContentForm
{
  public function setup()
  {
    parent::setup();

    $this->validatorSchema->setOption('allow_extra_fields', true);

    unset(
      $this['site_id'],
      $this['created_at'],
      $this['updated_at'],
      $this['last_updated_by_id']
    );

    sfSympalFormToolkit::embedRichDateWidget('date_published', $this);

    $this->widgetSchema['created_by_id']->setOption('add_empty', true);

    $q = Doctrine_Query::create()
      ->from('sfSympalMenuItem m')
      ->orderBy('m.root_id, m.lft ASC');

    if (sfSympalConfig::isI18nEnabled('sfSympalMenuItem'))
    {
      $q->leftJoin('m.Translation mt'); 
    }

    $this->widgetSchema['master_menu_item_id']->setOption('query', $q);
    $this->widgetSchema['master_menu_item_id'] = new sfWidgetFormDoctrineChoice(array('model' => 'sfSympalMenuItem', 'query' => $q, 'add_empty' => true));
    $this->widgetSchema['content_type_id'] = new sfWidgetFormInputHidden();

    sfSympalFormToolkit::changeThemeWidget($this);

    if (!$this->object->content_type_id)
    {
      $this->object->Type = Doctrine_Core::getTable('sfSympalContentType')->findOneBySlug('page');
    } else {
      $this->object->Type;
    }

    $this->_embedTypeForm();
  }

  protected function _embedTypeForm()
  {
    $typeModelClass = $this->object->Type->name ? $this->object->Type->name:'sfSympalPage';
    $typeFormClass = $typeModelClass . 'Form';

    $record = $this->object->getRecord();
    $record->mapValue('contentForm', $this);
    $typeForm = new $typeFormClass($record);

    unset($typeForm['id'], $typeForm['content_id']);

    if (count($typeForm))
    {
      $this->embedForm('TypeForm', $typeForm);
      $this->widgetSchema['TypeForm']->setLabel($this->object->Type->label);
    }
  }
}