<?php use_helper('I18N', 'Date') ?>
<?php include_partial('sympal_content/assets') ?>

<div id="sf_admin_container">
  <h2><?php echo __('Editing %%type%% titled "%%content%%"', array('%%type%%' => $sf_sympal_content->getType()->getLabel(),'%%content%%' => $sf_sympal_content->getHeaderTitle()), 'messages') ?></h2>

  <div id="sf_admin_header">
    <?php include_partial('sympal_content/form_header', array('sf_sympal_content' => $sf_sympal_content, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>

  <div id="sf_admin_content">
    <?php include_partial('sympal_content/form', array('sf_sympal_content' => $sf_sympal_content, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('sympal_content/form_footer', array('sf_sympal_content' => $sf_sympal_content, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>
</div>

<?php echo get_sympal_editor($sf_sympal_content->getMenuItem(), $sf_sympal_content) ?>