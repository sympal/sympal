<?php use_helper('I18N', 'Date') ?>
<?php include_partial('sympal_content/assets') ?>

<?php echo get_sympal_breadcrumbs($content->getMainMenuItem(), $content, 'Edit', true) ?>

<div id="sf_admin_container">
  <h2><?php echo __('Editing %%type%% titled "%%content%%"', array('%%type%%' => $content->getType()->getLabel(),'%%content%%' => $content->getHeaderTitle()), 'messages') ?></h2>

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

<?php echo get_sympal_editor($content->getMainMenuItem(), $content) ?>