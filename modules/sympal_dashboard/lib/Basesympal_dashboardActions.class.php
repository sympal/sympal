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
    $this->boxes = new sfSympalMenu('Dashboard Boxes');
    $this->boxes['Sites']->setRoute('@sympal_sites');
    $this->boxes['Create Content']->setRoute('@sympal_content_new');
    $this->boxes['Menu Manager']->setRoute('@sympal_menu_manager');
    $this->boxes['Plugin Manager']->setRoute('@sympal_plugin_manager');
    $this->boxes['Content Types']->setRoute('@sympal_content_types');
    $this->boxes['Content Templates']->setRoute('@sympal_content_templates');
    $this->boxes['Slot Types']->setRoute('@sympal_content_slot_types');
    $this->boxes['Users']->setRoute('@sympal_users');
    $this->boxes['Groups']->setRoute('@sympal_groups');
    $this->boxes['Permissions']->setRoute('@sympal_permissions');
    $this->boxes['Configuration']->setRoute('@sympal_config');
    $this->boxes['Sitemap']->setRoute('@sympal_sitemap');

    $this->plugins = $this->getContext()->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration()->getInstalledPlugins();
    $this->contentTypes = Doctrine::getTable('ContentType')->findAll();
  }
}