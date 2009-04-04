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

    sfSympalFormToolkit::embedRichDateWidget('date_published', $this);

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
      unset($this['value']);
      foreach ($this as $key => $value)
      {
        if (!$value instanceof sfFormFieldSchema && $this->contentSlot->name != $key)
        {
          unset($this[$key]);
        } elseif (!$value instanceof sfFormFieldSchema && $this->contentSlot->name == $key) {
          $this->widgetSchema[$key]->setAttribute('id', 'content_slot_value_' . $this->contentSlot['id']);
          $this->widgetSchema[$key]->setAttribute('onKeyUp', "edit_on_key_up('".$this->contentSlot['id']."');");
        }
      }
      $typeFormWidgetSchema = $typeForm->getWidgetSchema();
      foreach ($typeForm as $key => $value)
      {
        if (!$value instanceof sfFormFieldSchema && $this->contentSlot->name != $key)
        {
          unset($typeForm[$key]);
        } elseif (!$value instanceof sfFormFieldSchema && $this->contentSlot->name == $key) {
          $typeFormWidgetSchema[$key]->setAttribute('id', 'content_slot_value_' . $this->contentSlot['id']);
          $typeFormWidgetSchema[$key]->setAttribute('onKeyUp', "edit_on_key_up('".$this->contentSlot['id']."');");
        }
      }
    }

    sfSympalFormToolkit::embedI18n($this->object->Type->name, $typeForm);
    
    if (count($typeForm))
    {
      $this->embedForm($this->object->Type->name, $typeForm);
    }
  }

  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    $ret = parent::bind($taintedValues, $taintedFiles);
    foreach ($this->embeddedForms as $name => $form)
    {
      $this->embeddedForms[$name]->isBound = true;
      if (isset($this->values[$name]))
      {
        $this->embeddedForms[$name]->values = $this->values[$name];
      }
    }

    return $ret;
  }
}