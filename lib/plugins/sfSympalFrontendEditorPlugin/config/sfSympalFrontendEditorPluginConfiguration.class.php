<?php

class sfSympalFrontendEditorPluginConfiguration extends sfPluginConfiguration
{
  private $_editorAssetsLoaded = false;

  public function initialize()
  {
    $this->dispatcher->connect('response.filter_content', array($this, 'addEditorHtml'));
    $this->dispatcher->connect('sympal.load_content', array($this, 'loadEditor'));
  }

  public function loadEditor(sfEvent $event)
  {
    if (sfContext::getInstance()->getUser()->isEditMode() && sfSympalContext::getInstance()->getCurrentContent())
    {
      $this->loadEditorAssets();
    }
  }

  public function loadEditorAssets()
  {
    if (!$this->_editorAssetsLoaded)
    {
      $this->configuration->loadHelpers('SympalContentSlotEditor');

      sfSympalToolkit::useJQuery(array('ui'));

      $response = sfContext::getInstance()->getResponse();

      // Load jquery tools/plugins that the inline editor requires
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/js/jQuery.cookie.js'));
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/js/jQuery.elastic.js'));
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/js/jquery.Jcrop.min.js'));

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

      $this->_editorAssetsLoaded = true;
    }
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