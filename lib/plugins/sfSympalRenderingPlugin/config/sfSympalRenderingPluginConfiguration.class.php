<?php

class sfSympalRenderingPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('sympal.content_renderer.filter_content', array($this, 'filterSympalContent'));
  }

  public function filterSympalContent(sfEvent $event, $content)
  {
    $inlineEditBar = '';
    if (sfContext::getInstance()->getUser()->isEditMode())
    {
      $inlineEditBar  = '<div class="sympal_inline_edit_bar sympal_form">';
      $inlineEditBar .= ' <a href="#edit" class="toggle_edit_mode">'.image_tag('/sf/sf_admin/images/edit.png').' Edit '.$event['content']['Type']['label'].'</a>';
      $inlineEditBar .= ' <input type="button" class="sympal_save_content_slots" name="save" value="Save" />';
      $inlineEditBar .= '</div>';
    }
    return $inlineEditBar.$content.$inlineEditBar;
  }
}