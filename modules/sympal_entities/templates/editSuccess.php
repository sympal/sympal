<?php use_helper('I18N', 'Date') ?>
<?php include_partial('sympal_entities/assets') ?>

<?php echo get_sympal_breadcrumbs($entity->getMainMenuItem(), $entity, 'Edit', true) ?>

<div id="sf_admin_container">
  <h2><?php echo __('Editing %%type%% titled "%%entity%%"', array('%%type%%' => $entity->getType()->getLabel(),'%%entity%%' => $entity->getHeaderTitle()), 'messages') ?></h2>

  <div id="sf_admin_header">
    <?php include_partial('sympal_entities/form_header', array('entity' => $entity, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>

  <div id="sf_admin_content">
    <?php include_partial('sympal_entities/form', array('entity' => $entity, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('sympal_entities/form_footer', array('entity' => $entity, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>
</div>

<?php echo get_sympal_editor($entity->getMainMenuItem(), $entity) ?>