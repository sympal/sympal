<?php

class sfSympalAdminContentSlotForm extends PluginsfSympalContentSlotForm
{
  public function configure()
  {
    parent::configure();
    unset($this['value']);

    sfSympalFormToolkit::changeContentWidget($this, 'content_list');
  }
}