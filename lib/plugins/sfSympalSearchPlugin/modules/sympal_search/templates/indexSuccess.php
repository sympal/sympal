<?php use_helper('SympalPager') ?>

<h1>Search</h1>

<?php if ($sf_request->getParameter('action') == 'admin_search'): ?>
  <?php if ($q = $sf_request->getParameter('q')): ?>
    <?php echo get_sympal_breadcrumbs(array(
      'Dashboard' => '@sympal_dashboard',
      'Search' => '@'.$sf_context->getRouting()->getCurrentRouteName(),
      sprintf('Searching for "%s"', $q) => null
    )) ?>
  <?php else: ?>
    <?php echo get_sympal_breadcrumbs(array(
      'Dashboard' => '@sympal_dashboard',
      'Search' => '@'.$sf_context->getRouting()->getCurrentRouteName()
    )) ?>
  <?php endif; ?>
<?php endif; ?>

<?php echo get_partial('sympal_search/form') ?>

<?php if (isset($dataGrid)): ?>
  <?php echo $dataGrid->getPagerHeader() ?>

  <?php if ($dataGrid->count()): ?>
    <?php echo $dataGrid->getPagerNavigation(url_for('@'.$sf_context->getRouting()->getCurrentRouteName())) ?>

    <div id="sf_admin_container">
      <div id="sf_admin_content">
        <div class="sf_admin_list">
          <?php echo $dataGrid->render() ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
<?php endif; ?>