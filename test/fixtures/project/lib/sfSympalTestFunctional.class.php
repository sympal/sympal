<?php

class sfSympalTestFunctional extends sfTestFunctional
{
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
}