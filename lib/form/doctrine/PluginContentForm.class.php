<?php

/**
 * PluginContent form.
 *
 * @package    form
 * @subpackage Content
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginContentForm extends BaseContentForm
{
  public function setup()
  {
    parent::setup();

    unset(
      $this['created_at'],
      $this['updated_at'],
      $this['last_updated_by']
    );

    $this->widgetSchema['site_id'] = new sfWidgetFormInputHidden();

    sfSympalFormToolkit::changeLayoutWidget($this);

    $this->updateDefaultsFromObject();

    if (!$this->object->content_type_id)
    {
      $this->object->Type = Doctrine::getTable('ContentType')->findOneBySlug('page');
    } else {
      $this->object->Type;
    }

    $type = ($this->object->Type->name ? $this->object->Type->name:'Page') . 'Form';

    $typeForm = new $type($this->object->getRecord());

    unset($typeForm['id'], $typeForm['content_id']);

    $q = Doctrine_Query::create()
      ->from('MenuItem m')
      ->orderBy('m.lft ASC');

    $this->widgetSchema['content_type_id'] = new sfWidgetFormInputHidden();
    $this->widgetSchema['locked_by'] = new sfWidgetFormInputHidden();

    $this->widgetSchema['master_menu_item_id']->setLabel('Parent Menu Item');
    $this->widgetSchema['groups_list']->setLabel('Groups');
    $this->widgetSchema['permissions_list']->setLabel('Permissions');
    $this->widgetSchema['content_template_id']->setLabel('Template');

    if ($this instanceof InlineContentPropertyForm)
    {
      foreach ($this as $key => $value)
      {
        if (!$value instanceof sfFormFieldSchema && $this->contentSlot->name != $key)
        {
          unset($this[$key]);
        }
      }
      foreach ($typeForm as $key => $value)
      {
        if (!$value instanceof sfFormFieldSchema && $this->contentSlot->name != $key)
        {
          unset($typeForm[$key]);
        }
      }
    }

    sfSympalFormToolkit::embedI18n($this->object->Type->name, $typeForm);
    
    if (count($typeForm))
    {
      $this->embedForm($this->object->Type->name, $typeForm);
    }
  }
}