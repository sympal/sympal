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

    sfSympalFormToolkit::embedRichDateWidget('date_published', $this);

    if ($this->object->ContentType->name)
    {
      $q = Doctrine::getTable('sfSympalContent')
        ->getTypeQuery($this->object->ContentType->name);
    } else {
      $q = Doctrine::getTable('sfSympalContent')
        ->createQuery('c')
        ->leftJoin('c.Type t');
    }

    $this->widgetSchema['content_id']->setOption('query', $q);

    $q = Doctrine_Query::create()
      ->from('sfSympalMenuItem m');

    if ($this->object->exists())
    {
      $q->andWhere('m.id != ?', $this->object->id);
    }

    if (sfSympalConfig::isI18nEnabled('MenuItem'))
    {
      $q->leftJoin('m.Translation mt');
    }

    $this->widgetSchema['parent_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'MenuItem',
      'add_empty' => '~ (object is at root level)',
      'order_by' => array('root_id, lft', ''),
      'query' => $q,
      'method' => 'getIndentedName'
      ));
    $this->validatorSchema['parent_id'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'model' => 'MenuItem'
      ));
    $this->setDefault('parent_id', $this->object->getParentId());
    $this->widgetSchema->setLabel('parent_id', 'Child of');

    if ($this->object->exists())
    {
      $this->widgetSchema['move'] = new sfWidgetFormDoctrineChoice(array(
        'model' => 'MenuItem',
        'add_empty' => true,
        'order_by' => array('root_id, lft', ''),
        'query' => $q,
        'method' => 'getIndentedName'
        ));
      $this->validatorSchema['move'] = new sfValidatorDoctrineChoice(array(
        'required' => false,
        'model' => 'MenuItem'
        ));
      $this->widgetSchema->setLabel('move', 'Move to?');

      $choices = array(
        'Prev' => 'Before',
        'Next' => 'After'
      );
      $this->widgetSchema['where_to_move'] = new sfWidgetFormChoice(array('choices' => $choices));
      $this->validatorSchema['where_to_move'] = new sfValidatorChoice(array(
        'required' => false,
        'choices' => array_keys($choices)
      ));
      $this->widgetSchema->setLabel('where_to_move', 'Before or after?');
    }

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