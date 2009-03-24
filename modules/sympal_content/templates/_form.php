<?php include_stylesheets_for_form($form) ?>
<?php include_javascripts_for_form($form) ?>

<div class="sf_admin_form sympal_form">
  <?php echo form_tag_for($form, '@sympal_content') ?>
    <?php echo $form->renderHiddenFields() ?>

    <?php if ($form->hasGlobalErrors()): ?>
      <?php echo $form->renderGlobalErrors() ?>
    <?php endif; ?>

    <div id="right">
      <?php echo get_partial('sympal_content/form_part', array('name' => 'Mapping', 'form' => $form, 'fields' => array('site_id', 'master_menu_item_id', 'custom_path'))) ?>
      <?php echo get_partial('sympal_content/form_part', array('name' => 'Security', 'form' => $form, 'fields' => array('groups_list', 'permissions_list'))) ?>
      <?php echo get_partial('sympal_content/form_part', array('name' => 'Rendering', 'form' => $form, 'fields' => array('layout', 'content_template_id'))) ?>
    </div>

    <div id="left">
      <?php $typeForm = $form[$form->getObject()->getType()->getName()]; ?>

      <?php echo get_partial('sympal_content/form_part', array('name' => $form->getObject()->getType()->getLabel() . ' Information', 'form' => $typeForm)) ?>

      <?php foreach ($typeForm as $key => $embeddedForm): ?>
        <?php if ($embeddedForm instanceof sfFormFieldSchema): ?>
          <?php echo get_partial('sympal_content/form_part', array('name' => $key, 'form' => $embeddedForm)) ?>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php foreach ($form->getObject()->getSlots() as $slot): ?>
        <fieldset>
          <legend>Edit Slot: <?php echo $slot['name'] ?></legend>
          <?php echo sympal_content_slot($form->getObject(), $slot['name']) ?>
        </fieldset>
      <?php endforeach; ?>

      <?php echo get_partial('sympal_content/form_part', array('name' => 'Publish', 'form' => $form, 'fields' => array('is_published', 'date_published'))) ?>
    </div>

    <?php include_partial('sympal_content/form_actions', array('content' => $content, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
  </form>
</div>