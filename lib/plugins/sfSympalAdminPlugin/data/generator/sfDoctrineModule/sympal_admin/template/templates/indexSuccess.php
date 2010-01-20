[?php if ($helper->isNestedSet() && $sf_request->getParameter('order')): ?]

[?php sympal_use_jquery() ?]
[?php jq_add_plugins_by_name(array('ui')) ?]

[?php sympal_use_stylesheet('/sfSympalAdminPlugin/css/nestedsortablewidget.css') ?]
[?php sympal_use_javascript('/sfSympalAdminPlugin/js/interface.js') ?]
[?php sympal_use_javascript('/sfSympalAdminPlugin/js/inestedsortable.pack.js') ?]
[?php sympal_use_javascript('/sfSympalAdminPlugin/js/jquery.nestedsortablewidget.pack.js') ?]
[?php sympal_use_javascript('/sfSympalAdminPlugin/js/jquery.nested_set.js') ?]

[?php echo button_to(__('Back to list', array(), 'sf_admin'), '@'.$sf_context->getRouting()->getCurrentRouteName()) ?]

<h1>[?php echo $title = __('Adjust Order') ?]</h1>
[?php $sf_response->setTitle($title) ?]

[?php foreach ($pager->getResults() as $result): ?]
[?php if ($result->getNode()->isRoot()): ?]

<h2>[?php echo $result ?]</h2>

<div id="sf_admin_nested_set_[?php echo $result->getId() ?]"></div>
<script type="text/javascript">
$(function() {
  $('#sf_admin_nested_set_[?php echo $result->getId() ?]').NestedSortableWidget({
    name: 'sf_admin_nested_set_[?php echo $result->getId() ?]',
    loadUrl: "[?php echo url_for('@'.$sf_context->getRouting()->getCurrentRouteName().'?root_id='.$result->getId().'&sf_format=json') ?]",
    saveUrl: "[?php echo url_for('@sympal_admin_save_nested_set?model=<?php echo $this->getModelClass() ?>') ?]"
  });
});
</script>

[?php endif; ?]
[?php endforeach; ?]

[?php else: ?]

[?php use_helper('I18N', 'Date') ?]
[?php include_partial('<?php echo $this->getModuleName() ?>/assets') ?]

<?php if ($this->isNestedSet()): ?>
  [?php echo button_to(__('Adjust Order'), '@'.$sf_context->getRouting()->getCurrentRouteName().'?order=1') ?]
<?php endif; ?>

<div id="sf_admin_container">
  <h1>[?php echo $title = <?php echo $this->getI18NString('list.title') ?>; $sf_response->setTitle($title); ?]</h1>

  <div id="sf_admin_header">
    [?php include_partial('<?php echo $this->getModuleName() ?>/list_header', array('pager' => $pager)) ?]
  </div>

<?php if ($this->configuration->hasFilterForm()): ?>
  <div id="sf_admin_bar">
    [?php include_partial('<?php echo $this->getModuleName() ?>/filters', array('form' => $filters, 'configuration' => $configuration)) ?]
  </div>
<?php endif; ?>

  <div id="sf_admin_content">
<?php if ($this->configuration->getValue('list.batch_actions')): ?>
    <form action="[?php echo url_for('<?php echo $this->getUrlForAction('collection') ?>', array('action' => 'batch')) ?]" method="post">
<?php endif; ?>
    [?php include_partial('<?php echo $this->getModuleName() ?>/list', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper)) ?]
    <ul class="sf_admin_actions">
      [?php include_partial('<?php echo $this->getModuleName() ?>/list_batch_actions', array('helper' => $helper)) ?]
      [?php include_partial('<?php echo $this->getModuleName() ?>/list_actions', array('helper' => $helper)) ?]
    </ul>
<?php if ($this->configuration->getValue('list.batch_actions')): ?>
    </form>
<?php endif; ?>
  </div>

  <div id="sf_admin_footer">
    [?php include_partial('<?php echo $this->getModuleName() ?>/list_footer', array('pager' => $pager)) ?]
  </div>
</div>

[?php endif; ?]