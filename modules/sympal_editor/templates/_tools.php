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
      <?php if (sfSympalConfig::isI18nEnabled()): ?>
        <h3>Change Language</h3>
        <?php echo get_component('sympal_editor', 'language') ?>
        <hr />
      <?php endif; ?>

      <div class="sympal_icon_list">
        <h3>Editor Navigation</h3>

        <ul>
          <?php if ($entity['locked_by']): ?>
            <?php if ($entity['locked_by'] == $sf_user->getGuardUser()->getId()): ?>
              <li><?php echo image_tag('/sfSympalPlugin/images/lock.gif').' '.link_to('Un-Lock to Finish', '@sympal_unlock_entity?id='.$entity['id']) ?></li>
              <?php if ($sf_request->getParameter('module') == 'sympal_entities'): ?>
                <li><?php echo image_tag('/sf/sf_admin/images/edit.png').' '.link_to('Edit Entity Inline', $entity->getRoute()) ?></li>
              <?php else: ?>
                <li><?php echo image_tag('/sf/sf_admin/images/edit.png').' '.link_to('Edit Entity Backend', '@sympal_entities_edit?id='.$entity['id']) ?></li>
              <?php endif; ?>
              <li><?php echo image_tag('/sf/sf_admin/images/edit.png').' '.link_to('Edit Menu Item', '@sympal_menu_items_edit?id='.$menuItem['id']) ?></li>
            <?php else: ?>
              <li>Entity is currently locked by "<?php echo $entity['LockedBy']['username'] ?>" and cannot be edited.</li>
            <?php endif; ?>
          <?php elseif (!count($locks)): ?>
            <li><?php echo image_tag('/sfSympalPlugin/images/lock.gif').' '.link_to('Obtain Edit Lock', '@sympal_lock_entity?id='.$entity['id']) ?></li>
          <?php elseif (isset($locks[0])): ?>
            <li>You still have an edit lock open on "<strong><?php echo $locks[0]->getHeaderTitle() ?></strong>".</li>
            <li><?php echo image_tag('/sf/sf_admin/images/edit.png').' '.link_to('Edit '.$locks[0]->getHeaderTitle(), $locks[0]->getRoute()) ?></li>
          <?php endif; ?>

          <?php if ($entity->getTemplate()): ?>
            <li><?php echo image_tag('/sf/sf_admin/images/edit.png').' '.link_to('Edit Entity Template', '@sympal_entity_templates_edit?id='.$entity->getTemplate()->getId()) ?></li>
          <?php endif; ?>

          <?php if ($entity['is_published']): ?>
            <li>
              <?php echo image_tag('/sf/sf_admin/images/cancel.png').' '.link_to('Un-Publish', '@sympal_unpublish_entity?id='.$entity['id']) ?>
              <small>(Published on <strong><?php echo date('m/d/Y h:i:s', strtotime($entity['date_published'])) ?></strong>)</small>
            </li>
          <?php else: ?>
            <li><?php echo image_tag('/sf/sf_admin/images/tick.png').' '.link_to('Publish', '@sympal_publish_entity?id='.$entity['id']) ?></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
var changed = false;
function edit_on_key_up(e)
{
  changed = true;
}

function highlight_entity_slot(id)
{
  document.getElementById('edit_entity_slot_button_' + id).style.background = '#ffc';
  document.getElementById('edit_entity_slot_button_' + id).style.border = '1px solid #ddd';
}

function unhighlight_entity_slot(id)
{
  document.getElementById('edit_entity_slot_button_' + id).style.background = 'none';
  document.getElementById('edit_entity_slot_button_' + id).style.border = '1px solid transparent';
}

var interval;
function edit_entity_slot(id)
{
	var url = "<?php echo url_for('@sympal_edit_entity_slot?id=##REPLACE##', 'absolute=true') ?>";
	url = url.replace('##REPLACE##', id);

  interval = setInterval(function() { preview_entity_slot(id) }, 500);

  YAHOO.plugin.Dispatcher.fetch('edit_entity_slot_content_' + id, url);
}

function preview_entity_slot(id)
{
  if (!changed)
  {
    return;
  }

  changed = false;
  clearInterval(interval);

	var url = "<?php echo url_for('@sympal_preview_entity_slot?id=##REPLACE##', 'absolute=true') ?>";
	url = url.replace('##REPLACE##', id);

	var callback = {
		success: function(o) {
			document.getElementById('edit_entity_slot_button_' + id).innerHTML = o.responseText;
			}
		} 

	YAHOO.util.Connect.asyncRequest('POST', url, callback, 'value=' + escape(document.getElementById('entity_slot_value_' + id).value));
}

function save_entity_slot(id)
{
	var url = "<?php echo url_for('@sympal_save_entity_slot?id=888', 'absolute=true') ?>";
	url = url.replace('888', id);

  highlight_entity_slot(id);

	var callback = {
		success: function(o) {
			document.getElementById('edit_entity_slot_button_' + id).innerHTML = o.responseText;
			unhighlight_entity_slot(id);
			}
		} 

	YAHOO.util.Connect.asyncRequest('POST', url, callback, 'value=' + escape(document.getElementById('entity_slot_value_' + id).value));
}

function change_entity_slot_type(id, slotTypeId)
{
	var url = "<?php echo url_for('@sympal_change_entity_slot_type?id=888&type=999', 'absolute=true') ?>";
	url = url.replace('888', id);
  url = url.replace('999', slotTypeId);

  YAHOO.plugin.Dispatcher.fetch('edit_entity_slot_content_' + id, url);
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