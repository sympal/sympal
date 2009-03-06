<?php
class sympal_frontendActions extends sfActions
{
  public function preExecute()
  {
    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));
  }

  public function executeIndex()
  {
    sfSympalConfig::set('use_query_caching', true);
    sfSympalConfig::set('use_result_caching', true);

    $sympalContext = sfSympalContext::createInstance('sympal', $this->getContext());
    $this->renderer = $sympalContext->quickRenderEntity('home');
  }

  public function executeSecure()
  {
    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));
  }

  public function executeError404()
  {
    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));
  }
}