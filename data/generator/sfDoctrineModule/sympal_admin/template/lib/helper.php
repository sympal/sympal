[?php

/**
 * <?php echo $this->getModuleName() ?> module configuration.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage <?php echo $this->getModuleName()."\n" ?>
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: helper.php 12482 2008-10-31 11:13:22Z jwage $
 */
class Base<?php echo ucfirst($this->getModuleName()) ?>GeneratorHelper extends sfModelGeneratorHelper
{
  public function getTabExtras($tab, $position, $variables = array())
  {
    $tab = str_replace(' ', '_', sfInflector::tableize($tab));
    $extras = '';

    $tabExtras = array();
    $tabExtras = sfApplicationConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($this, '<?php echo $this->getModuleName() ?>.'.$tab.'_tab_'.$position.'_extras'), $tabExtras)->getReturnValue();
    foreach ($tabExtras as $tabExtra)
    {
      $e = explode('/', $tabExtra);
      $resource = $this->getSymfonyResource($e[0], isset($e[1]) ? $e[1]:null, $variables);
      $extras .= $resource ? $resource:$tabExtra;
    }

    return $extras;
  }

  public function getSymfonyResource($module, $action, $variables = array())
  {
    $action = str_replace(' ', '_', sfInflector::tableize($action));
    try {
      return sfSympalToolkit::getSymfonyResource($module, $action, $variables);
    } catch (Exception $e) {
      return false;
    }
  }

  public function linkToNew($params)
  {
    return '<li class="sf_admin_action_new">'.link_to(__($params['label'], array(), 'sf_admin'), $this->getUrlForAction('new')).'</li>';
  }

  public function linkToEdit($object, $params)
  {
    return '<li class="sf_admin_action_edit">'.link_to(__($params['label'], array(), 'sf_admin'), $this->getUrlForAction('edit'), $object).'</li>';
  }

  public function linkToDelete($object, $params)
  {
    if ($object->isNew())
    {
      return '';
    }

    return '<li class="sf_admin_action_delete">'.link_to(__($params['label'], array(), 'sf_admin'), $this->getUrlForAction('delete'), $object, array('method' => 'delete', 'confirm' => !empty($params['confirm']) ? __($params['confirm'], array(), 'sf_admin') : $params['confirm'])).'</li>';
  }

  public function linkToList($params)
  {
    return '<li class="sf_admin_action_list">'.link_to(__($params['label'], array(), 'sf_admin'), $this->getUrlForAction('list')).'</li>';
  }

  public function linkToSave($object, $params)
  {
    return '<li class="sf_admin_action_save"><input type="submit" value="'.__($params['label'], array(), 'sf_admin').'" /></li>';
  }

  public function linkToSaveAndAdd($object, $params)
  {
    return '<li class="sf_admin_action_save_and_add"><input type="submit" value="'.__($params['label'], array(), 'sf_admin').'" name="_save_and_add" /></li>';
  }

  public function linkToSaveAndList($object, $params)
  {
    return '<li class="sf_admin_action_save_and_list"><input type="submit" value="'.__($params['label'], array(), 'sf_admin').'" name="_save_and_list" /></li>';
  }

  public function getUrlForAction($action)
  {
    return 'list' == $action ? '<?php echo $this->params['route_prefix'] ?>' : '<?php echo $this->params['route_prefix'] ?>_'.$action;
  }
}
