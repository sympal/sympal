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

    $this->indicators = new sfSympalMenu('Sympal Indicators');

    $numUsers = Doctrine_Core::getTable('sfGuardUser')->count();
    $this->indicators->addChild(sprintf(__('Users: %s'), $numUsers), '@sympal_users');

    $numSites = Doctrine_Core::getTable('sfSympalSite')->count();
    $this->indicators->addChild(sprintf('Sites: %s', $numSites), '@sympal_sites');

    $numContentTypes = Doctrine_Core::getTable('sfSympalContentType')->count();
    $this->indicators->addChild(sprintf('Content Types: %s', $numContentTypes), '@sympal_content_types');

    $contentTypes = Doctrine::getTable('sfSympalContentType')->getAllContentTypes();
    foreach ($contentTypes as $contentType)
    {
      $numPublishedContent = Doctrine_Core::getTable('sfSympalContent')
        ->createQuery('c')
        ->where('c.date_published < NOW()')
        ->andWhere('c.content_type_id = ?', $contentType->getId())
        ->count();
      $this->indicators->addChild(
        sprintf('Published: %s Content %s', $contentType->getLabel(), $numPublishedContent),
        '@sympal_content_list_type?type='.$contentType->getId().'&published=1'
      );

      $numUnPublishedContent = Doctrine_Core::getTable('sfSympalContent')
        ->createQuery('c')
        ->where('c.date_published >= NOW() OR c.date_published IS NULL')
        ->andWhere('c.content_type_id = ?', $contentType->getId())
        ->count();
      $this->indicators->addChild(
        sprintf('Un-Published: %s Content %s', $contentType->getLabel(), $numUnPublishedContent),
        '@sympal_content_list_type?type='.$contentType->getId().'&published=0'
      );
    }

    sfApplicationConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this->indicators, 'sympal.load_indicators'));
  }
}
