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
    $this->form = new sfFormLanguage($this->getUser(), array('languages' => sfSympalConfig::get('language_codes', null, array($this->getUser()->getCulture()))));
    unset($this->form[$this->form->getCSRFFieldName()]);

    $this->form->process($request);

    $this->getUser()->setFlash('notice', 'Changed language successfully!');

    return $this->redirect($request->getReferer());
  }

  public function executePublish_content(sfWebRequest $request)
  {
    $content = $this->getRoute()->getObject()->publish();
    
    $this->getUser()->setFlash('notice', 'Content published successfully!');
    $this->redirect($request->getReferer());
  }

  public function executeUnpublish_content(sfWebRequest $request)
  {
    $content = $this->getRoute()->getObject()->unpublish();
    
    $this->getUser()->setFlash('notice', 'Content un-published successfully!');
    $this->redirect($request->getReferer());
  }

  public function executeLock_content(sfWebRequest $request)
  {
    $content = $this->getRoute()->getObject();
    if (!$content->locked_by)
    {
      $content->obtainLock($this->getUser()->getGuardUser());
      
      $this->getUser()->setFlash('notice', 'Lock obtained successfully!');
    } else {
      $this->getUser()->setFlash('error', 'Could not obtain lock!');
    }
    $this->redirect($request->getReferer());
  }

  public function executeUnlock_content(sfWebRequest $request)
  {
    $content = $this->getRoute()->getObject();
    $content->locked_by = null;
    $content->save();

    $this->getUser()->setFlash('notice', 'Lock released successfully!');

    $this->redirect($content->getRoute());
  }

  public function executeChange_content_slot_type(sfWebRequest $request)
  {
    $this->contentSlot = $this->getRoute()->getObject();
    $this->contentSlot->content_slot_type_id = $request->getParameter('type');
    $this->contentSlot->save();

    $this->form = new ContentSlotForm($this->contentSlot);
    $this->setLayout(false);
    $this->setTemplate('edit_slot');
  }

  public function executeEdit_slot()
  {
    $this->contentSlot = $this->getRoute()->getObject();
    $this->form = new ContentSlotForm($this->contentSlot);
  }

  public function executeSave_slot(sfWebrequest $request)
  {
    $this->setLayout(false);

    $this->contentSlot = $this->getRoute()->getObject();
    $this->contentSlot->value = $request->getParameter('value');
    $this->contentSlot->save();

    $this->setTemplate('preview_slot');
  }

  public function executePreview_slot(sfWebRequest $request)
  {
    $this->setLayout(false);

    $this->contentSlot = $this->getRoute()->getObject();
    $this->contentSlot->value = $request->getParameter('value');
  }

  public function executeToggle_edit(sfWebRequest $request)
  {
    $mode = $this->getUser()->toggleEditMode();

    if ($mode == 'off')
    {
      $msg = 'Edit mode turned off successfully. Any content you had a lock on were released!';
    } else {
      $msg = 'Edit mode turned on successfully. To edit an content you must obtain an edit lock first!';
    }

    $this->getUser()->setFlash('notice', $msg);

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