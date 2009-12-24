<?php use_helper('jQuery') ?>

<?php use_stylesheet('/sfSympalMenuPlugin/css/jQuery.treeTable.css') ?>

<?php use_javascript('/sfSympalPlugin/jquery/js/jquery-ui.min.js') ?>
<?php use_javascript('/sfSympalMenuPlugin/js/jQuery.treeTable.min.js') ?>

<div class="sf_admin_list">
  <?php if (!$pager->getNbResults()): ?>
    <p><?php echo __('No result', array(), 'sf_admin') ?></p>
  <?php else: ?>
    <a name="list_top"></a>
    <table class="treeTable" id="main_list" cellspacing="0">
      <thead>
        <tr>
          <th id="sf_admin_list_batch_actions"><input id="sf_admin_list_batch_checkbox" type="checkbox" onclick="checkAll();" /></th>
          <?php include_partial('sympal_menu_items/list_th_tabular', array('sort' => $sort)) ?>
          <th></th>
          <th id="sf_admin_list_th_actions"><?php echo __('Actions', array(), 'sf_admin') ?></th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th colspan="8">
            <?php if ($pager->haveToPaginate()): ?>
              <?php include_partial('sympal_menu_items/pagination', array('pager' => $pager)) ?>
            <?php endif; ?>

            <?php echo format_number_choice('[0] no result|[1] 1 result|(1,+Inf] %1% results', array('%1%' => $pager->getNbResults()), $pager->getNbResults(), 'sf_admin') ?>
            <?php if ($pager->haveToPaginate()): ?>
              <?php echo __('(page %%page%%/%%nb_pages%%)', array('%%page%%' => $pager->getPage(), '%%nb_pages%%' => $pager->getLastPage()), 'sf_admin') ?>
            <?php endif; ?>
          </th>
        </tr>
      </tfoot>
      <tbody>
        <?php foreach ($pager->getResults() as $i => $menu_item): $odd = fmod(++$i, 2) ? 'odd' : 'even' ?>
          <tr id="node-<?php echo $menu_item['id'] ?>" class="sf_admin_row <?php echo $odd ?><?php
          // insert hierarchical info
          $node = $menu_item->getNode();
          if ($node->isValidNode() && $node->hasParent())
          {
            echo " child-of-node-".$node->getParent()->getId();
          }
          ?>">
            <?php include_partial('sympal_menu_items/list_td_batch_actions', array('sf_sympal_menu_item' => $menu_item, 'helper' => $helper)) ?>
            <?php include_partial('sympal_menu_items/list_td_tabular', array('sf_sympal_menu_item' => $menu_item)) ?>
            <td>
              <?php if (!$node->isRoot()): ?>
                <?php echo jq_button_to_remote('Up', array(
                  'method' => 'get',
                  'update' => 'sf_admin_container',
                  'url' => '@sympal_menu_items_move?direction=up&id='.$menu_item['id'],
                  'loading' => "$('#sympal_ajax_loading').show();",
                  'complete' => "$('#sympal_ajax_loading').hide();",
                )) ?>

                <?php echo jq_button_to_remote('Down', array(
                  'method' => 'get',
                  'update' => 'sf_admin_container',
                  'url' => '@sympal_menu_items_move?direction=down&id='.$menu_item['id'],
                  'loading' => "$('#sympal_ajax_loading').show();",
                  'complete' => "$('#sympal_ajax_loading').hide();",
                )) ?>
              <?php endif; ?>
            </td>
            <?php include_partial('sympal_menu_items/list_td_actions', array('sf_sympal_menu_item' => $menu_item, 'helper' => $helper)) ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<script type="text/javascript">
/* <![CDATA[ */
function checkAll()
{
  var boxes = document.getElementsByTagName('input'); for(index in boxes) { box = boxes[index]; if (box.type == 'checkbox' && box.className == 'sf_admin_batch_checkbox') box.checked = document.getElementById('sf_admin_list_batch_checkbox').checked } return true;
}
$(document).ready(function()  {
  $("#main_list").treeTable({
    treeColumn: 1,
    initialState: 'expanded'
  });

  // Configure draggable nodes
  $("#main_list .file, #main_list .folder").draggable({
    helper: "clone",
    opacity: .75,
    refreshPositions: true, // Performance?
    revert: "invalid",
    revertDuration: 300,
    scroll: true
  });

  // Configure droppable rows
  $("#main_list .file, #main_list .folder").each(function() {
    $(this).parents("tr").droppable({
      accept: ".file, .folder",
      drop: function(e, ui) { 
        // Call jQuery treeTable plugin to move the branch
        var parentTr = $($(ui.draggable).parents("tr"));
        parentTr.appendBranchTo(this);
        var parentId = parentTr.attr("id");
        var thisId = this.id;
        $("#select_" + parentId).val(thisId.substr(5));
      },
      hoverClass: "accept",
      over: function(e, ui) {
        // Make the droppable branch expand when a draggable node is moved over it.
        if(this.id != ui.draggable.parents("tr")[0].id && !$(this).is(".expanded")) {
          $(this).expand();
        }
      }
    });
  });

  // Make visible that a row is clicked
  $("table#main_list tbody tr").mousedown(function() {
    $("tr.selected").removeClass("selected"); // Deselect currently selected rows
    $(this).addClass("selected");
  });

  // Make sure row is selected when span is clicked
  $("table#main_list tbody tr span").mousedown(function() {
    $($(this).parents("tr")[0]).trigger("mousedown");
  });
});
/* ]]> */
</script>