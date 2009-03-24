<?php echo get_sympal_breadcrumbs($menuItem, null, 'Create New '.$content->getType()->getLabel(), true) ?>

<?php use_helper('I18N', 'Date') ?>
<?php include_partial('sympal_content/assets') ?>

<div id="sf_admin_container">
  <h2><?php echo __('Create New '.$content->getType()->getLabel(), array(), 'messages') ?></h2>

  <div id="sf_admin_header">
    <?php include_partial('sympal_content/form_header', array('content' => $content, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>

  <div id="sf_admin_content">
    <?php include_partial('sympal_content/form', array('content' => $content, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('sympal_content/form_footer', array('content' => $content, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>
</div>
