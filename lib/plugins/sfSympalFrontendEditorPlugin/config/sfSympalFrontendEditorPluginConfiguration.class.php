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

      $this->configuration->loadHelpers('jQuery', 'SympalContentSlotEditor');

      $response = sfContext::getInstance()->getResponse();

      // Load jquery tools/plugins that the inline editor requires
      $response->addJavascript('/sfSympalPlugin/js/jQuery.cookie.js');
      $response->addJavascript('/sfSympalPlugin/js/jQuery.hoverIntent.js');
      $response->addJavascript('/sfSympalPlugin/js/jQuery.elastic.js');

      // Load markitup markdown editor
      $response->addJavascript('/sfSympalPlugin/markitup/jquery.markitup.js');
      $response->addJavascript('/sfSympalPlugin/markitup/sets/markdown/set.js');
      $response->addStylesheet('/sfSympalPlugin/markitup/skins/markitup/style.css');
      $response->addStylesheet('/sfSympalPlugin/markitup/sets/markdown/style.css');

      // Load tinymce
      $response->addJavascript('/sfSympalPlugin/tiny_mce/tiny_mce.js');

      // Load the sympal editor js and css
      $response->addJavascript('/sfSympalFrontendEditorPlugin/js/editor.js');
      $response->addStylesheet('/sfSympalFrontendEditorPlugin/css/editor.css');
    }
  }

  public function addInlineEditBarHtml(sfEvent $event, $content)
  {
    $inlineEditBar  = '<div class="sympal_inline_edit_bar sympal_form">';
    $inlineEditBar .= ' <a href="#edit" class="toggle_edit_mode">'.image_tag('/sf/sf_admin/images/edit.png').' Edit '.$event['content']['Type']['label'].'</a>';
    $inlineEditBar .= ' <div class="sympal_inline_edit_bar_buttons">';
    $inlineEditBar .= '   <input type="button" class="sympal_save_content_slots" name="save" value="Save" />';
    $inlineEditBar .= '   <input type="button" class="sympal_preview_content_slots" name="preview" value="Preview" />';
    $inlineEditBar .= ' </div>';
    if (sfSympalConfig::isI18nEnabled())
    {
      $inlineEditBar .= '<div class="sympal_inline_edit_bar_change_language">';
      $user = sfContext::getInstance()->getUser();
      $form = new sfFormLanguage($user, array('languages' => sfSympalConfig::get('language_codes', null, array($user->getCulture()))));
      unset($form[$form->getCSRFFieldName()]);
      $widgetSchema = $form->getWidgetSchema();
      $widgetSchema['language']->setAttribute('onChange', "this.form.submit();");

      $inlineEditBar .= $form->renderFormTag(url_for('@sympal_change_language_form'));
      $inlineEditBar .= $form['language'];
      $inlineEditBar .= '</form>';
      $inlineEditBar .= '</div>';
    }
    $inlineEditBar .= '</div>';
    return $inlineEditBar.$content.$inlineEditBar;
  }

  public function addEditorHtml(sfEvent $event, $content)
  {
    return str_replace('</body>', get_sympal_editor().'</body>', $content);
  }
}