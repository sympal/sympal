<?php

class sfSympalUserRememberMeFilter extends sfFilter
{
  public function execute($filterChain)
  {
    $cookieName = sfSympalConfig::get('sfSympalUserPlugin', 'remember_me_cookie_name', 'sfRemember');

    if (
      $this->isFirstCall()
      &&
      $this->context->getUser()->isAnonymous()
      &&
      $cookie = $this->context->getRequest()->getCookie($cookieName)
    )
    {
      $q = Doctrine_Query::create()
            ->from('RememberKey r')
            ->innerJoin('r.User u')
            ->where('r.remember_key = ?', $cookie);

      if ($q->count())
      {
        $this->context->getUser()->signIn($q->fetchOne()->User);
      }
    }

    $filterChain->execute();
  }
}
