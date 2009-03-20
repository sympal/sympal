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
      $this['created_by'],
      $this['last_updated_by']
    );

    $this->widgetSchema['site_id'] = new sfWidgetFormInputHidden();

    sfSympalTools::changeLayoutWidget($this);

    $this->updateDefaultsFromObject();

    if (!$this->object->entity_type_id)
    {
      $this->object->Type = Doctrine::getTable('EntityType')->findOneBySlug('page');
    } else {
      $this->object->Type;
    }

    $type = ($this->object->Type->name ? $this->object->Type->name:'Page') . 'Form';

    $typeForm = new $type($this->object->getRecord());
    unset($typeForm['id'], $typeForm['entity_id']);
    sfSympalTools::embedI18n($this->object->Type->name, $typeForm);
    $this->embedForm($this->object->Type->name, $typeForm);

    $q = Doctrine_Query::create()
      ->from('MenuItem m')
      ->orderBy('m.lft ASC');

    $this->widgetSchema['entity_type_id'] = new sfWidgetFormInputHidden();
    $this->widgetSchema['locked_by'] = new sfWidgetFormInputHidden();

    $this->widgetSchema['master_menu_item_id']->setLabel('Parent Menu Item');
    $this->widgetSchema['groups_list']->setLabel('Groups');
    $this->widgetSchema['permissions_list']->setLabel('Permissions');
    $this->widgetSchema['entity_template_id']->setLabel('Template');
  }
}