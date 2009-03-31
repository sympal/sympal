<?php use_stylesheet('/sfSympalPlugin/css/editor') ?>

<?php use_stylesheet('/sfSympalPlugin/yui/container/assets/skins/sam/container.css') ?>
<?php use_stylesheet('/sfSympalPlugin/yui/assets/skins/sam/skin.css') ?>
<?php use_stylesheet('/sfSympalPlugin/yui/resize/assets/skins/sam/resize.css') ?>

<?php use_javascript('/sfSympalPlugin/yui/yahoo-dom-event/yahoo-dom-event.js') ?>

<?php use_javascript('/sfSympalPlugin/yui/dragdrop/dragdrop-min.js') ?>
<?php use_javascript('/sfSympalPlugin/yui/container/container-min.js') ?>
<?php use_javascript('/sfSympalPlugin/yui/connection/connection-min.js') ?>


<?php use_javascript('/sfSympalPlugin/yui/yahoo-dom-event/yahoo-dom-event.js') ?>
<?php use_javascript('/sfSympalPlugin/yui/element/element-min.js') ?>
<?php use_javascript('/sfSympalPlugin/yui/container/container_core-min.js') ?>
<?php use_javascript('/sfSympalPlugin/yui/menu/menu-min.js') ?>
<?php use_javascript('/sfSympalPlugin/yui/button/button-min.js') ?>
<?php use_javascript('/sfSympalPlugin/yui/editor/editor-min.js') ?>

<?php use_javascript('/sfSympalPlugin/yui/utilities/utilities.js') ?>
<?php use_javascript('/sfSympalPlugin/yui/resize/resize.js') ?>

<?php use_javascript('/sfSympalPlugin/js/bubbling/dispatcher/dispatcher-min') ?>

<?php use_javascript('/sfSympalPlugin/yui/animation/animation-min.js') ?>
<?php use_javascript('/sfSympalPlugin/js/yui-image-uploader26.js') ?>

<div class="yui-skin-sam" id="sympal_edit_panel_container">
  <div id="sympal_edit_panel">
    <div class="hd">Sympal Editor Panel</div>
    <div class="bd">
      <?php echo $menu ?>
    </div>
  </div>
</div>

<script type="text/javascript">
var changed = false;
function edit_on_key_up(e)
{
  changed = true;
}

function highlight_sympal_content_slot(id)
{
  document.getElementById('edit_content_slot_button_' + id).style.background = '#ffc';
  document.getElementById('edit_content_slot_button_' + id).style.border = '1px solid #ddd';
}

function unhighlight_sympal_content_slot(id)
{
  document.getElementById('edit_content_slot_button_' + id).style.background = 'none';
  document.getElementById('edit_content_slot_button_' + id).style.border = '1px solid transparent';
}

var interval;
function edit_sympal_content_slot(id)
{
  var hiddenName = document.getElementById('content_slot_'+id+'_is_column');
  if (hiddenName)
  {
    var name = hiddenName.value
    var url = "<?php echo url_for('@sympal_edit_column_content_slot?id=ID&name=NAME', 'absolute=true') ?>";
    url = url.replace('NAME', name);
  } else {
  	var url = "<?php echo url_for('@sympal_edit_content_slot?id=ID', 'absolute=true') ?>";
  }

  interval = setInterval(function() { preview_sympal_content_slot(id) }, 500);

  url = url.replace('ID', id);
  YAHOO.plugin.Dispatcher.fetch('edit_content_slot_content_' + id, url);    
}

function preview_sympal_content_slot(id)
{
  if (!changed)
  {
    return;
  }

  changed = false;
  clearInterval(interval);

	var url = "<?php echo url_for('@sympal_preview_content_slot?id=ID', 'absolute=true') ?>";
	url = url.replace('ID', id);

	var callback = {
		success: function(o) {
			  document.getElementById('edit_content_slot_button_' + id).innerHTML = o.responseText;
			}
		} 

	YAHOO.util.Connect.asyncRequest('POST', url, callback, 'value=' + escape(document.getElementById('content_slot_value_' + id).value));
}

function save_sympal_content_slot(id)
{
  highlight_sympal_content_slot(id);

	var url = "<?php echo url_for('@sympal_save_content_slot?id=ID', 'absolute=true') ?>";
	url = url.replace('ID', id);

	var callback = {
		success: function(o) {
			  document.getElementById('edit_content_slot_button_' + id).innerHTML = o.responseText;
			  unhighlight_sympal_content_slot(id);
			}
		} 

  var formObject = document.getElementById('edit_content_slot_form_' + id);
  YAHOO.util.Connect.setForm(formObject);
  var cObj = YAHOO.util.Connect.asyncRequest('POST', url, callback);
}

function change_content_slot_type(id, slotTypeId)
{
  var hiddenName = document.getElementById('content_slot_'+id+'_is_column');
  if (hiddenName)
  {
    var name = hiddenName.value
    var url = "<?php echo url_for('@sympal_change_column_content_slot_type?name=NAME&id=ID&type=TYPE', 'absolute=true') ?>";
    url = url.replace('NAME', name);
  } else {
	  var url = "<?php echo url_for('@sympal_change_content_slot_type?id=ID&type=TYPE', 'absolute=true') ?>";
  }
	
	url = url.replace('ID', id);
  url = url.replace('TYPE', slotTypeId);

  YAHOO.plugin.Dispatcher.fetch('edit_content_slot_content_' + id, url);
}

myPanel = new YAHOO.widget.Panel('sympal_edit_panel', {
	underlay:"shadow",
	width:"350px",
	x:<?php echo $sf_user->getAttribute('sympal_editor_panel_x', 50) ?>,
	y:<?php echo $sf_user->getAttribute('sympal_editor_panel_y', 50) ?>,
	close:false,
	visible:true,
	draggable:true} );

myPanel.render();
myPanel.show();

myPanel.dd.endDrag = function(e, id) {
	var x = 0;
	var y = 0;

	if (e.pageX || e.pageY)
	{
		x = e.pageX;
		y = e.pageY;
	} else if (e.clientX || e.clientY) 	{
		x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
		y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
	}

	var url = "<?php echo url_for('@sympal_save_editor_panel_position?x=##X##&y=##Y##', 'absolute=true') ?>";
	
	url = url.replace('##X##', x);
	url = url.replace('##Y##', y);

	YAHOO.util.Connect.asyncRequest('GET', url);
}
</script>