<fieldset id="<?php echo $form->getName() ?>">
  <?php foreach ($form as $name => $widget): ?>
    <?php if ($widget->isHidden()) continue; ?>
    <?php echo get_partial('sympal_default/render_form_widget', array('form' => $form, 'name' => $name, 'widget' => $widget)) ?>
  <?php endforeach; ?>
</fieldset>