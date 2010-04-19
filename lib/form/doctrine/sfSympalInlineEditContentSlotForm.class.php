<?php

class sfSympalInlineEditContentSlotForm extends sfSympalContentSlotForm
{
  public function setup()
  {
    parent::setup();
    
    $this->widgetSchema->setNameFormat('sf_sympal_content_slot_'.$this->getObject()->id.'[%s]');
  }
}
