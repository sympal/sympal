<?php

/**
 * sympal_editor actions.
 *
 * @package    sympal
 * @subpackage sympal_editor
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z jwage $
 */
class sympal_editorActions extends sfActions
{
  public function executeChange_language(sfWebRequest $request)
  {
    $this->form = new sfFormLanguage($this->getUser(), array('languages' => Doctrine::getTable('Language')->getLanguageCodes()));
    unset($this->form[$this->form->getCSRFFieldName()]);

    $this->form->process($request);

    $this->getUser()->setFlash('notice', 'Changed language successfully!');

    return $this->redirect('@homepage');
  }

  public function executePublish_entity(sfWebRequest $request)
  {
    $entity = $this->getRoute()->getObject()->publish();
    
    $this->getUser()->setFlash('notice', 'Entity published successfully!');
    $this->redirect($request->getReferer());
  }

  public function executeUnpublish_entity(sfWebRequest $request)
  {
    $entity = $this->getRoute()->getObject()->unpublish();
    
    $this->getUser()->setFlash('notice', 'Entity un-published successfully!');
    $this->redirect($request->getReferer());
  }

  public function executeLock_entity(sfWebRequest $request)
  {
    $entity = $this->getRoute()->getObject();
    if (!$entity->locked_by)
    {
      $entity->obtainLock($this->getUser()->getGuardUser());
      
      $this->getUser()->setFlash('notice', 'Lock obtained successfully!');
    } else {
      $this->getUser()->setFlash('error', 'Could not obtain lock!');
    }
    $this->redirect($request->getReferer());
  }

  public function executeUnlock_entity(sfWebRequest $request)
  {
    $entity = $this->getRoute()->getObject();
    $entity->locked_by = null;
    $entity->save();

    $this->getUser()->setFlash('notice', 'Lock released successfully!');

    $this->redirect($entity->getRoute());
  }

  public function executeChange_entity_slot_type(sfWebRequest $request)
  {
    $this->entitySlot = $this->getRoute()->getObject();
    $this->entitySlot->entity_slot_type_id = $request->getParameter('type');
    $this->entitySlot->save();

    $this->form = new EntitySlotForm($this->entitySlot);
    $this->setLayout(false);
    $this->setTemplate('edit_slot');
  }

  public function executeEdit_slot()
  {
    $this->entitySlot = $this->getRoute()->getObject();
    $this->form = new EntitySlotForm($this->entitySlot);
  }

  public function executeSave_slot(sfWebrequest $request)
  {
    $this->setLayout(false);

    $this->entitySlot = $this->getRoute()->getObject();
    $this->entitySlot->value = $request->getParameter('value');
    $this->entitySlot->save();

    $this->setTemplate('preview_slot');
  }

  public function executePreview_slot(sfWebRequest $request)
  {
    $this->setLayout(false);

    $this->entitySlot = $this->getRoute()->getObject();
    $this->entitySlot->value = $request->getParameter('value');
  }

  public function executeToggle_edit(sfWebRequest $request)
  {
    $this->getUser()->setAttribute('sympal_edit', !$this->getUser()->getAttribute('sympal_edit', false));
    $mode = $this->getUser()->getAttribute('sympal_edit', false) ? 'on':'off';
    $this->getUser()->setFlash('notice', 'Edit mode turned '.$mode.'!');
    $this->redirect($request->getReferer());
  }

  public function executeSave_panel_position(sfWebRequest $request)
  {
    $x = $request->getParameter('x');
    $y = $request->getParameter('y');
    $this->getUser()->setAttribute('sympal_editor_panel_x', $x);
    $this->getUser()->setAttribute('sympal_editor_panel_y', $y);

    return sfView::NONE;
  }
}