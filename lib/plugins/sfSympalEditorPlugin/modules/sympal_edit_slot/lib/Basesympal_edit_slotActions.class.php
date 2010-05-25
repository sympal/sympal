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

  /**
   * Changes the type of a slot and then re-renders the form
   */
  public function executeChange_content_slot_type(sfWebRequest $request)
  {
    $this->contentSlot = $this->setupContentSlot($request);
    
    $type = $request->getParameter('new_type');
    $validTypes = array_keys(sfSympalConfig::get('content_slot_types', null, array()));
    if (!in_array($type, $validTypes))
    {
      // in the future, return something json-intelligent to report an error
      $this->forward404(sprintf('Type "%s" is not a valid slot type', $type));
    }
    
    $this->contentSlot->setType($type);
    $this->contentSlot->save();

    // render the slot editor
    $slotEditor = $this->getSympalContext()
      ->getService('slot_renderer')
      ->renderSlot($this->contentSlot, sfSympalSlotRenderer::SLOT_EDITOR);
    $this->renderText($slotEditor);

    // refresh the slot editors
    $this->renderText(sprintf('
      <script type="text/javascript">
        $(document).ready(function() {
          $.bindAllSlotEditors();
          $("#sympal_slot_wrapper_%s").sympalSlot("openEditor");
        });
      </script>
    ', $this->contentSlot->id));

    return sfView::NONE;
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

    // render the slot in view mode
    $rendered = $this->getSympalContext()
      ->getService('slot_renderer')
      ->renderSlot($this->contentSlot, sfSympalSlotRenderer::SLOT_VIEW);
    $this->renderText($rendered);
    
    return sfView::NONE;
  }
  
  /**
   * Handles the form submit for a given slot. This could come from two sources:
   *   1) Inline, frontend editing. (ajax request)
   *   2) Admin, non-inline editing (non-ajax request)
   */
  public function executeSlot_save(sfWebRequest $request)
  {
    $this->contentSlot = $this->setupContentSlot($request);
    
    $this->form = $this->contentSlot->getEditForm();
    $this->form->bind($request->getParameter($this->form->getName()));
    
    if ($this->form->isValid())
    {
      $this->form->save();
      $this->getUser()->setFlash('saved', __('Slot saved'), false);
      
      // if we close editor on save, just return the flash js and the editor close js
      if ($request->isXmlHttpRequest() && sfSympalConfig::get('inline_editing', 'close_editor_on_save', false))
      {
        $this->getContext()->getConfiguration()->loadHelpers('SympalContentSlotEditor');
        $this->renderText(trigger_flash_from_user($this->getUser()));
        $this->renderText(sprintf('
        <script type="text/javascript">
          $(document).ready(function() {
            $("#sympal_slot_wrapper_%s").sympalSlot("closeEditor");
          });
        </script>
        ', $this->contentSlot->id));
        
        return sfView::NONE;
      }
    }
    else
    {
      $this->getUser()->setFlash('error', __('There was an error saving your slot'), false);
    }

    // we need to route the response appropriately based on ajax/non-ajax
    if ($request->isXmlHttpRequest())
    {
      // ajax, just re-show the form
      $this->renderPartial('sympal_edit_slot/slot_editor_form');
    }
    else
    {
      // handle non-ajax, which come from sympal_content/edit_slots
      // this is a bit of a hack, we redirect even on errors, which is technically a fail
      $this->redirect('@sympal_content_edit_slots?id='.$this->contentSlot->getContentRenderedFor()->id);
    }
    
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
