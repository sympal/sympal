<?php

/**
 * Base actions for the sfTestPlugin sympal_dashboard module.
 * 
 * @package     sfTestPlugin
 * @subpackage  sympal_dashboard
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_dashboardActions extends sfActions
{
  public function executeIndex()
  {
    if ($this->isAjax = $this->isAjax())
    {
      $this->setLayout(false);
    }

    if (sfSympalConfig::get('check_for_upgrades_on_dashboard', null, false))
    {
      $this->upgrade = new sfSympalUpgradeFromWeb(
        $this->getContext()->getConfiguration(),
        $this->getContext()->getEventDispatcher(),
        new sfFormatter()
      );

      $this->hasNewVersion = $this->upgrade->hasNewVersion();
    } else {
      $this->hasNewVersion = false;
    }

    $this->boxes = new sfSympalMenu('Dashboard Boxes');

    $this->boxes['Sites']
      ->setRoute('@sympal_sites')
      ->setCredentials(array('ManageSites'));

    $this->boxes['Manage Content']
      ->setRoute('@sympal_content')
      ->setCredentials(array('ManageContent'));

    $contentTypes = Doctrine_Core::getTable('sfSympalContentType')->getAllContentTypes();

    foreach ($contentTypes as $contentType)
    {
      $this->boxes['Create '.$contentType['label']]
        ->setRoute('@sympal_content_create_type?type='.$contentType['slug'])
        ->setCredentials(array('ManageContent'))
        ->setLiClass('create_content');
    }

    $this->boxes['Assets Manager']
      ->setRoute('@sympal_assets')
      ->setCredentials(array('ManageAssets'));

    $this->boxes['Menu Manager']
      ->setRoute('@sympal_menu_items')
      ->setCredentials(array('ManageMenus'));

    $this->boxes['Plugin Manager']
      ->setRoute('@sympal_plugin_manager')
      ->setCredentials(array('ManagePlugins'));

    $this->boxes['Content Types']
      ->setRoute('@sympal_content_types')
      ->setCredentials(array('ManageContentTypes'));

    $this->boxes['Users']
      ->setRoute('@sympal_users')
      ->setCredentials(array('ManageUsers'));

    $this->boxes['Groups']
      ->setRoute('@sympal_groups')
      ->setCredentials(array('ManageGroups'));

    $this->boxes['Permissions']
      ->setRoute('@sympal_permissions')
      ->setCredentials(array('ManagePermissions'));

    $this->boxes['Configuration']
      ->setRoute('@sympal_config')
      ->setCredentials(array('ManageSystemSettings'));

    $this->boxes['Sitemap']
      ->setRoute('@sympal_sitemap')
      ->setCredentials(array('ManageMenus'));

    $this->boxes['Check for Updates']
      ->setRoute('@sympal_check_for_updates')
      ->setCredentials(array('UpdateManager'));

    $this->boxes['Check Server']
      ->setRoute('@sympal_check_server')
      ->setCredentials(array('ViewServerCheck'));

    $this->getContext()->getEventDispatcher()->notify(new sfEvent($this->boxes, 'sympal.load_dashboard'));
  }
}