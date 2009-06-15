<?php

class Basesympal_editorActions extends sfActions
{
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

  public function executeChange_content_slot_type(sfWebRequest $request)
  {
    $this->contentSlot = $this->getRoute()->getObject();
    $this->contentSlot->content_slot_type_id = $request->getParameter('type');
    $this->contentSlot->save();

    $this->form = $this->_getContentSlotForm($request);

    $this->setLayout(false);
    $this->setTemplate('edit_slot');
  }

  protected function _getContentSlotForm(sfWebRequest $request)
  {
    $this->form = new ContentSlotForm($this->contentSlot);

    if ($request->getParameter('is_column'))
    {
      unset($this->form[$this->getUser()->getCulture()]);
      unset($this->form['value']);

      $name = $request->getParameter('name');
      $form = new InlineContentPropertyForm($this->contentSlot->RelatedContent, array('contentSlot' => $this->contentSlot));
      $this->form->embedForm('RelatedContent', $form);      
      $widgetSchema = $this->form->getWidgetSchema();
      $widgetSchema['content_slot_type_id'] = new sfWidgetFormInputHidden();
    }

    return $this->form;
  }

  public function executeEdit_slot(sfWebRequest $request)
  {
    $this->setLayout(false);

    $this->contentSlot = $this->getRoute()->getObject();

    if ($this->getUser()->hasUnsavedContentSlotValue($this->contentSlot))
    {
      $unsavedValue = $this->getUser()->getUnsavedContentSlotValue($this->contentSlot);
      $this->contentSlot->setValue($unsavedValue);
    }

    $this->form = $this->_getContentSlotForm($request);
  }

  public function executeSave_slot(sfWebrequest $request)
  {
    $this->contentSlot = $this->getRoute()->getObject();
    $this->form = $this->_getContentSlotForm($request);

    $values = $request->getParameter($this->form->getName());
    $this->form->bind($values);
    if ($this->form->isValid())
    {
      $this->form->save();
      $this->getUser()->clearUnsavedContentSlotValue($this->contentSlot);
    } else {
      exit('errors'.(string) $this->form);
      // handle errors?
    }

    $this->setLayout(false);
    $this->setTemplate('preview_slot');
    $this->getUser()->setFlash('notice', 'Successfully saved slot contents!');
  }

  public function executePreview_slot(sfWebRequest $request)
  {
    $this->setLayout(false);

    $this->contentSlot = $this->getRoute()->getObject();
    $this->contentSlot->setValue($request->getParameter('value'));

    $this->getUser()->updateUnsavedContentSlotValue($this->contentSlot, $request->getParameter('value'));
  }

  public function executeToggle_edit(sfWebRequest $request)
  {
    $mode = $this->getUser()->toggleEditMode();

    if ($mode == 'off')
    {
      $msg = 'Edit mode turned off successfully.';
    } else {
      $msg = 'Edit mode turned on successfully.';
    }

    $this->getUser()->setFlash('notice', $msg);

    if ($mode == 'off')
    {
      $this->redirect('@homepage');
    } else {
      $this->redirect($request->getReferer());
    }
  }

  public function executeSave_panel_position(sfWebRequest $request)
  {
    $x = $request->getParameter('x');
    $y = $request->getParameter('y');
    $this->getUser()->setAttribute('sympal_editor_panel_x', $x);
    $this->getUser()->setAttribute('sympal_editor_panel_y', $y);

    return sfView::NONE;
  }

  public function executeSave_form_current_tab(sfWebRequest $request)
  {
    if ($request->getParameter('name'))
    {
      $this->getUser()->setAttribute($request->getParameter('name').'.current_form_tab', $request->getParameter('id'), 'admin_module');
    }

    return sfView::NONE;
  }

  public function executeSave_tools_state(sfWebRequest $request)
  {
    $this->getUser()->setAttribute('editor_tools_state', $request->getParameter('state'), 'sympal');

    return sfView::NONE;
  }

  public function executeRevert_data(sfWebRequest $request)
  {
    $version = $this->getRoute()->getObject();

    $this->askConfirmation('Revert to version #'.$version['version'], 'sympal_editor/confirm_revert', array('version' => $version));

    $version->revert();

    $this->getUser()->setFlash('notice', 'Record was successfully reverted back to version #'.$version['version']);

    $this->redirect($request->getParameter('redirect_url'));
  }

  public function executeVersion_history(sfWebRequest $request)
  {
    $type = $request->getParameter('record_type');
    $id = $request->getParameter('record_id');
    $parentType = str_replace('Translation', '', $type);
    Doctrine::initializeModels($parentType);

    $this->record = Doctrine::getTable($type)
      ->createQuery()
      ->andWhere('id = ?', $id)
      ->fetchOne();

    $this->versions = Doctrine::getTable('Version')
      ->createQuery('v')
      ->andWhere('record_type = ?', $type)
      ->andWhere('record_id = ?', $id)
      ->orderBy('v.version ASC')
      ->execute();
  }
}