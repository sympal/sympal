<?php include_stylesheets_for_form($form) ?>
<?php include_javascripts_for_form($form) ?>

<div class="sf_admin_form" id="sympal_entities_form">
  <?php echo form_tag_for($form, '@sympal_entities') ?>
    <?php echo $form->renderHiddenFields() ?>

    <?php if ($form->hasGlobalErrors()): ?>
      <?php echo $form->renderGlobalErrors() ?>
    <?php endif; ?>

    <div id="right">
      <?php echo get_partial('sympal_entities/form_part', array('name' => 'Mapping', 'form' => $form, 'fields' => array('site_id', 'master_menu_item_id'))) ?>
      <?php echo get_partial('sympal_entities/form_part', array('name' => 'Security', 'form' => $form, 'fields' => array('groups_list', 'permissions_list'))) ?>
      <?php echo get_partial('sympal_entities/form_part', array('name' => 'Rendering', 'form' => $form, 'fields' => array('layout', 'entity_template_id'))) ?>
    </div>

    <div id="left">
      <?php echo get_partial('sympal_entities/form_part', array('name' => $form->getObject()->getType()->getLabel() . ' Information', 'form' => $form[$form->getObject()->getType()->getName()])) ?>
      <?php echo get_partial('sympal_entities/form_part', array('name' => 'Publish', 'form' => $form, 'fields' => array('is_published', 'date_published'))) ?>
    </div>

    <?php include_partial('sympal_entities/form_actions', array('entity' => $entity, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
  </form>
</div>