<?php

/**
 * PluginMenuItem form.
 *
 * @package    form
 * @subpackage MenuItem
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginMenuItemForm extends BaseMenuItemForm
{
  protected $parentId = null;
  
  public function setup()
  {
    parent::setup();

    $this->widgetSchema['parent_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'MenuItem',
      'add_empty' => '~ (object is at root level)',
      'order_by' => array('root_id, lft', ''),
      'method' => 'getIndentedName'
      ));
    $this->validatorSchema['parent_id'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'model' => 'MenuItem'
      ));
    $this->setDefault('parent_id', $this->object->getParentId());
    $this->widgetSchema->setLabel('parent_id', 'Child of');

    unset($this['Content'], $this['root_id'], $this['lft'], $this['rgt'], $this['level']);

    sfSympalTools::embedI18n('menus', $this);
  }
  
  public function updateParentIdColumn($parentId)
  {    
    $this->parentId = $parentId;
    // further action is handled in the save() method
  }  

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $node = $this->object->getNode();

    if ($this->parentId != $this->object->getParentId() || !$node->isValidNode())
    {
      if (empty($this->parentId))
      {
        //save as a root
        if ($node->isValidNode())
        {
          $node->makeRoot($this->object['id']);
          $this->object->save($con);
        }
        else
        {
          $this->object->getTable()->getTree()->createRoot($this->object); //calls $this->object->save internally
        }
      }
      else
      {
        //form validation ensures an existing ID for $this->parentId
        $parent = $this->object->getTable()->find($this->parentId);
        $method = ($node->isValidNode() ? 'move' : 'insert') . 'AsFirstChildOf';
        $node->$method($parent); //calls $this->object->save internally
      }
    }
  }
}