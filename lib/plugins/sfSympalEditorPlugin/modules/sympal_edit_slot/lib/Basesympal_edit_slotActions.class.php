<?php

/**
 * Base actions for the sfSympalPlugin sympal_edit_slot module.
 * 
 * @package     sfSympalPlugin
 * @subpackage  sympal_edit_slot
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_edit_slotActions extends sfActions
{
  public function preExecute()
  {
    $this->setLayout(false);
  }

  public function executeChange_content_slot_type(sfWebRequest $request)
  {
    $this->content = Doctrine_Core::getTable('sfSympalContent')->find($request->getParameter('content_id'));
    $this->contentSlot = Doctrine_Core::getTable('sfSympalContentSlot')->find($request->getParameter('id'));
    $this->contentSlot->setContentRenderedFor($this->content);
    $this->contentSlot->setType($request->getParameter('type'));
    $this->contentSlot->save();

    $this->form = $this->contentSlot->getEditForm();
  }

  public function executeSave_slots(sfWebRequest $request)
  {
    $this->contentSlots = array();
    $this->failedContentSlots = array();
    $this->errors = array();

    $slotIds = $request->getParameter('slot_ids');
    $contentIds = $request->getParameter('content_ids');
    foreach ($slotIds as $slotId)
    {
      $content = Doctrine_Core::getTable('sfSympalContent')->find($contentIds[$slotId]);
      $contentSlot = Doctrine_Core::getTable('sfSympalContentSlot')->find($slotId);
      $contentSlot->setContentRenderedFor($content);
      $form = $contentSlot->getEditForm();
      $form->bind($request->getParameter($form->getName()));
      if ($form->isValid())
      {
        if ($request->getParameter('preview'))
        {
          $form->updateObject();
        } else {
          $form->save();
        }
        $this->contentSlots[] = $contentSlot;
      } else {
        $this->failedContentSlots[] = $contentSlot;
        foreach ($form as $name => $field)
        {
          if ($field->hasError())
          {
            $this->errors[$contentSlot->getName()] = $field->getError();
          }
        }
      }
    }
  }
  
  /**
   * Called via ajax to load in the form that represents the given slot
   */
  public function executeSlot_form(sfWebRequest $request)
  {
    $this->contentSlot = $this->setupContentSlot($request);
    
    $this->form = $this->contentSlot->getEditForm();
    
    $this->renderPartial('sympal_edit_slot/slot_editor');
    
    return sfView::NONE;
  }
  
  /**
   * Renders an individual slot
   */
  public function executeSlot_view(sfWebRequest $request)
  {
    $this->contentSlot = $this->setupContentSlot($request);
    
    $this->renderText($this->contentSlot->render());
    
    return sfView::NONE;
  }
  
  /**
   * Handles the form submit for a given slot
   */
  public function executeSlot_save(sfWebRequest $request)
  {
    $this->contentSlot = $this->setupContentSlot($request);
    
    $this->form = $this->contentSlot->getEditForm();
    $this->form->bind($request->getParameter($this->form->getName()));
    
    if ($this->form->isValid())
    {
      $this->form->save();
      
      $this->getUser()->setFlash('notice', __('Slot saved'), false);
    }
    else
    {
      $this->getUser()->setFlash('error', __('There was an error saving your slot'), false);
    }
    
    $this->renderPartial('sympal_edit_slot/slot_editor_form');
    
    return sfView::NONE;
  }
  
  /**
   * For the slot_form and slot_save, this sets up and retrieves the content
   * slot in question
   * 
   * @return sfSympalContentSlot
   */
  protected function setupContentSlot(sfWebRequest $request)
  {
    $content = Doctrine_Core::getTable('sfSympalContent')->find($request->getParameter('content_id'));
    $this->forward404Unless($content);
    
    $contentSlot = $this->getRoute()->getObject();
    $contentSlot->setContentRenderedFor($content);
    
    return $contentSlot;
  }
}
