<?php

/**
 * Default actions class for sympal.
 * 
 * Similar to symfony's "default" module, but with additional actions
 * 
 * @package     
 * @subpackage  
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-04-02
 * @version     svn:$Id$ $Author$
 */
class Basesympal_defaultActions extends sfActions
{

  /**
   * Default action called when sympal is in "offline" mode
   */
  public function executeOffline(sfWebRequest $request)
  {
  }

  /**
   * Renders the sitemap
   */
  public function executeSitemap(sfWebRequest $request)
  {
    $this->setLayout(false);
    $this->sitemapGenerator = new sfSympalSitemapGenerator($this->getContext()->getConfiguration()->getApplication());
  }

  public function executeChange_language(sfWebRequest $request)
  {
    $oldCulture = $this->getUser()->getCulture();
    $this->form = new sfFormLanguage($this->getUser(), array('languages' => sfSympalConfig::getLanguageCodes()));
    unset($this->form[$this->form->getCSRFFieldName()]);

    $this->form->process($request);

    $newCulture = $this->getUser()->getCulture();

    $this->getUser()->setFlash('notice', 'Changed language successfully!');

    return $this->redirect(str_replace('/'.$oldCulture.'/', '/'.$newCulture.'/', $this->getRequest()->getReferer($this->getUser()->getReferer('@homepage'))));
  }

  public function executeChange_edit_language(sfWebRequest $request)
  {
    $this->getUser()->setEditCulture($request->getParameter('language'));
    return $this->redirect($this->getRequest()->getReferer($this->getUser()->getReferer('@homepage')));
  }

  public function executeAsk_confirmation(sfWebRequest $request)
  {
    if ($this->isAjax())
    {
      $this->setLayout(false);
    }
    else
    {
      $this->loadAdminTheme();
    }

    $this->url = $request->getUri();
    $this->title = $request->getAttribute('title');
    $this->message = $request->getAttribute('message');
    $this->isAjax = $request->getAttribute('is_ajax');
  }

  public function executeError404()
  {
    $this->loadSiteTheme();
  }

  public function executeDisabled()
  {
    $this->loadSiteTheme();
  }
}
