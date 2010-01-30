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

    $content = Doctrine_Core::getTable('sfSympalContent')->find($request->getParameter('content_id'));
    $slotIds = $request->getParameter('slots');
    foreach ($slotIds as $slotId)
    {
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
}