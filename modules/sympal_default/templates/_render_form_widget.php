<?php $embeddedForms = $form->getEmbeddedForms() ?>
<?php $validatorSchema = $form->getValidatorSchema() ?>

<?php if ($widget instanceof sfFormFieldSchema): ?>
  <div class="sf_admin_form_row">
    <h3><?php echo strip_tags($widget->renderLabel()) ?></h3>
    <?php echo get_partial('sympal_default/render_form', array('form' => $embeddedForms[$name])) ?>
  </div>
<?php else: ?>
  <div class="sf_admin_form_row<?php $widget->hasError() and print ' errors' ?><?php $validatorSchema[$name]->getOption('required') and print ' required' ?>">
    <?php if ($help = $widget->renderHelp()): ?>
      <?php $helpId = sfInflector::tableize($name).'_help' ?>
      <span id="<?php echo $helpId ?>" class="help" style="float: right;" title="<?php echo strip_tags($help); ?>">
        <?php echo image_tag('/sf/sf_admin/images/help.png') ?>
      </span>
    <?php endif; ?>

    <?php echo $widget->renderError() ?>
    <?php echo $widget->renderLabel() ?>

    <?php echo $widget ?>
    <?php echo $widget->renderHelp() ?>
  </div>
<?php endif; ?>