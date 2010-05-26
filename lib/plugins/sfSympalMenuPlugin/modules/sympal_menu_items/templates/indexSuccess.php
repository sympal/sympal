<?php if ($helper->isNestedSet() && $sf_request->getParameter('order')): ?>
  <?php include_partial('nested_sortable_index', array('pager' => $pager)); ?>
<?php else: ?>

  <?php sympal_use_stylesheet('/sfSympalMenuPlugin/css/jQuery.treeTable.css', 'first') ?> 
  <?php sympal_use_stylesheet('/sfSympalMenuPlugin/css/table.css', 'first') ?> 
  <?php sympal_use_javascript('/sfSympalMenuPlugin/js/jquery.min-1.2.6.js', 'last') ?> 
  <?php sympal_use_javascript('/sfSympalMenuPlugin/js/jquery-ui.min-1.5.3.js', 'last') ?> 
  <?php sympal_use_javascript('/sfSympalMenuPlugin/js/jquery.treeTable.min.js', 'last') ?> 

  <?php use_helper('I18N', 'Date') ?>

  <?php include_partial('sympal_menu_items/assets') ?>

  <?php echo button_to(__('Adjust Order'), '@'.$sf_context->getRouting()->getCurrentRouteName().'?order=1') ?>

  <div id="sf_admin_container">

    <h1><?php echo $title = __('Menu items list', array(), 'messages'); $sf_response->setTitle($title); ?></h1>

    <div id="sf_admin_header">
      <?php include_partial('sympal_menu_items/list_header', array('pager' => $pager)) ?>
    </div>

    <div id="sf_admin_bar">
      <?php include_partial('sympal_menu_items/filters', array('form' => $filters, 'configuration' => $configuration)) ?>
    </div>

    <div id="sf_admin_content">

      <form action="<?php echo url_for('sympal_menu_items_collection', array('action' => 'batch')) ?>" method="post">

        <?php include_partial('sympal_menu_items/list', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper)) ?>

        <ul class="sf_admin_actions">
          <?php include_partial('sympal_menu_items/list_batch_actions', array('helper' => $helper)) ?>
          <?php include_partial('sympal_menu_items/list_actions', array('helper' => $helper)) ?>
        </ul>

      </form>

    </div>

    <div id="sf_admin_footer">
      <?php include_partial('sympal_menu_items/list_footer', array('pager' => $pager)) ?>
    </div>

  </div>

<?php endif; ?>
