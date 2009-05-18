<?php

class sfWidgetFormSympalRichText extends sfWidgetFormSympalMultiLineText
{
  public function getStylesheets()
  {
    return array(
      '/sfSympalPlugin/yui/assets/skins/sam/skin.css'
    );
  }

  public function getJavascripts()
  {
    return array(
      '/sfSympalPlugin/yui/yahoo-dom-event/yahoo-dom-event.js',
      '/sfSympalPlugin/yui/element/element-min.js',
      '/sfSympalPlugin/yui/container/container_core-min.js',
      '/sfSympalPlugin/yui/menu/menu-min.js',
      '/sfSympalPlugin/yui/button/button-min.js',
      '/sfSympalPlugin/yui/editor/editor-min.js',
      '/sfSympalPlugin/js/yui-image-uploader26.js'
    );
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $textarea = parent::render($name, $value, $attributes, $errors);

    preg_match_all("/id=\"([^\"]+)\"/", $textarea, $matches);
    $id = $matches[1][0];

    $e = explode('_', $id);
    $contentSlotId = end($e);

    $url = sfContext::getInstance()->getController()->genUrl('sympal_yui_image_uploader');

    $js = sprintf(<<<EOF
<script type="text/javascript">
var myEditor = new YAHOO.widget.Editor('%s', {
    height: '300px',
    width: '600px',
    dompath: true,
    animate: true
});

function updatePreview(myEditor)
{
  myEditor.saveHTML();
  var html = myEditor.get('element').value;
  document.getElementById('edit_content_slot_button_%s').innerHTML = html;
}

myEditor.on('afterNodeChange', function() {
  updatePreview(myEditor);
}, myEditor, true);

myEditor.on('editorKeyUp', function() {
  updatePreview(myEditor);
}, myEditor, true);

yuiImgUploader(myEditor, '%s', '%s','image');
myEditor.render();

</script>
EOF
    ,
      $id,
      $contentSlotId,
      $id,
      $url
    );

    return '<div class="yui-skin-sam">'.$textarea.$js.'</div>';
  }
}