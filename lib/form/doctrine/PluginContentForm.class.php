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
      ->from('MenuItem m')
      ->orderBy('m.root_id, m.lft ASC');

    if (sfSympalConfig::isI18nEnabled('MenuItem'))
    {
      $q->leftJoin('m.Translation mt'); 
    }

    $this->widgetSchema['master_menu_item_id']->setOption('query', $q);
    $this->widgetSchema['master_menu_item_id'] = new sfWidgetFormDoctrineChoice(array('model' => 'MenuItem', 'query' => $q, 'add_empty' => true));
    $this->widgetSchema['content_type_id'] = new sfWidgetFormInputHidden();

    sfSympalFormToolkit::changeThemeWidget($this);

    if (!$this->object->content_type_id)
    {
      $this->object->Type = Doctrine_Core::getTable('ContentType')->findOneBySlug('page');
    } else {
      $this->object->Type;
    }

    $this->_embedTypeForm();
    $this->_embedContentSlotForms();
  }

  protected function _embedTypeForm()
  {
    $typeModelClass = $this->object->Type->name ? $this->object->Type->name:'Page';
    $typeFormClass = $typeModelClass . 'Form';

    $record = $this->object->getRecord();
    $record->mapValue('contentForm', $this);
    $typeForm = new $typeFormClass($record);

    unset($typeForm['id'], $typeForm['content_id']);

    if (count($typeForm))
    {
      $this->embedForm($this->object->Type->name, $typeForm);
      $this->widgetSchema[$this->object->Type->name]->setLabel($this->object->Type->label);
    }
  }

  protected function _embedContentSlotForms()
  {
    if (count($this->object->Slots))
    {
      $slotsForm = new BaseForm();
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
}