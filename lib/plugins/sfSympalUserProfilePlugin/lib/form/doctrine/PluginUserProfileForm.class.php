<?php

/**
 * PluginUserProfile form.
 *
 * @package    form
 * @subpackage UserProfile
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
abstract class PluginUserProfileForm extends BaseUserProfileForm
{
  public function setup()
  {
    parent::setup();
    unset($this['user_id'], $this['entity_id']);

    $userForm = new sfGuardUserAdminForm($this->object->User);
    unset(
      $userForm['is_active'],
      $userForm['is_super_admin'],
      $userForm['updated_at'],
      $userForm['groups_list'],
      $userForm['permissions_list']
    );
    $this->embedForm('User', $userForm);
  }
}