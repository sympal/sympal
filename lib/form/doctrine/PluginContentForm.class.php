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
    $this->widgetSchema['created_by']->setOption('add_empty', true);

    sfSympalFormToolkit::changeLayoutWidget($this);

    $this->updateDefaultsFromObject();

    if (!$this->object->content_type_id)
    {
      $this->object->Type = Doctrine::getTable('ContentType')->findOneBySlug('page');
    } else {
      $this->object->Type;
    }

    $typeModelClass = $this->object->Type->name ? $this->object->Type->name:'Page';
    $typeFormClass = $typeModelClass . 'Form';

    $typeForm = new $typeFormClass($this->object->getRecord());

    unset($typeForm['id'], $typeForm['content_id']);

    $q = Doctrine_Query::create()
      ->from('MenuItem m')
      ->orderBy('m.lft ASC');

    $this->widgetSchema['content_type_id'] = new sfWidgetFormInputHidden();
    $this->widgetSchema['locked_by'] = new sfWidgetFormInputHidden();

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

    if (count($typeForm))
    {
      $this->embedForm($this->object->Type->name, $typeForm);
    }

    if (count($this->object->Slots) && !$this instanceof InlineContentPropertyForm)
    {
      $slotsForm = new sfForm();
      foreach ($this->object->Slots as $key => $slot)
      {
        if ($slot->is_column)
        {
          continue;
        }
        $slotForm = new ContentSlotForm($slot);
        unset($slotForm['id'], $slotForm['slot_type_id']);
        $slotsForm->embedForm($key, $slotForm);
        $slotsForm->widgetSchema[$key]->setLabel(sfInflector::humanize($slot['name']));
      }
      $this->embedForm('Content Slots', $slotsForm);
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