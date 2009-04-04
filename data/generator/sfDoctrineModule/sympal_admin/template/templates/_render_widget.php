[?php use_sympal_yui_css('container/assets/skins/sam/container') ?]
[?php use_sympal_yui_js('yahoo-dom-event/yahoo-dom-event') ?]
[?php use_sympal_yui_js('animation/animation') ?]
[?php use_sympal_yui_js('container/container') ?]

[?php $validatorSchema = $form->getValidatorSchema() ?]

[?php $id = sfInflector::tableize($name).'_tool_tip' ?]
<div id="[?php echo $id ?]" [?php if ($help || $help = $widget->renderHelp()): ?]title="[?php echo $help; ?]" [?php endif; ?]class="[?php echo $class ?][?php $widget->hasError() and print ' errors' ?][?php $validatorSchema[$name]->getOption('required') and print ' required' ?]">
  [?php echo $widget->renderError() ?]

  <div>
    [?php if (!$widget instanceof sfFormFieldSchema): ?]
      [?php echo $widget->renderLabel($label) ?]
    [?php endif; ?]

    [?php echo $widget->render($attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes) ?]
  </div>
</div>

[?php if ($help): ?]
<script type="text/javascript">
var ttA = new YAHOO.widget.Tooltip("ttA", { 
			context: "[?php echo $id ?]",
			effect: { effect: YAHOO.widget.ContainerEffect.FADE, duration: 0.20 }
		});

</script>
[?php endif; ?]