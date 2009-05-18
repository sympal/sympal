<script type="text/javascript">
function getElement(id)
{
  var el;

  if ("string" == typeof id) {
      el = YAHOO.util.DDM.getElement(id);
  } else {
      el = YAHOO.util.DDM.getBestMatch(id).getEl();
  }
  return el;
}

function showLoading()
{
  var el = getElement('loading');
  el.innerHTML = 'Loading...';
}

function hideLoading()
{
  var el = getElement('loading');
  el.innerHTML = '';
}

function executeAjaxUrl(url)
{
  showLoading();
  YAHOO.plugin.Dispatcher.fetch('sympal_menu_manager_tree_holder', url);
}

(function() {
	var tree;

	function treeInit()
	{
		tree = new YAHOO.widget.TreeView("sympal_menu_manager_tree");
		tree.draw();
		tree.expandAll();

    <?php echo sfSympalMenuManager::getDragDrops(); ?>

		YAHOO.util.Event.on("expand", "click", function(e) {
			tree.expandAll();
			YAHOO.util.Event.preventDefault(e);
		});

		YAHOO.util.Event.on("collapse", "click", function(e) {
			tree.collapseAll();
			YAHOO.util.Event.preventDefault(e);
		});
	}

	YAHOO.util.Event.onDOMReady(treeInit);
})();

YAHOO.util.Event.onAvailable("sympal_menu_manager_tree", function ()
{
  var currentMenuItemId = null;
  var oContextMenu = null;

  function addNode()
  {
    var sLabel = window.prompt("Enter a label for the new child: ", ""), oChildNode;

    if (sLabel && sLabel.length > 0)
    {
      var url = '<?php echo url_for('@sympal_menu_manager_tree_add_node?root_slug='.$menuItem['slug'].'&id=NODE_ID&new_label=LABEL') ?>';
      url = url.replace('NODE_ID', currentMenuItemId);
      url = url.replace('LABEL', sLabel);

      executeAjaxUrl(url);
    }
  }

  function editNodeLabel()
  {
    var sLabel = window.prompt("Enter a new label for this child: ", getElement('node-' + currentMenuItemId).innerHTML);

    if (sLabel && sLabel.length > 0) {
      var url = '<?php echo url_for('@sympal_menu_manager_tree_update_node?root_slug='.$menuItem['slug'].'&id=NODE_ID&new_label=LABEL') ?>';
      url = url.replace('NODE_ID', currentMenuItemId);
      url = url.replace('LABEL', sLabel);

      executeAjaxUrl(url);
    }
  }

  function editNode()
  {
    var url = '<?php echo url_for('@sympal_menu_items_edit?id=NODE_ID') ?>';
    url = url.replace('NODE_ID', currentMenuItemId);
    location.href = url;
  }

  function deleteNode()
  {
    var url = '<?php echo url_for('@sympal_menu_manager_tree_delete_node?root_slug='.$menuItem['slug'].'&id=NODE_ID') ?>';
    url = url.replace('NODE_ID', currentMenuItemId);
    location.href = url; 
  }

  function onTriggerContextMenu(p_oEvent)
  {
    var oTarget = this.contextEventTarget,
		Dom = YAHOO.util.Dom;

    var oTextNode = Dom.hasClass(oTarget, "ygtvlabel") ? oTarget : Dom.getAncestorByClassName(oTarget, "ygtvlabel");

    if (oTextNode)
    {
      var num = oTextNode.id.replace('ygtvlabelel', '');
      currentMenuItemId = oTextNode.id.replace('node-', '');
    } else {
      this.cancel();
    }
  }

  <?php if (!$sf_request->isXmlHttpRequest()): ?>
  var oContextMenu = new YAHOO.widget.ContextMenu("mytreecontextmenu", {
    trigger: "sympal_menu_manager_tree",
    lazyload: true, 
    itemdata: [
        { text: "Add Child", onclick: { fn: addNode } },
        { text: "Edit Label", onclick: { fn: editNodeLabel } },
        { text: "Edit", onclick: { fn: editNode } },
        { text: "Delete", onclick: { fn: deleteNode } }
    ] });
  <?php else: ?>
  var oContextMenu = new YAHOO.widget.ContextMenu("mytreecontextmenu", {
    trigger: "sympal_menu_manager_tree",
    lazyload: true
    });
  <?php endif; ?>

  oContextMenu.subscribe("triggerContextMenu", onTriggerContextMenu);
});

DDSend = function(id, sGroup, config)
{
  if (id)
  {
    this.init(id, sGroup, config);
    this.initFrame();
  }

  var s = this.getDragEl().style;
  s.borderColor = "transparent";
  s.backgroundColor = "#f6f5e5";
  s.opacity = 0.76;
  s.filter = "alpha(opacity=76)";
};

DDSend.prototype = new YAHOO.util.DDProxy();

DDSend.prototype.onDragDrop = function(e, id)
{
  var moveId = this.id.replace('node-', '');
  var toId = id.replace('node-', '');

  var url = '<?php echo url_for('@sympal_menu_manager_tree_move_node?move_action=MOVE_ACTION&slug='.$menuItem['slug'].'&move_id=MOVE_ID&to_id=TO_ID', 'absolute=true') ?>';
  url = url.replace('MOVE_ID', moveId);
  url = url.replace('TO_ID', toId);
  url = url.replace('MOVE_ACTION', getElement('move_action').value);

  executeAjaxUrl(url);

  var el = getElement(id);
  el.className = 'drag_on_item';
}

DDSend.prototype.startDrag = function(x, y)
{
  var dragEl = this.getDragEl();
  var clickEl = this.getEl();

  dragEl.innerHTML = clickEl.innerHTML;
  dragEl.className = 'drag_item';
};

DDSend.prototype.onDragEnter = function(e, id)
{
  var el = getElement(id);
  el.className = 'drag_on_item';
};

DDSend.prototype.onDragOut = function(e, id)
{
  var el = getElement(id);
  el.className = '';
}

DDSend.prototype.endDrag = function(e)
{
  var el = getElement(this.id);
  el.className = '';
}
</script>