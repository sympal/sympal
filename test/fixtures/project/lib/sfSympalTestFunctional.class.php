<?php

class sfSympalTestFunctional extends sfTestFunctional
{
  /**
   * Override to load in some testers
   */
  public function __construct($hostname = null, $remote = null, $options = array())
  {    
    parent::__construct($hostname, $remote, $options);
    
    $this->setTester('doctrine', 'sfTesterDoctrine');
    $this->setTester('theme', 'sfTesterTheme');
  }

  public function signInAsAdmin()
  {
    return $this->
      get('/security/signin')->
      click('input[type="submit"]', array('signin' => array('username' => 'admin', 'password' => 'admin')), array('method' => 'post', '_with_csrf' => true))->
      with('response')->begin()->
        followRedirect()->
      end()
    ;
  }

  public function signOut()
  {
    return $this->
      get('/security/signout')->
      with('response')->begin()->
        followRedirect()->
      end()
    ;
  }

  public function clearCache()
  {
    return $this->get('/')->getContext()->getController()->getActionStack()->getLastEntry()->getActionInstance()->clearCache();
  }
}