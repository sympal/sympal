<?php

class sfSympalFrontendEditorPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('sympal.content_renderer.filter_content', array($this, 'loadEditor'));
  }

  public function loadEditor(sfEvent $event, $content)
  {
    if (sfContext::getInstance()->getUser()->isEditMode())
    {
      $this->dispatcher->connect('response.filter_content', array($this, 'addEditorHtml'));

      $this->configuration->loadHelpers('SympalContentSlotEditor');

      sfSympalToolkit::useJQuery(array('ui'));

      $response = sfContext::getInstance()->getResponse();

      // Load jquery tools/plugins that the inline editor requires
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/js/jQuery.cookie.js'));
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/js/jQuery.elastic.js'));

      // Load markitup markdown editor
      if (sfSympalConfig::get('enable_markdown_editor'))
      {
        $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/markitup/jquery.markitup.js'));
        $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/markitup/sets/markdown/set.js'));
        $response->addStylesheet(sfSympalConfig::getAssetPath('/sfSympalPlugin/markitup/skins/markitup/style.css'));
        $response->addStylesheet(sfSympalConfig::getAssetPath('/sfSympalPlugin/markitup/sets/markdown/style.css'));
      }

      // Load tinymce
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/tiny_mce/tiny_mce.js'));

      // Load the sympal editor js and css
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalFrontendEditorPlugin/js/editor.js'));
      $response->addStylesheet(sfSympalConfig::getAssetPath('/sfSympalFrontendEditorPlugin/css/editor.css'));
    }
    return $content;
  }

  public function addEditorHtml(sfEvent $event, $content)
  {
    if ($contentRecord = sfSympalContext::getInstance()->getCurrentContent())
    {
      return str_replace('</body>', get_sympal_editor().'</body>', $content);
    } else {
      return $content;
    }
  }
}