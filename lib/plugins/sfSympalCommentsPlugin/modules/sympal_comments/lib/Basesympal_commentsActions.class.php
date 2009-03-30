<?php

class Basesympal_commentsActions extends autoSympal_commentsActions
{
  public function executeCreate(sfWebRequest $request)
  {
    if (sfSympalConfig::get('Comments', 'requires_auth') && !$this->getUser()->isAuthenticated())
    {
      throw new sfException('Comments require that you are authenticated!');
    }

    $this->content = Doctrine::getTable('Content')->find($request->getParameter('comment[content_id]'));

    $this->form = new NewCommentForm();
    $this->form->setDefault('content_id', $this->content->getId());

    if (sfSympalConfig::get('Comments', 'requires_auth'))
    {
      $this->form->setDefault('user_id', $this->getUser()->getGuardUser()->getId());
    }

    sfSympalFormToolkit::bindFormRecaptcha($this->form, sfSympalConfig::get('sfSympalCommentsPlugin', 'enable_recaptcha'));

    if ($this->form->isValid())
    {
      $this->form->getObject()->status = sfSympalConfig::get('Comments', 'default_status', 'Approved');
      $this->form->save();

      $obj = new ContentComment();
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
      ->from('Comment c')
      ->whereIn('c.id', $ids)
      ->execute();

    foreach ($objects as $object)
    {
      $object->setStatus($status);
      $object->save();
    }
  }
}