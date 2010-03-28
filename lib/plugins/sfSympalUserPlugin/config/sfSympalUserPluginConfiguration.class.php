<?php

/**
 * sfSympalUserPlugin configuration.
 * 
 * @package     sfSympalUserPlugin
 * @subpackage  config
 * @author      Your name here
 * @version     SVN: $Id: PluginConfiguration.class.php 12628 2008-11-04 14:43:36Z Kris.Wallsmith $
 */
class sfSympalUserPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
    $this->dispatcher->connect('context.load_factories', array($this, 'bootstrap'));
  }

  public function loadAdminMenu(sfEvent $event)
  {
    $menu = $event->getSubject();

    $security = $menu->getChild('Security')
      ->setCredentials(array(array('ManageUsers', 'ManageGroups', 'ManagePermissions')));

    $security->addChild('Users', '@sympal_users')
      ->setCredentials(array('ManageUsers'));

    $security->addChild('Groups', '@sympal_groups')
      ->setCredentials(array('ManageGroups'));

    $security->addChild('Permissions', '@sympal_permissions')
      ->setCredentials(array('ManagePermissions'));
  }

  /**
   * Listens on context.load_factories event
   */
  public function bootstrap(sfEvent $event)
  {
    $this->_initiateUserTable();
  }

  /**
   * Initiates the user model and throws the sympal.user.set_table_definition event.
   * 
   * Ths idea is that the user model hasn't been loaded yet, so it'll be
   * loaded here for the first time, and this allows a hook into its
   * table definition.
   */
  protected function _initiateUserTable()
  {
    $record = Doctrine_Core::getTable(sfSympalConfig::get('user_model'))->getRecordInstance();
    $this->dispatcher->notify(new sfEvent($record, 'sympal.user.set_table_definition', array('object' => $record)));
  }
}
