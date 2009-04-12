<?php use_stylesheet('/sfSympalPlugin/css/editor') ?>

<?php use_sympal_yui_css('container/assets/skins/sam/container') ?>
<?php use_sympal_yui_css('assets/skins/sam/skin') ?>
<?php use_sympal_yui_css('resize/assets/skins/sam/resize') ?>

<?php use_sympal_yui_js('yahoo-dom-event/yahoo-dom-event') ?>

<?php use_sympal_yui_js('dragdrop/dragdrop') ?>
<?php use_sympal_yui_js('container/container') ?>
<?php use_sympal_yui_js('connection/connection') ?>

<?php use_sympal_yui_js('yahoo-dom-event/yahoo-dom-event') ?>
<?php use_sympal_yui_js('element/element') ?>
<?php use_sympal_yui_js('container/container_core') ?>
<?php use_sympal_yui_js('menu/menu') ?>
<?php use_sympal_yui_js('button/button') ?>
<?php use_sympal_yui_js('editor/editor') ?>

<?php use_sympal_yui_js('utilities/utilities') ?>
<?php use_sympal_yui_js('resize/resize') ?>
<?php use_sympal_yui_js('animation/animation') ?>

<?php use_javascript('/sfSympalPlugin/js/yui-image-uploader26.js') ?>
<?php use_javascript('/sfSympalPlugin/js/bubbling/dispatcher/dispatcher-min') ?>

<?php $state = $sf_user->getAttribute('editor_tools_state', 'visible', 'sympal') ?>
<div class="yui-skin-sam" id="sympal_edit_panel_container">
  <div id="sympal_edit_panel">
    <div class="hd" style="height: 25px;">
      <span id="title"><?php echo __('Sympal Editor Panel') ?></span>
      <span id="toggle"><?php if ($state == 'hidden'): ?><?php echo __('Show') ?><?php else: ?><?php echo __('Hide') ?><?php endif; ?></span>
    </div>
    <div class="bd" id="sympal_edit_panel_contents">
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
  document.getElementById('loading').style.display = 'visible';
  document.getElementById('loading').innerHTML = 'Loading...';

	var url = "<?php echo url_for('@sympal_save_content_slot?id=ID', 'absolute=true') ?>";
	url = url.replace('ID', id);

	var callback = {
		success: function(o) {
			  document.getElementById('edit_content_slot_button_' + id).innerHTML = o.responseText;
			  unhighlight_sympal_content_slot(id);
			  edit_sympal_content_slot(id);
			  document.getElementById('loading').style.display = 'none';
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

overlay = new YAHOO.widget.Overlay("sympal_edit_panel_contents", { visible:true,
    						  zIndex:1000,
    						  width:"330px" } );

overlay.render();
<?php if ($state == 'hidden'): ?>
overlay.hide();
<?php endif; ?>

function overlayToggle()
{
  var toggle = document.getElementById('toggle');
  var current = toggle.innerHTML;

  if (current == '<?php echo __('Hide') ?>')
  {
    overlay.hide();
    toggle.innerHTML = '<?php echo __('Show') ?>';

    var url = '<?php echo url_for('@sympal_tools_save_state?state=hidden') ?>';
  } else {
    overlay.show();
    toggle.innerHTML = '<?php echo __('Hide') ?>';

    var url = '<?php echo url_for('@sympal_tools_save_state?state=visible') ?>';
  }

	YAHOO.util.Connect.asyncRequest('GET', url);
}

YAHOO.util.Event.addListener("toggle", "click", overlayToggle, overlay, true);

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