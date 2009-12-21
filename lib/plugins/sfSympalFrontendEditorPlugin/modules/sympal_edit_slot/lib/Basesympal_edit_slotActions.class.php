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
  public function executeChange_content_slot_type(sfWebRequest $request)
  {
    $this->contentSlot = $this->_getContentSlot($request);
    $this->contentSlot->content_slot_type_id = $request->getParameter('type');
    $this->contentSlot->save();

    $this->form = $this->_getContentSlotForm($request);

    $this->setLayout(false);
    $this->setTemplate('edit_slot');
  }

  protected function _getContentSlotColumnForm(sfWebRequest $request)
  {
    $content = $this->contentSlot->getContentRenderedFor();
    $contentTable = $content->getTable();

    if ($contentTable->hasField($this->contentSlot->name))
    {
      $form = new sfSympalInlineEditContentForm($content);
      $form->useFields(array($this->contentSlot->name));
    }

    if (sfSympalConfig::isI18nEnabled('sfSympalContent'))
    {
      $contentTranslationTable = Doctrine::getTable('sfSympalContentTranslation');
      if ($contentTranslationTable->hasField($this->contentSlot->name))
      {
        $form = new sfSympalInlineEditContentForm($content);
        $form->useFields(array($this->getUser()->getCulture()));
      }      
    }

    $contentTypeClassName = $content->getContentTypeClassName();
    $contentTypeFormClassName = $contentTypeClassName.'Form';
    $contentTypeTable = Doctrine_Core::getTable($contentTypeClassName);
    if ($contentTypeTable->hasField($this->contentSlot->name))
    {
      $form = new $contentTypeFormClassName($content->getRecord());
      $form->useFields(array($this->contentSlot->name));
    }

    if (sfSympalConfig::isI18nEnabled($contentTypeClassName))
    {
      $contentTypeTranslationClassName = $contentTypeClassName.'Translation';
      $contentTypeTranslationFormClassName = $contentTypeTranslationClassName.'Form';
      $contentTypeTranslationTable = Doctrine_Core::getTable($contentTypeTranslationClassName);
      if ($contentTypeTranslationTable->hasField($this->contentSlot->name))
      {
        $form = new $contentTypeFormClassName($content->getRecord());
        $form->useFields(array($this->getUser()->getCulture()));
      }
    }

    if (!$form)
    {
      throw new InvalidArgumentException('Invalid content slot');
    }

    return $form;
  }

  protected function _getContentSlot(sfWebRequest $request)
  {
    $this->contentSlot = $this->getRoute()->getObject();
    $this->content = Doctrine_Core::getTable('sfSympalContent')->find($request['content_id']);
    $this->contentSlot->setContentRenderedFor($this->content);

    return $this->contentSlot;
  }

  protected function _getContentSlotForm(sfWebRequest $request)
  {
    sfSympalContentSlotForm::disableCSRFProtection();

    $this->contentSlot = $this->_getContentSlot($request);

    if ($this->contentSlot->is_column)
    {
      $this->form = $this->_getContentSlotColumnForm($request);
    } else {
      $this->form = new sfSympalContentSlotForm($this->contentSlot);
    }

    return $this->form;
  }

  public function executeEdit_slot(sfWebRequest $request)
  {
    $this->setLayout(false);

    $this->contentSlot = $this->_getContentSlot($request);
    $this->form = $this->_getContentSlotForm($request);
  }

  public function executeSave_slot(sfWebrequest $request)
  {
    $this->form = $this->_getContentSlotForm($request);

    $values = $request->getParameter($this->form->getName());
    $this->form->bind($values);
    if ($this->form->isValid())
    {
      $this->form->save();
    } else {
      exit('errors'.(string) $this->form);
      // handle errors?
    }

    $this->setLayout(false);
    $this->setTemplate('preview_slot');
  }

  public function executePreview_slot(sfWebRequest $request)
  {
    $this->setLayout(false);

    $this->contentSlot = $this->_getContentSlot($request);
    $this->contentSlot->setValue($request->getParameter('value'));
  }
}
