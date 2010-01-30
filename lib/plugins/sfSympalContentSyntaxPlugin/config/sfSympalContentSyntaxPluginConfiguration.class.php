<?php

class sfSympalContentSyntaxPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('sympal.content_renderer.filter_slot_content', array('sfSympalContentSlotReplacer', 'listenToFilterSlotContent'));
  }
}