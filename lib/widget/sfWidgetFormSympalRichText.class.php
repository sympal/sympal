<?php
sfContext::getInstance()->getConfiguration()->loadHelpers(array('Asset'));

use_stylesheet('http://yui.yahooapis.com/2.7.0/build/assets/skins/sam/skin.css');
use_javascript('http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js');
use_javascript('http://yui.yahooapis.com/2.7.0/build/element/element-min.js');
use_javascript('http://yui.yahooapis.com/2.7.0/build/container/container_core-min.js');
use_javascript('http://yui.yahooapis.com/2.7.0/build/menu/menu-min.js');
use_javascript('http://yui.yahooapis.com/2.7.0/build/button/button-min.js');
use_javascript('http://yui.yahooapis.com/2.7.0/build/editor/editor-min.js');

class sfWidgetFormSympalRichText extends sfWidgetFormSympalMultiLineText
{
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $e = explode('_', $attributes['id']);
    $entitySlotId = end($e);

    $js = sprintf(<<<EOF
<script type="text/javascript">
var myEditor = new YAHOO.widget.Editor('%s', {
    height: '300px',
    width: '600px',
    dompath: true,
    animate: true
});
myEditor.render();

YAHOO.util.Event.on('preview_button', 'click', function() {
    myEditor.saveHTML();
    var html = myEditor.get('element').value;
    document.getElementById('edit_entity_slot_button_%s').innerHTML = html;
});

</script>
EOF
    ,
      $attributes['id'],
      $entitySlotId
    );

    $textarea = parent::render($name, $value, $attributes, $errors);

    return '<div class="yui-skin-sam">'.$textarea.$js.'</div>';
  }
}