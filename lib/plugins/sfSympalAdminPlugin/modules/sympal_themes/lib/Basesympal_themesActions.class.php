<?php

/**
 * Base actions for the sfSympalAdminPlugin sympal_themes module.
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  sympal_themes
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_themesActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->themes = $this->getSympalContext()->getSympalConfiguration()->getThemes();

    if ($preview = $request->getParameter('preview'))
    {
      $this->getResponse()->setTitle(sprintf('Sympal Admin / Previewing %s', $preview));
      $this->loadTheme($preview);
    } else {
      $this->getResponse()->setTitle(sprintf('Sympal Admin / Themes'));
      $this->loadAdminTheme();
    }
  }
}