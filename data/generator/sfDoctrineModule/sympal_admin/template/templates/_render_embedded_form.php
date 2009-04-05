<fieldset id="sf_fieldset_[?php echo preg_replace('/[^a-z0-9_]/', '_', strtolower($name)) ?]">
  [?php foreach ($embeddedForm as $key => $value): ?]
    [?php include_partial('<?php echo $this->getModuleName() ?>/render_widget', array('form' => $form, 'name' => $key, 'class' => 'sf_admin_form_row sf_admin_form_field_'.$key, 'widget' => $value, 'label' => null, 'attributes' => array(), 'help' => '')) ?]
  [?php endforeach; ?]
</fieldset>