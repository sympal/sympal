<?php

class sfSympalRenderingPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('sympal.content_renderer.filter_content', array($this, 'filterSympalContent'));
  }

  public function filterSympalContent(sfEvent $event, $content)
  {
    if (sfContext::getInstance()->getUser()->isEditMode())
    {
      $content .= '<div id="sympal_inline_edit_bar" class="sympal_form">';
      $content .= ' <a href="#edit" id="toggle_edit_mode">'.image_tag('/sf/sf_admin/images/edit.png').' Edit '.$event['content']['Type']['label'].'</a>';
      $content .= ' <input type="button" id="sympal_save_content_slots" name="save" value="Save" />';
      $content .= '</div>';
    }
    return $content;
  }
}