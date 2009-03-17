<?php
class sympal_frontendActions extends sfActions
{
  public function executeSecure()
  {
    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));
  }

  public function executeError404()
  {
    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));
  }
}