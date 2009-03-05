<?php

require_once dirname(__FILE__).'/../lib/sympal_commentsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sympal_commentsGeneratorHelper.class.php';

/**
 * sympal_comments actions.
 *
 * @package    sympal
 * @subpackage sympal_comments
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z jwage $
 */
class sympal_commentsActions extends autoSympal_commentsActions
{
  public function preExecute()
  {
    parent::preExecute();
    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));
  }

  public function executeCreate(sfWebRequest $request)
  {
    if (sfSympalConfig::get('Comments', 'requires_auth') && !$this->getUser()->isAuthenticated())
    {
      throw new sfException('Comments require that you are authenticated!');
    }

    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));

    $this->entity = Doctrine::getTable('Entity')->find($request->getParameter('comment[entity_id]'));

    $this->form = new NewCommentForm();
    $this->form->setDefault('entity_id', $this->entity->getId());

    if (sfSympalConfig::get('Comments', 'requires_auth'))
    {
      $this->form->setDefault('user_id', $this->getUser()->getGuardUser()->getId());
    }

    if (sfSympalConfig::get('Comments', 'enable_recaptcha'))
    {
      $captcha = array(
        'recaptcha_challenge_field' => $request->getParameter('recaptcha_challenge_field'),
        'recaptcha_response_field'  => $request->getParameter('recaptcha_response_field'),
      );
      $this->form->bind(array_merge($request->getParameter('comment'), array('captcha' => $captcha)));
    } else {
      $this->form->bind($request->getParameter('comment'));      
    }

    if ($this->form->isValid())
    {
      $this->form->save();

      $class = $this->entity->getType()->getName() . 'Comment';
      $field = Doctrine_Inflector::tableize($this->entity->getType()->getName()) . '_id';
      $obj = new $class();
      $obj->comment_id = $this->form->getObject()->getId();
      $obj->$field = $this->entity->getRecord()->getId();
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
