<?php

/**
 * PluginsfSympalMenuItem form.
 *
 * @package    form
 * @subpackage sfSympalMenuItem
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginsfSympalMenuItemForm extends BasesfSympalMenuItemForm
{
  protected $parentId = null;
  protected $move = null;
  protected $whereToMove = null;

  public function setup()
  {
    parent::setup();

    $q = Doctrine_Query::create()
      ->from('sfSympalMenuItem m');

    if ($this->object->exists())
    {
      $q->andWhere('m.id != ?', $this->object->id);
    }

    if (sfSympalConfig::isI18nEnabled('sfSympalMenuItem'))
    {
      $q->leftJoin('m.Translation mt');
    }

    $q->andWhere('m.site_id = ?', sfSympalContext::getInstance()->getSite()->getId());

    $this->widgetSchema['parent_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'sfSympalMenuItem',
      'add_empty' => '~ (object is at root level)',
      'order_by' => array('root_id, lft', ''),
      'query' => $q,
      'method' => 'getIndentedName'
      ));
    $this->validatorSchema['parent_id'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'model' => 'sfSympalMenuItem'
      ));
    $this->setDefault('parent_id', $this->object->getParentId());
    $this->widgetSchema->setLabel('parent_id', 'Child of');

    $this->widgetSchema['move'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'sfSympalMenuItem',
      'add_empty' => true,
      'order_by' => array('root_id, lft', ''),
      'query' => $q,
      'method' => 'getIndentedName'
      ));
    $this->validatorSchema['move'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'model' => 'sfSympalMenuItem'
      ));
    $this->widgetSchema->setLabel('move', 'Position menu item');

    $choices = array(
      '' => '',
      'Prev' => 'Before',
      'Next' => 'After'
    );
    $this->widgetSchema['where_to_move'] = new sfWidgetFormChoice(array('choices' => $choices));
    $this->validatorSchema['where_to_move'] = new sfValidatorChoice(array(
      'required' => false,
      'choices' => array_keys($choices)
    ));
    $this->widgetSchema->setLabel('where_to_move', 'Position before or after?');

    unset($this['site_id'], $this['Content'], $this['root_id'], $this['lft'], $this['rgt'], $this['level'], $this['slug']);
  }
  
  public function updateParentIdColumn($parentId)
  {    
    $this->parentId = $parentId;
  }  

  public function updateMoveColumn($move)
  {    
    $this->move = $move;
  }  

  public function updateWhereToMoveColumn($whereToMove)
  {    
    $this->whereToMove = $whereToMove;
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
        $method = ($node->isValidNode() ? 'move' : 'insert') . 'AsLastChildOf';
        $node->$method($parent); //calls $this->object->save internally
      }
    }

    if ($this->move)
    {
      $type = $this->whereToMove ? $this->whereToMove : 'Next';
      $func = 'moveAs'.$type.'SiblingOf';
      $node->$func($this->object->getTable()->find($this->move));
    }
  }
}