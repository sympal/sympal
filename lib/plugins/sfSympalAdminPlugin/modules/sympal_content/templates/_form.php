<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>

<?php sympal_use_stylesheet('/sfSympalAdminPlugin/css/content_admin.css', 'last') ?>

<div class="sf_admin_form">
  <?php echo form_tag_for($form, '@sympal_content') ?>
    <?php echo $form->renderHiddenFields(false) ?>

    <?php if ($form->hasGlobalErrors()): ?>
      <?php echo $form->renderGlobalErrors() ?>
    <?php endif; ?>

    <?php foreach ($configuration->getFormFields($form, $form->isNew() ? 'new' : 'edit') as $fieldset => $fields): ?>
      <?php include_partial('sympal_content/form_fieldset', array('sf_sympal_content' => $sf_sympal_content, 'form' => $form, 'fields' => $fields, 'fieldset' => $fieldset)) ?>
    <?php endforeach; ?>

    <?php include_partial('sympal_content/form_actions', array('sf_sympal_content' => $sf_sympal_content, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
  </form>
</div>