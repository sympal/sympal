<?php

class sfSympalEditorPluginConfiguration extends sfPluginConfiguration
{
  private $_editorAssetsLoaded = false;

  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_content', array($this, 'loadEditor'));
  }

  public function shouldLoadEditor()
  {
    $format = sfContext::getInstance()->getRequest()->getRequestFormat();
    $format = $format ? $format : 'html';

    return sfContext::getInstance()->getUser()->isEditMode() 
      && $format == 'html' && (!sfSympalConfig::get('page_cache', 'enabled')
      && sfSympalConfig::get('inline_editing', 'enabled')
      && sfSympalContext::getInstance()->getCurrentContent())
      || (sfSympalConfiguration::getActive()->isAdminModule() && sfSympalContext::getInstance()->getCurrentContent());
  }

  public function loadEditor(sfEvent $event)
  {
    if ($this->shouldLoadEditor())
    {
      // Load the editor assets (css/js)
      $this->loadEditorAssets();

      // Use the response.filter_content event to add the editor html
      $this->dispatcher->connect('response.filter_content', array($this, 'addEditorHtml'));
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
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalEditorPlugin/js/editor.js'));
      $response->addStylesheet(sfSympalConfig::getAssetPath('/sfSympalEditorPlugin/css/editor.css'));

      // Fancybox
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/fancybox/jquery.fancybox.js'));
      $response->addStylesheet(sfSympalConfig::getAssetPath('/sfSympalPlugin/fancybox/jquery.fancybox.css'));

      $this->_editorAssetsLoaded = true;
    }
  }

  public function addEditorHtml(sfEvent $event, $content)
  {
    $statusCode = $event->getSubject()->getStatusCode();
    
    if ($statusCode == 404 || $statusCode == 500)
    {
      return $content;
    }
    
    return str_replace('</body>', get_sympal_editor().'</body>', $content);
  }
}