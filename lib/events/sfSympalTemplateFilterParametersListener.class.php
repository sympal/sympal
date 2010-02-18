<?php

class sfSympalTemplateFilterParametersListener extends sfSympalListener
{
  public function getEventName()
  {
    return 'template.filter_parameters';
  }

  public function run(sfEvent $event, $parameters)
  {
    if (!$sympalContext = $this->_invoker->getSympalContext())
    {
      return $parameters;
    }
    $parameters['sf_sympal_context'] = $sympalContext;
    if ($content = $sympalContext->getCurrentContent())
    {
      $parameters['sf_sympal_content'] = $content;
    }
    if ($menuItem = $sympalContext->getCurrentMenuItem())
    {
      $parameters['sf_sympal_menu_item'] = $menuItem;
    }
    return $parameters;
  }
}