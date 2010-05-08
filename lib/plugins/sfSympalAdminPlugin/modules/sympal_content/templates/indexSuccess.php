<?php use_helper('I18N', 'Date') ?>
<?php include_partial('sympal_content/assets') ?>

<div id="sf_admin_container">
  <h1><?php echo $title = __('Manage '.$contentType->getLabel().' Content', array(), 'messages'); $sf_response->setTitle($title); ?></h1>

  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Site Content' => '@sympal_content_types_index',
    $contentType->getLabel() => '@sympal_content_list_type?type='.$contentType->getSlug() 
  )) ?>

  <div id="sf_admin_header">
    <?php include_partial('sympal_content/list_header', array('pager' => $pager)) ?>
  </div>

  <div id="sf_admin_bar">
    <?php include_partial('sympal_content/filters', array('form' => $filters, 'configuration' => $configuration)) ?>
  </div>

  <div id="sf_admin_content">
    <form action="<?php echo url_for('sympal_content_collection', array('action' => 'batch')) ?>" method="post">
    <?php include_partial('sympal_content/list', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper)) ?>
    <ul class="sf_admin_actions">
      <?php include_partial('sympal_content/list_batch_actions', array('helper' => $helper)) ?>
      <?php include_partial('sympal_content/list_actions', array('helper' => $helper, 'contentType' => $contentType)) ?>
    </ul>
    </form>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('sympal_content/list_footer', array('pager' => $pager)) ?>
  </div>
</div>