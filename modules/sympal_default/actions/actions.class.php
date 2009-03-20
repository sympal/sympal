<?php
class sympal_defaultActions extends sfActions
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