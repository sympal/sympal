[?php

/**
 * <?php echo $this->getModuleName() ?> module configuration.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage <?php echo $this->getModuleName()."\n" ?>
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: helper.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class Base<?php echo ucfirst($this->getModuleName()) ?>GeneratorHelper extends sfModelGeneratorHelper
{
  public function isNestedSet()
  {
    return Doctrine_Core::getTable('<?php echo $this->getModelClass() ?>')->hasTemplate('Doctrine_Template_NestedSet');
  }

  public function getNestedSetJsonResults($rootId, $pager, $columns)
  {
    $results = $pager->getResults()->toHierarchy();
    $data = array(
      'requestFirstIndex' => 0,
      'firstIndex' => 0,
      'count' => $results->count(),
      'totalCount' => $pager->getNbResults(),
      'columns' => array_values($columns),
      'items' => $this->_buildNestedSetItems($rootId, $results, $columns)
    );
    return json_encode($data);
  }

  private function _buildNestedSetItems($rootId, $results, $columns)
  {
    $items = array();
    foreach ($results as $result)
    {
      if ($result->getRootId() == $rootId || $result->getId() == $rootId)
      {
        $item = array();
        $item['id'] = $result->getPrimaryKey();
        $item['info'] = array();
        foreach ($columns as $column => $label)
        {
          $item['info'][] = $result[$column];
        }
        if (isset($result['__children']))
        {
          $item['children'] = $this->_buildNestedSetItems($rootId, $result['__children'], $columns);
        }
        $items[] = $item;
      }
    }
    return $items;
  }

  public function getUrlForAction($action)
  {
    return 'list' == $action ? '<?php echo $this->params['route_prefix'] ?>' : '<?php echo $this->params['route_prefix'] ?>_'.$action;
  }
}
