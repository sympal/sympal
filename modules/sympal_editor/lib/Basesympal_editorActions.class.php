<?php

class Basesympal_editorActions extends sfActions
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
    } else {
      exit((string) $this->form);
      // handle errors?
    }

    $this->setLayout(false);
    $this->setTemplate('preview_slot');
  }

  public function executePreview_slot(sfWebRequest $request)
  {
    $this->setLayout(false);

    $this->contentSlot = $this->getRoute()->getObject();
    $this->contentSlot->setValue($request->getParameter('value'));
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
}