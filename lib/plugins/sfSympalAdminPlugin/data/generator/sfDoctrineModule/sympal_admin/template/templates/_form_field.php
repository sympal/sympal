[?php if ($field->isPartial()): ?]
  [?php include_partial('<?php echo $this->getModuleName() ?>/'.$name, array('form' => $form, 'attributes' => $attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes)) ?]
[?php elseif ($field->isComponent()): ?]
  [?php include_component('<?php echo $this->getModuleName() ?>', $name, array('form' => $form, 'attributes' => $attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes)) ?]
[?php else: ?]
  [?php if ($form instanceof sfForm && $form->isI18n() && !isset($form[$name])): ?]
    [?php foreach ($form->getEmbeddedForms() as $embeddedFormName => $embeddedForm): ?]
      [?php if (isset($embeddedForm[$name])): ?]
        [?php echo $form[$embeddedFormName]->renderHiddenFields() ?]
        <div class="[?php echo $class ?][?php $form[$embeddedFormName][$name]->hasError() and print ' errors' ?]">
          [?php echo $form[$embeddedFormName][$name]->renderError() ?]
          <div>
            [?php $i18nLabel = strip_tags($form[$embeddedFormName]->renderLabel($label)) ?]
            [?php $label = strip_tags($form[$embeddedFormName][$name]->renderLabel($label)) ?]
            [?php echo $form[$embeddedFormName][$name]->renderLabel($i18nLabel.' '.$label) ?]

            <div class="content">[?php echo $form[$embeddedFormName][$name]->render($attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes) ?]</div>

            [?php if ($help): ?]
              <div class="help">[?php echo __($help, array(), '<?php echo $this->getI18nCatalogue() ?>') ?]</div>
            [?php elseif ($help = $form[$embeddedFormName][$name]->renderHelp()): ?]
              <div class="help">[?php echo $help ?]</div>
            [?php endif; ?]
          </div>
        </div>
      [?php endif; ?]
    [?php endforeach; ?]
  [?php else: ?]
    [?php echo $form[$name]->renderError() ?]
    [?php $thisEmbeddedForm = null ?]
    [?php if ($form[$name] instanceof sfFormFieldSchema): ?]
      [?php if (isset($embeddedForm)): ?]
        [?php $embeddedForms = $embeddedForm->getEmbeddedForms() ?]
      [?php else: ?]
        [?php $embeddedForms = $form->getEmbeddedForms() ?>
      [?php endif ?]
      [?php $thisEmbeddedForm = $embeddedForms[$name] ?>

      <div class="sympal_admin_embedded_form">
        [?php foreach ($form[$name] as $fieldName => $f): ?]
          [?php if ($f->isHidden()): ?]
             [?php echo $f ?]
             [?php continue ?]
          [?php endif; ?]

          [?php include_partial('<?php echo $this->getModuleName() ?>/form_field', array(
            'name'           => $fieldName,
            'attributes'     => array(),
            'label'          => '',
            'help'           => '',
            'form'           => $form[$name],
            'field'          => $field,
            'class'          => 'sf_admin_form_row sf_admin_'.strtolower($field->getType()).' sf_admin_form_field_'.$name,
            'isEmbeddedForm' => true,
            'embeddedForm'   => $thisEmbeddedForm
          )) ?]
        [?php endforeach; ?]
      </div>
    [?php else: ?]
      <div class="[?php echo $class ?][?php $form[$name]->hasError() and print ' errors' ?]">

        <div>
          [?php echo $form[$name]->renderLabel($label) ?]

          <div class="content">[?php echo $form[$name]->render($attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes) ?]</div>

          [?php if ($help): ?]
            <div class="help">[?php echo __($help, array(), '<?php echo $this->getI18nCatalogue() ?>') ?]</div>
          [?php elseif ($help = $form[$name]->renderHelp()): ?]
            <div class="help">[?php echo $help ?]</div>
          [?php endif; ?]
        </div>
    
      </div>
    [?php endif; ?]
  [?php endif; ?]
[?php endif; ?]