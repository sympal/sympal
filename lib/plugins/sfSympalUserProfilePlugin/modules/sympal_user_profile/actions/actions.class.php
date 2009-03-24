<?php

/**
 * sympal_user_profile actions.
 *
 * @package    sympal
 * @subpackage sympal_user_profile
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class sympal_user_profileActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->user = $this->getUser()->getGuardUser();

    Doctrine::getTable('UserProfile');

    $q = Doctrine::getTable('Content')
      ->getTypeQuery('UserProfile')
      ->where('p.user_id = ?', $this->user->id);
    $this->content = $q->fetchOne();

    if (!$this->content)
    {
      $this->getUser()->setFlash('notice', 'You have not created a user profile yet. A blank one has been created for you to customize!');

      $this->content = new Content();
      $this->content->Type = Doctrine::getTable('ContentType')->findOneByName('UserProfile');
      $this->content->is_published = true;
      $this->content->slug = $this->user->username;

      $this->profile = $this->content->UserProfile;
      $this->profile->first_name = $this->user->username;
      $this->profile->user_id = $this->user->id;
      $this->content->save();

    }
    $this->profile = $this->content->UserProfile;
    $this->menuItem = $this->content->getMainMenuItem();

    $this->renderer = sfSympalContext::getInstance()->processPhpCode($this->menuItem, $this->content);
    $this->form = new UserProfileForm($this->profile);

    if ($request->getMethod() == 'POST')
    {
      $this->form->bind($request->getParameter($this->form->getName()));
      if ($this->form->isValid())
      {
        $this->form->save();

        $this->getUser()->setFlash('notice', 'User profile updated successfully!');
        $this->redirect('@sympal_user_profile');
      }
    }
  }
}