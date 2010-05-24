<?php sympal_use_javascript('jquery.js') ?>
<?php sympal_use_javascript('jquery.ui.js') ?>
<?php sympal_use_stylesheet('/sfSympalAdminPlugin/css/nested_sortable/nestedsortablewidget.css') ?>
<?php sympal_use_javascript('/sfSympalAdminPlugin/js/nested_sortable/interface.js') ?>
<?php sympal_use_javascript('/sfSympalAdminPlugin/js/nested_sortable/inestedsortable.pack.js') ?>
<?php sympal_use_javascript('/sfSympalAdminPlugin/js/nested_sortable/jquery.nestedsortablewidget.pack.js') ?>

<?php echo button_to(__('Back to list', array(), 'sf_admin'), '@'.$sf_context->getRouting()->getCurrentRouteName()) ?>

<h1><?php echo $title = __('Adjust Order') ?></h1>

<?php $sf_response->setTitle($title) ?>

<?php foreach ($pager->getResults() as $result): ?>

  <?php if ($result->getNode()->isRoot()): ?>

    <h2><?php echo $result ?></h2>

    <div id="sf_admin_nested_set_<?php echo $result->getId() ?>"></div>
    <script type="text/javascript">
    $(function() {
      $('#sf_admin_nested_set_<?php echo $result->getId() ?>').NestedSortableWidget({
        name: 'sf_admin_nested_set_<?php echo $result->getId() ?>',
        loadUrl: "<?php echo url_for('@'.$sf_context->getRouting()->getCurrentRouteName().'?root_id='.$result->getId().'&sf_format=json') ?>",
        saveUrl: "<?php echo url_for('@sympal_admin_save_nested_set?model=sfSympalMenuItem') ?>"
      });
    });
    </script>

  <?php endif; ?>

<?php endforeach; ?>
