<?php
class sfSympalForgotPasswordPluginConfiguration extends sfPluginConfiguration
{
  public 
    $dependencies = array(
      'sfSympalPlugin'
    );

  public function install($installVars, $task)
  {
    $menuItem = new MenuItem();
    $menuItem->name = 'Forgot Password';
    $menuItem->label = 'Forgot Password?';
    $menuItem->route = '@sympal_forgot_password';
    $menuItem->requires_no_auth = true;

    $task->addToMenu($menuItem);
  }

  public function uninstall($uninstallVars, $task)
  {
    $q = Doctrine::getTable('MenuItem')
      ->createQuery('m')
      ->where('m.name = ?', 'Forgot Password');
    $menuItem = $q->fetchOne();
    if ($menuItem)
    {
      $menuItem->getNode()->delete();
    }
  }
}