<?php

class sympal_commentsComponents extends sfComponents
{
  public function executeFor_entity()
  {
    $this->form = new NewCommentForm();
    $this->form->setDefault('entity_id', $this->entity->getId());

    if (sfSympalConfig::get('Comments', 'requires_auth') && $this->getUser()->isAuthenticated())
    {
      $this->form->setDefault('user_id', $this->getUser()->getGuardUser()->getId());
    }
  }
}