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

    if (count($typeForm))
    {
      $this->embedForm($this->object->Type->name, $typeForm);
    }

    if ($this instanceof InlineContentPropertyForm)
    {
      unset($this['value']);
      $this->_unsetWidgetsExceptContentSlot($this);
    }

    $this->_embedContentSlots();
  }

  protected function _embedContentSlots()
  {
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
  protected function _unsetWidgetsExceptContentSlot($form, $widgetSchema = null)
  {
    if (is_null($widgetSchema))
    {
      $widgetSchema = $form->getWidgetSchema();
    }

    foreach ($form as $key => $value)
    {
      if (!$value instanceof sfFormFieldSchema && $this->contentSlot->name != $key)
      {
        unset($form[$key]);
        unset($widgetSchema[$key]);
      } elseif (!$value instanceof sfFormFieldSchema && $this->contentSlot->name == $key) {
        $widgetSchema[$key]->setAttribute('id', 'content_slot_value_' . $this->contentSlot->id);
        $widgetSchema[$key]->setAttribute('onKeyUp', "edit_on_key_up('".$this->contentSlot->id."');");
      }
    }

    $embeddedForms = $form->getEmbeddedForms();
    foreach ($embeddedForms as $key => $embeddedForm)
    {
      $this->_unsetWidgetsExceptContentSlot($embeddedForm, $widgetSchema[$key]);
    }
  }
}