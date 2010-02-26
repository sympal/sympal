<?php

class Basesympal_commentsActions extends autoSympal_commentsActions
{
  public function executeCreate(sfWebRequest $request)
  {
    $this->authComments();
    
    $this->loadDefaultTheme();

    $this->content = Doctrine::getTable('sfSympalContent')->find($request['sf_sympal_comment']['content_id']);

    $this->form = new sfSympalNewCommentForm();
    $this->form->setDefault('content_id', $this->content->getId());

    if (sfSympalConfig::get('sfSympalCommentsPlugin', 'requires_auth'))
    {
      $this->form->setDefault('user_id', $this->getUser()->getGuardUser()->getId());
    }

    $this->form->bind($request->getParameter($this->form->getName()));
    if ($this->form->isValid())
    {
      $this->form->getObject()->status = sfSympalConfig::get('sfSympalCommentsPlugin', 'default_status', 'Approved');
      $this->form->save();

      $obj = new sfSympalContentComment();
      $obj->comment_id = $this->form->getObject()->getId();
      $obj->content_id = $this->content->getId();
      $obj->save();

      $this->redirect($request->getParameter('from_url') . '#comment_' . $this->form->getObject()->getId());
    }
  }

  public function executeBatchApprove(sfWebRequest $request)
  {
    $this->_batchChangeStatus($request, 'Approved');
  }

  public function executeBatchDeny(sfWebRequest $request)
  {
    $this->_batchChangeStatus($request, 'Denied');
  }

  public function executeBatchPending(sfWebRequest $request)
  {
    $this->_batchChangeStatus($request, 'Pending');
  }

  protected function _batchChangeStatus($request, $status)
  {
    $ids = $request->getParameter('ids');
    $objects = Doctrine_Query::create()
      ->from('sfSympalComment c')
      ->whereIn('c.id', $ids)
      ->execute();

    foreach ($objects as $object)
    {
      $object->setStatus($status);
      $object->save();
    }
  }
  
  /**
   * Function validates if the current user has the rights to be posting
   * comments.
   * 
   * @throws sfException
   */
  protected function authComments()
  {
    if (sfSympalConfig::get('sfSympalCommentsPlugin', 'requires_auth') && !$this->getUser()->isAuthenticated())
    {
      throw new sfException('Comments require that you are authenticated!');
    }
    
    if (!sfSympalConfig::get('sfSympalCommentsPlugin', 'enabled'))
    {
      throw new sfException('Commenting is disabled');
    }
  }
}