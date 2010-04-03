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
    $response = $this->getResponse();
    $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/js/shortcuts.js'));
    $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalAdminPlugin/js/shortcuts.js'));

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

    $this->dashboardRight = new sfSympalMenu('Sympal Dashboard Right');

    $numUsers = Doctrine_Core::getTable('sfGuardUser')->count();
    $this->dashboardRight->addChild(sprintf('<label>Users</label> %s', $numUsers), '@sympal_users');

    $numSites = Doctrine_Core::getTable('sfSympalSite')->count();
    $this->dashboardRight->addChild(sprintf('<label>Sites</label> %s', $numSites), '@sympal_sites');

    $numContentTypes = Doctrine_Core::getTable('sfSympalContentType')->count();
    $this->dashboardRight->addChild(sprintf('<label>Content Types</label> %s', $numContentTypes), '@sympal_content_types');

    $content = $this->dashboardRight->addChild('Site Content')->setliClass('main_section');
    $contentTypes = Doctrine::getTable('sfSympalContentType')->getAllContentTypes();
    foreach ($contentTypes as $contentType)
    {
      $numPublishedContent = Doctrine_Core::getTable('sfSympalContent')
        ->createQuery('c')
        ->andWhere('c.content_type_id = ?', $contentType->getId())
        ->count();
      $content->addChild(
        sprintf('<label>%s</label> %s', $contentType->getLabel(), $numPublishedContent),
        '@sympal_content_list_type?type='.$contentType->getId()
      );
    }

    sfApplicationConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this->dashboardRight, 'sympal.load_dashboard_right'));
  }
}