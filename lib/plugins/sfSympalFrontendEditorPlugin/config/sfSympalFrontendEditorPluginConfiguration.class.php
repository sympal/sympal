<?php

class sfSympalFrontendEditorPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('context.load_factories', array($this, 'loadEditor'));
  }

  public function loadEditor(sfEvent $event)
  {
    if (sfContext::getInstance()->getUser()->isEditMode())
    {
      $this->dispatcher->connect('sympal.content_renderer.filter_content', array($this, 'addInlineEditBarHtml'));
      $this->dispatcher->connect('response.filter_content', array($this, 'addEditorHtml'));

      $this->configuration->loadHelpers('jQuery');
      $response = sfContext::getInstance()->getResponse();
      $response->addJavascript('/sfSympalPlugin/js/jQuery.cookie.js');
      $response->addJavascript('/sfSympalPlugin/js/jQuery.hoverIntent.js');
      $response->addJavascript('/sfSympalFrontendEditorPlugin/js/editor.js');
      $response->addStylesheet('/sfSympalFrontendEditorPlugin/css/editor.css');
    }
  }

  public function addInlineEditBarHtml(sfEvent $event, $content)
  {
    $inlineEditBar  = '<div class="sympal_inline_edit_bar sympal_form">';
    $inlineEditBar .= ' <a href="#edit" class="toggle_edit_mode">'.image_tag('/sf/sf_admin/images/edit.png').' Edit '.$event['content']['Type']['label'].'</a>';
    $inlineEditBar .= ' <input type="button" class="sympal_save_content_slots" name="save" value="Save" />';
    $inlineEditBar .= '</div>';
    return $inlineEditBar.$content.$inlineEditBar;
  }

  public function addEditorHtml(sfEvent $event, $content)
  {
    return str_replace('</body>', get_sympal_editor().'</body>', $content);
  }
}