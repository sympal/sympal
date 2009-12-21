<?php use_helper('I18N', 'Date') ?>
<?php include_partial('sympal_menu_items/assets') ?>

<?php if (!$sf_request->isXmlHttpRequest()): ?>
<div id="sf_admin_container">
<?php endif; ?>

  <h2><?php echo $title = __('Sympal menu items List', array(), 'messages'); set_sympal_title($title); ?></h2>

  <div id="sf_admin_header">
    <?php include_partial('sympal_menu_items/list_header', array('pager' => $pager)) ?>
  </div>


  <div id="sf_admin_content">
    <form action="<?php echo url_for('sympal_menu_items_collection', array('action' => 'batch')) ?>" method="post">
    <ul class="sf_admin_actions">
      <?php include_partial('sympal_menu_items/list_batch_actions', array('helper' => $helper)) ?>
      <?php include_partial('sympal_menu_items/list_actions', array('helper' => $helper)) ?>
    </ul>
    <?php include_partial('sympal_menu_items/list', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper)) ?>
    </form>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('sympal_menu_items/list_footer', array('pager' => $pager)) ?>
  </div>

<?php if (!$sf_request->isXmlHttpRequest()): ?>
</div>
<?php endif; ?>
