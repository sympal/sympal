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

    $q = Doctrine::getTable('Entity')
      ->getTypeQuery('UserProfile')
      ->where('p.user_id = ?', $this->user->id);
    $this->entity = $q->fetchOne();

    if (!$this->entity)
    {
      $this->getUser()->setFlash('notice', 'You have not created a user profile yet. A blank one has been created for you to customize!');

      $this->entity = new Entity();
      $this->entity->Type = Doctrine::getTable('EntityType')->findOneByName('UserProfile');
      $this->entity->is_published = true;
      $this->entity->slug = $this->user->username;

      $this->profile = $this->entity->UserProfile;
      $this->profile->first_name = $this->user->username;
      $this->profile->user_id = $this->user->id;
      $this->entity->save();

    }
    $this->profile = $this->entity->UserProfile;
    $this->menuItem = $this->entity->getMainMenuItem();

    $this->renderer = sfSympalContext::getInstance()->renderEntity($this->menuItem, $this->entity);
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