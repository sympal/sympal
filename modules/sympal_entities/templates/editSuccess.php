<?php use_helper('I18N', 'Date') ?>
<?php include_partial('sympal_entities/assets') ?>

<div id="sf_admin_container">
  <h1><?php echo __('Editing Entity #%%id%%', array('%%id%%' => link_to($entity['id'], 'sympal_entities_edit', $entity)), 'messages') ?></h1>

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

<?php echo get_cmf_editor($entity->getMainMenuItem(), $entity) ?>