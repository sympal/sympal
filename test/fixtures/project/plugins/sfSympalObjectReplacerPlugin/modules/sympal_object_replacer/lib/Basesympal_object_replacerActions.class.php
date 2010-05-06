<?php

class Basesympal_object_replacerActions extends sfActions
{  
  /**
   * Ajax action that populates the object choose on the editor toolbar
   */
  public function executeObjectBrowser(sfWebRequest $request)
  {
    $this->keys = array_keys(sfSympalConfig::get('object_browser_classes', null, array()));
    
    if (count($this->keys))
    {
      if (!$object_slug = $request->getParameter('key'))
      {
        $this->selectedKey = $this->keys[0];
      } else {
        $this->selectedKey = $object_slug;
      }
      
      $classConfig = sfSympalConfig::get('object_browser_classes', $this->selectedKey);
      $this->selectedLabel = isset($classConfig['label']) ? $classConfig['label'] : $this->selectedKey;
      
      if (!isset($classConfig['class']))
      {
        // I die so that this can be seen, this is an ajax response
        die(sprintf('Cannot find "class" key object_browser_classes "%s".', $this->selectedKey));
      }
      
      $tbl = Doctrine_Core::getTable($classConfig['class']);
      
      if (method_exists($tbl, 'fetchForObjectBrowser'))
      {
        $this->objects = $tbl->fetchForObjectBrowser();
      }
      else
      {
        $this->objects =  $tbl->createQuery()->execute();
      }
    }
    else
    {
      $this->selectedKey = false;
    }
  }
}