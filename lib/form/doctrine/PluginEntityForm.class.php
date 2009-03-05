<?php

/**
 * PluginEntity form.
 *
 * @package    form
 * @subpackage Entity
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginEntityForm extends BaseEntityForm
{
  public function setup()
  {
    parent::setup();

    unset(
      $this['created_at'],
      $this['updated_at'],
      $this['entity_type_id'],
      $this['locked_by'],
      $this['created_by'],
      $this['last_updated_by']
    );

    sfSympalTools::changeLayoutWidget($this);

    if (!isset($this->object->Type))
    {
      $this->object->Type = Doctrine::getTable('EntityType')->findOneBySlug('page');
    }

    $type = ($this->object->Type->name ? $this->object->Type->name:'Page') . 'Form';

    $typeForm = new $type($this->object->getRecord());
    unset($typeForm['id'], $typeForm['entity_id']);
    sfSympalTools::embedI18n($this->object->Type->name, $typeForm);
    $this->embedForm('Entity', $typeForm);
    $this->widgetSchema['Entity']->setLabel($this->object->Type->name . ' Information');

    $q = Doctrine_Query::create()
      ->from('MenuItem m')
      ->orderBy('m.lft ASC');

    $this->widgetSchema['site_id']->setOption('add_empty', true);

    foreach ($this->object->Slots as $slot)
    {
      $entitySlotForm = new EntitySlotForm($slot);
      unset($entitySlotForm['id'], $entitySlotForm['entity_id'], $entitySlotForm['name']);
      $this->embedForm($slot->getName(), $entitySlotForm);
    }
  }
}