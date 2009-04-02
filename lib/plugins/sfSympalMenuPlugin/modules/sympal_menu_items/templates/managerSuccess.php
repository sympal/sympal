<?php use_stylesheet('/sfSympalPlugin/yui/treeview/assets/skins/sam/treeview.css') ?>
<?php use_stylesheet('/sfSympalPlugin/yui/menu/assets/skins/sam/menu.css') ?>

<?php use_stylesheet('/sfSympalPlugin/yui/assets/css/folders/tree.css') ?>
<?php use_stylesheet('/sfSympalPlugin/yui/fonts/fonts-min.css') ?>
<?php use_stylesheet('/sfSympalPlugin/yui/treeview/assets/skins/sam/treeview.css') ?>
<?php use_javascript('/sfSympalPlugin/yui/yahoo-dom-event/yahoo-dom-event.js') ?>
<?php use_javascript('/sfSympalPlugin/yui/treeview/treeview-min.js') ?>
<?php use_javascript('/sfSympalPlugin/yui/event/event-min.js') ?>
<?php use_javascript('/sfSympalPlugin/yui/dragdrop/dragdrop-min.js') ?>
<?php use_javascript('/sfSympalPlugin/yui/connection/connection-min.js') ?>
<?php use_javascript('/sfSympalPlugin/yui/container/container_core.js') ?>
<?php use_javascript('/sfSympalPlugin/yui/menu/menu-min.js') ?>

<h1>Sympal Menu Manager</h1>

<style>
#expandcontractdiv { border:1px dotted #dedede; background-color:#EBE4F2; margin:0 0 .5em 0; padding:0.4em; }
</style>

<!-- markup for expand/contract links -->
<div id="expandcontractdiv">
	<a id="expand" href="#">Expand all</a>
	<a id="collapse" href="#">Collapse all</a>
</div>

<?php slot('sympal_right_sidebar') ?>
  <h2>Manage Sympal Menus</h2>
  <ul>
    <?php foreach ($roots as $root): ?>
      <li><?php echo link_to($root['name'], '@sympal_menu_manager_tree?slug='.$root['name']) ?></li>
    <?php endforeach; ?>
  </ul>
<?php end_slot() ?>

<h2>Managing <?php echo $menuItem['name'] ?> Menu</h2>

<?php
class MyMenu extends sfSympalMenuSite
{
  protected static $_dragDrops;

  public function renderChildBody()
  {
    $id = $this->getMenuItem()->getId();
    $html = '<div id="'.$id.'" class="ygtvlabel">';
    if ($this->_route)
    {
      $html .= $this->renderLink();
    } else {
      $html .= $this->renderLabel();
    }
    $html .= '</div>';
    self::$_dragDrops .= "new DDSend(\"".$id."\");\n";
    return $html;
  }

  public function render()
  {
    return parent::render();

    $id = Doctrine_Inflector::urlize($this->getName()).'-menu';
    self::$_dragDrops .= "new DDSend(\"".$id."\");\n";
  }

  public static function getDragDrops()
  {
    return self::$_dragDrops;
  }
}
?>

<div id="sympal_menu_manager_tree">
  <?php
  $menu = get_sympal_menu($menuItem['name'], true, 'MyMenu');
  $menu->callRecursively('setRoute', null);
  echo $menu;
  ?>
</div>

<script type="text/javascript">

function getElement(id)
{
  var el;

  // this is called anytime we drag out of
  // a potential valid target
  // remove the highlight
  if ("string" == typeof id) {
      el = YAHOO.util.DDM.getElement(id);
  } else {
      el = YAHOO.util.DDM.getBestMatch(id).getEl();
  }
  return el;
}

(function() {
	var tree;
	
	function treeInit()
	{
		tree = new YAHOO.widget.TreeView("sympal_menu_manager_tree");
		tree.draw();

    <?php echo MyMenu::getDragDrops(); ?>

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
  var oCurrentTextNode = null;

  function addNode()
  {
    var sLabel = window.prompt("Enter a label for the new child: ", ""), oChildNode;

    if (sLabel && sLabel.length > 0)
    {
      oChildNode = new YAHOO.widget.TextNode(sLabel, oCurrentTextNode, false);

      oCurrentTextNode.refresh();
      oCurrentTextNode.expand();

      oTextNodeMap[oChildNode.labelElId] = oChildNode;
    }
  }

  function editNodeLabel()
  {
    var sLabel = window.prompt("Enter a new label for this child: ", oCurrentTextNode.getLabelEl().innerHTML);

    if (sLabel && sLabel.length > 0) {
      oCurrentTextNode.getLabelEl().innerHTML = sLabel;
    }
  }

  function deleteNode()
  {
    delete oTextNodeMap[oCurrentTextNode.labelElId];

    oTreeView.removeNode(oCurrentTextNode);
    oTreeView.draw();
  }

  function onTriggerContextMenu(p_oEvent)
  {
    var oTarget = this.contextEventTarget,
		Dom = YAHOO.util.Dom;

    var oTextNode = Dom.hasClass(oTarget, "ygtvlabel") ? oTarget : Dom.getAncestorByClassName(oTarget, "ygtvlabel");

    if (oTextNode)
    {
      oCurrentTextNode = oTextNodeMap[oTarget.id];
    } else {
      this.cancel();
    }
  }

  var oContextMenu = new YAHOO.widget.ContextMenu("mytreecontextmenu", {
    trigger: "sympal_menu_manager_tree",
    lazyload: true, 
    itemdata: [
        { text: "Add Child", onclick: { fn: addNode } },
        { text: "Edit Label", onclick: { fn: editNodeLabel } },
        { text: "Delete", onclick: { fn: deleteNode } }
    ] });

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
    alert("dd " + this.id + " was dropped on " + id);

    var el = getElement(id);

    el.style.backgroundColor = "transparent";
}

DDSend.prototype.startDrag = function(x, y)
{
    var dragEl = this.getDragEl();
    var clickEl = this.getEl();

    dragEl.innerHTML = clickEl.innerHTML;
    dragEl.className = clickEl.className;
    dragEl.style.color = clickEl.style.color;
    dragEl.style.backgroundColor = "#ffc";
};

DDSend.prototype.onDragEnter = function(e, id)
{
    var el = getElement(id);
    el.style.backgroundColor = "#ffc";
};

DDSend.prototype.onDragOut = function(e, id)
{
  var el = getElement(id);
  el.style.backgroundColor = "transparent";
}

DDSend.prototype.endDrag = function(e, id)
{
  var el = getElement(id);
  el.style.backgroundColor = "transparent";
}
</script>