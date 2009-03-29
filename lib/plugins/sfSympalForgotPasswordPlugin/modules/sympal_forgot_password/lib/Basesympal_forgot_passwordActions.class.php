<?php

/**
 * Base actions for the sfSympalForgotPasswordPlugin sympal_forgot_password module.
 * 
 * @package     sfSympalForgotPasswordPlugin
 * @subpackage  sympal_forgot_password
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_forgot_passwordActions extends sfActions
{
  public function preExecute()
  {
    if ($this->getUser()->isAuthenticated())
    {
      $this->redirect('@homepage');
    }
  }

  public function executeIndex($request)
  {
    $this->menuItem = Doctrine::getTable('MenuItem')->getForSlug('forgot-password');

    $this->form = new ForgotPasswordForm();
    if ($request->getMethod() == sfRequest::POST)
    {
      $this->form->bind($request->getParameter($this->form->getName()));
      if ($this->form->isValid())
      {
        $this->getUser()->setFlash('notice', 'E-mail successfully sent!');

        $this->form->send();

        $this->redirect('@homepage');
      } else {
        $this->getUser()->setFlash('error', 'Invalid e-mail address!');
      }
    }
  }

  public function executeChange($request)
  {
    $this->menuItem = Doctrine::getTable('MenuItem')->getForSlug('forgot-password');

    $this->forgotPassword = $this->getRoute()->getObject();
    $this->user = $this->forgotPassword->User;
    $this->profile = $this->user->Profile;
    $this->form = new sfGuardUserAdminForm($this->user);

    unset(
      $this->form['username'],
      $this->form['is_active'],
      $this->form['is_super_admin'],
      $this->form['updated_at'],
      $this->form['groups_list'],
      $this->form['permissions_list']
    );

    if ($request->getMethod() == sfRequest::POST)
    {
      $this->form->bind($request->getParameter($this->form->getName()));
      if ($this->form->isValid())
      {
        $this->form->save();

        Doctrine::getTable('ForgotPassword')
          ->createQuery('p')
          ->delete()
          ->where('p.user_id = ?', $this->user->id)
          ->execute();

        $this->getUser()->setFlash('notice', 'Password updated successfully!');

        $this->redirect('@sympal_signin');
      }
    }
  }
}

class ForgotPasswordForm extends sfForm
{
  public function setup()
  {
    $this->widgetSchema['email_address'] = new sfWidgetFormInput();
    $this->validatorSchema['email_address'] = new sfValidatorString();

    $this->widgetSchema->setNameFormat('forgot_password[%s]');
  }

  public function isValid()
  {
    $valid = parent::isValid();
    if ($valid)
    {
      $values = $this->getValues();
      $emailAddress = $values['email_address'];
      $this->profile = Doctrine::getTable('UserProfile')
        ->createQuery('p')
        ->innerJoin('p.User u')
        ->where('p.email_address = ?', $emailAddress)
        ->fetchOne();
      if ($this->profile)
      {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  public function send()
  {
    $forgotPassword = new ForgotPassword();
    $forgotPassword->user_id = $this->profile->user_id;
    $forgotPassword->unique_key = md5(rand() + time());
    $forgotPassword->expires_at = new Doctrine_Expression('NOW() + 86400');
    $forgotPassword->save();

    sfSympalToolkit::sendEmail('sympal_forgot_password/send_request', array('forgot_password' => $forgotPassword, 'email_address' => $this->profile->email_address, 'profile' => $this->profile));
  }
}