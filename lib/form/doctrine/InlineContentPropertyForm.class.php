<?php

class InlineContentPropertyForm extends PluginContentForm
{
  public function setup()
  {
    $this->contentSlot = $this->getOption('contentSlot');
    parent::setup();
  }
}