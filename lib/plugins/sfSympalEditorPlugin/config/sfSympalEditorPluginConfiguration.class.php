<?php

class sfSympalEditorPluginConfiguration extends sfPluginConfiguration
{
  private $_editorAssetsLoaded = false;

  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_content', array($this, 'loadEditor'));
    $this->dispatcher->connect('sympal.load_inline_edit_bar_buttons', array($this, 'loadInlineEditBarButtons'));
  }

  public function loadInlineEditBarButtons(sfEvent $event)
  {
    $menu = $event->getSubject();
    
    $menu->addChild('Save')->
      isEditModeButton(true)->
      setShortcut('Ctrl+S')->
      setInputClass('sympal_save_content_slots')
    ;

    $menu->
      addChild('Preview')->
      isEditModeButton(true)->
      setShortcut('Ctrl+Shift+S')->
      setInputClass('sympal_preview_content_slots')
    ;

    $menu->
      addChild('Enable Edit Mode')->
      setShortcut('Ctrl+E')->
      setInputClass('toggle_edit_mode')
    ;

    $menu->
      addChild('Disable Edit Mode')->
      isEditModeButton(true)->
      setShortcut('Ctrl+Shift+X')->
      setInputClass('sympal_disable_edit_mode')
    ;
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

      // Load tinymce
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/tiny_mce/tiny_mce.js'));

      // Load the sympal editor js and css
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalEditorPlugin/js/editor.js'));
      $response->addStylesheet(sfSympalConfig::getAssetPath('/sfSympalEditorPlugin/css/editor.css'));
      
      // Load the js and css for the slot editing
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalEditorPlugin/js/slots.js'));
      $response->addStylesheet(sfSympalConfig::getAssetPath('/sfSympalEditorPlugin/css/slots.css'));

      // Fancybox
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/fancybox/jquery.fancybox.js'));
      $response->addStylesheet(sfSympalConfig::getAssetPath('/sfSympalPlugin/fancybox/jquery.fancybox.css'));
      
      // Ajax form submission
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/js/jQuery.form.js'));
      
      // Shortcuts
      $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/js/shortcuts.js'));

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