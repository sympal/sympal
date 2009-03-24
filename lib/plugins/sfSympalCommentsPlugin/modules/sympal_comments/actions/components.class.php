<?php

class sympal_commentsComponents extends sfComponents
{
  public function executeFor_content()
  {
    $this->form = new NewCommentForm();
    $this->form->setDefault('content_id', $this->content->getId());

    if (sfSympalConfig::get('Comments', 'requires_auth') && $this->getUser()->isAuthenticated())
    {
      $this->form->setDefault('user_id', $this->getUser()->getGuardUser()->getId());
    }
  }
}