[?php include_stylesheets_for_form($form) ?]
[?php include_javascripts_for_form($form) ?]

[?php use_sympal_yui_css('tabview/assets/skins/sam/tabview') ?]

[?php use_sympal_yui_js('yahoo-dom-event/yahoo-dom-event') ?]
[?php use_sympal_yui_js('element/element') ?]
[?php use_sympal_yui_js('connection/connection') ?]
[?php use_sympal_yui_js('tabview/tabview') ?]

<div class="sf_admin_form sympal_form">
  [?php echo form_tag_for($form, '@<?php echo $this->params['route_prefix'] ?>') ?]
    [?php echo $form->renderHiddenFields() ?]

    [?php if ($form->hasGlobalErrors()): ?]
      [?php echo $form->renderGlobalErrors() ?]
    [?php endif; ?]

    <div id="demo" class="yui-navset">
      <ul class="yui-nav">
        [?php foreach ($configuration->getFormFields($form, $form->isNew() ? 'new' : 'edit') as $fieldset => $fields): ?]
          <li[?php if (!isset($selected)) { echo ' class="selected"'; $selected = true; } ?]><a href="#[?php echo $fieldset ?]"><em>[?php echo $fieldset == 'NONE' ? ucwords(sfInflector::humanize(sfInflector::tableize($form->getModelName()))):$fieldset ?]</em></a></li>
        [?php endforeach; ?]

        [?php foreach ($form as $key => $value): ?]
          [?php if ($value instanceof sfFormFieldSchema): ?]
            <li><a href="#[?php echo $key ?]"><em>[?php echo sfInflector::humanize(sfInflector::tableize($key)) ?]</em></a></li>
          [?php endif; ?]
        [?php endforeach; ?]
      </ul>

      <div class="yui-content">
        [?php foreach ($configuration->getFormFields($form, $form->isNew() ? 'new' : 'edit') as $fieldset => $fields): ?]
          <div id="[?php echo $fieldset ?]"><p>[?php include_partial('<?php echo $this->getModuleName() ?>/form_fieldset', array('content' => '', 'form' => $form, 'fields' => $fields, 'fieldset' => $fieldset)) ?]</p></div>
        [?php endforeach; ?]

        [?php $embeddedForms = $form->getEmbeddedForms() ?]
        [?php foreach ($form as $key => $value): ?]
          [?php if ($value instanceof sfFormFieldSchema): ?]
            <div id="[?php echo $key ?]">
              <p>
                [?php echo include_partial('<?php echo $this->getModuleName() ?>/render_embedded_form', array('form' => $embeddedForms[$key], 'embeddedForm' => $value)) ?]
              </p>
            </div>
          [?php endif; ?]
        [?php endforeach; ?]
      </div>
    </div>

    <script>
    (function() {
      var tabView = new YAHOO.widget.TabView('demo');
      YAHOO.log("The example has finished loading; as you interact with it, you'll see log messages appearing here.", "info", "example");
    })();
    </script>

    [?php include_partial('<?php echo $this->getModuleName() ?>/form_actions', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?]
  </form>
</div>