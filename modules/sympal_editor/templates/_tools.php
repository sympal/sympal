<?php use_stylesheet('/sfSympalPlugin/css/editor') ?>

<?php use_stylesheet('http://yui.yahooapis.com/2.7.0/build/container/assets/skins/sam/container.css') ?>

<?php use_javascript('http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js') ?>

<?php use_javascript('http://yui.yahooapis.com/2.7.0/build/dragdrop/dragdrop-min.js') ?>
<?php use_javascript('http://yui.yahooapis.com/2.7.0/build/container/container-min.js') ?>
<?php use_javascript('http://yui.yahooapis.com/2.2.2/build/connection/connection-min.js') ?>

<?php use_stylesheet('http://yui.yahooapis.com/2.7.0/build/assets/skins/sam/skin.css') ?>
<?php use_javascript('http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js') ?>
<?php use_javascript('http://yui.yahooapis.com/2.7.0/build/element/element-min.js') ?>
<?php use_javascript('http://yui.yahooapis.com/2.7.0/build/container/container_core-min.js') ?>
<?php use_javascript('http://yui.yahooapis.com/2.7.0/build/menu/menu-min.js') ?>
<?php use_javascript('http://yui.yahooapis.com/2.7.0/build/button/button-min.js') ?>
<?php use_javascript('http://yui.yahooapis.com/2.7.0/build/editor/editor-min.js') ?>

<?php use_stylesheet('http://yui.yahooapis.com/2.7.0/build/resize/assets/skins/sam/resize.css') ?>
<?php use_javascript('http://yui.yahooapis.com/2.7.0/utilities/utilities.js') ?>
<?php use_javascript('http://yui.yahooapis.com/2.7.0/build/resize/resize.js') ?>

<?php use_javascript('/sfSympalPlugin/js/bubbling/dispatcher/dispatcher-min') ?>

<?php use_javascript('http://yui.yahooapis.com/2.7.0/build/animation/animation-min.js') ?>

<div class="yui-skin-sam" id="sympal_edit_panel_container">
  <div id="sympal_edit_panel">
    <div class="hd">Sympal Editor Panel</div>
    <div class="bd">
      <div class="sympal_icon_list">
        <h3><?php echo $content['Type']['name'] ?> Editor</h3>

        <ul>
          <?php if ($content['locked_by']): ?>
            <?php if ($content['locked_by'] == $sf_user->getGuardUser()->getId()): ?>
              <li><?php echo image_tag('/sfSympalPlugin/images/lock.gif').' '.link_to('Un-Lock '.$content['Type']['name'], '@sympal_unlock_content?id='.$content['id']) ?></li>

              <?php if ($sf_request->getParameter('module') == 'sympal_content'): ?>
                <li><?php echo image_tag('/sf/sf_admin/images/edit.png').' '.link_to('Edit '.$content['Type']['name'].' Inline', $content->getRoute()) ?></li>
              <?php else: ?>
                <li><?php echo image_tag('/sf/sf_admin/images/edit.png').' '.link_to('Edit '.$content['Type']['name'].' Backend', '@sympal_content_edit?id='.$content['id']) ?></li>
              <?php endif; ?>
            <?php else: ?>
              <li><?php echo $content['Type']['name'] ?> is currently locked by "<?php echo $content['LockedBy']['username'] ?>" and cannot be edited.</li>
            <?php endif; ?>
          <?php elseif (!$lock): ?>
            <li><?php echo image_tag('/sfSympalPlugin/images/lock.gif').' '.link_to('Obtain Edit Lock', '@sympal_lock_content?id='.$content['id']) ?></li>
          <?php elseif ($lock): ?>
            <li>You still have an edit lock open on a <strong><?php echo $lock['Type']['name'] ?></strong> titled <strong><?php echo $lock->getHeaderTitle() ?></strong>.</li>
            <li><?php echo image_tag('/sf/sf_admin/images/edit.png').' '.link_to('Un-Lock '.$lock['Type']['name'], '@sympal_unlock_content?id='.$lock['id']) ?></li>
            <li><?php echo image_tag('/sf/sf_admin/images/edit.png').' '.link_to('Go Back to '.$lock->getHeaderTitle(), $lock->getRoute()) ?></li>
          <?php endif; ?>

          <?php if ($content['is_published']): ?>
            <li>
              <?php echo image_tag('/sf/sf_admin/images/cancel.png').' '.link_to('Un-Publish', '@sympal_unpublish_content?id='.$content['id']) ?>
              <small>(Published on <strong><?php echo date('m/d/Y h:i:s', strtotime($content['date_published'])) ?></strong>)</small>
            </li>
          <?php else: ?>
            <li><?php echo image_tag('/sf/sf_admin/images/tick.png').' '.link_to('Publish', '@sympal_publish_content?id='.$content['id']) ?></li>
          <?php endif; ?>
          <li><?php echo image_tag('/sf/sf_admin/images/delete.png').' '.link_to('Delete', '@sympal_content_delete?id='.$menuItem['id'], 'confirm=Are you sure you wish to delete this content?') ?></li>
        </ul>

        <?php if ($menuItem && $menuItem->exists()): ?>
          <h3>Menu Editor</h3>
          <ul>
            <li><?php echo image_tag('/sf/sf_admin/images/edit.png').' '.link_to('Edit Menu Item', '@sympal_menu_items_edit?id='.$menuItem['id']) ?></li>
            <li><?php echo image_tag('/sf/sf_admin/images/add.png').' '.link_to('Add Child Menu Item', 'sympal_menu_items/ListNew?id='.$menuItem['id']) ?></li>
            <li><?php echo image_tag('/sf/sf_admin/images/delete.png').' '.link_to('Delete', '@sympal_menu_items_delete?id='.$menuItem['id'], 'confirm=Are you sure you wish to delete this menu item?') ?></li>
          </ul>
        <?php endif; ?>

        <h3><?php echo $content['Type']['label'] ?> Content</h3>

        <ul>
          <li><?php echo image_tag('/sf/sf_admin/images/add.png').' '.link_to('Create', '@sympal_content_create_type?type='.$content['Type']['slug']) ?></li>
          <li><?php echo image_tag('/sf/sf_admin/images/list.png').' '.link_to('List', '@sympal_content_type_'.$content['Type']['slug']) ?></li>
        </ul>
      </div>

      <?php if (sfSympalConfig::isI18nEnabled()): ?>
        <h3>Change Language</h3>
        <?php echo get_component('sympal_editor', 'language') ?>
      <?php endif; ?>
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
	var url = "<?php echo url_for('@sympal_edit_content_slot?id=##REPLACE##', 'absolute=true') ?>";
	url = url.replace('##REPLACE##', id);

  interval = setInterval(function() { preview_sympal_content_slot(id) }, 500);

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

	var url = "<?php echo url_for('@sympal_preview_content_slot?id=##REPLACE##', 'absolute=true') ?>";
	url = url.replace('##REPLACE##', id);

	var callback = {
		success: function(o) {
			document.getElementById('edit_content_slot_button_' + id).innerHTML = o.responseText;
			}
		} 

	YAHOO.util.Connect.asyncRequest('POST', url, callback, 'value=' + escape(document.getElementById('content_slot_value_' + id).value));
}

function save_sympal_content_slot(id)
{
	var url = "<?php echo url_for('@sympal_save_content_slot?id=888', 'absolute=true') ?>";
	url = url.replace('888', id);

  highlight_sympal_content_slot(id);

	var callback = {
		success: function(o) {
			document.getElementById('edit_content_slot_button_' + id).innerHTML = o.responseText;
			unhighlight_sympal_content_slot(id);
			}
		} 

	YAHOO.util.Connect.asyncRequest('POST', url, callback, 'value=' + escape(document.getElementById('content_slot_value_' + id).value));
}

function change_content_slot_type(id, slotTypeId)
{
	var url = "<?php echo url_for('@sympal_change_content_slot_type?id=888&type=999', 'absolute=true') ?>";
	url = url.replace('888', id);
  url = url.replace('999', slotTypeId);

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