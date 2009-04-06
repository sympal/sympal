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

    [?php $fields = $configuration->getFormFields($form, $form->isNew() ? 'new' : 'edit') ?]
    [?php $currentTab = $sf_user->getAttribute('<?php echo $this->getModuleName() ?>.current_form_tab', null, 'admin_module'); ?]
    <div id="sympal_admin_gen_tab_view" class="yui-navset">
      <ul class="yui-nav">
        [?php foreach ($fields as $fieldset => $fields): ?]
          [?php $id = sfInflector::tableize($fieldset); ?]
          <li[?php if ($id == $currentTab || is_null($currentTab)) echo ' class="selected"'; if (is_null($currentTab)) $currentTab = false; ?]><a href="#[?php echo $fieldset ?]"><em id="[?php echo $id ?]">[?php echo $fieldset == 'NONE' ? ucwords(sfInflector::humanize(sfInflector::tableize($form->getModelName()))):$fieldset ?]</em></a></li>
        [?php endforeach; ?]

        [?php foreach ($form as $key => $value): ?]
          [?php if ($value instanceof sfFormFieldSchema): ?]
            [?php $id = sfInflector::tableize($key) ?]
            [?php $label = $value->getWidget()->getLabel() ? $value->getWidget()->getLabel():$key ?]
            <li[?php if ($id == $currentTab) echo ' class="selected"'; ?]><a href="#[?php echo $key ?]"><em id="[?php echo $id ?]">[?php echo $label ?]</em></a></li>
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
                [?php echo include_partial('<?php echo $this->getModuleName() ?>/render_embedded_form', array('name' => $key, 'form' => $embeddedForms[$key], 'embeddedForm' => $value)) ?]
              </p>
            </div>
          [?php endif; ?]
        [?php endforeach; ?]
      </div>
    </div>

    [?php use_sympal_yui_js('connection/connection') ?]

    <script type="text/javascript">
    (function() {
      var tabView = new YAHOO.widget.TabView('sympal_admin_gen_tab_view');
      tabView.addListener('click', handleClick);
      function handleClick(e)
      {
        var url = '[?php echo url_for('@sympal_save_form_tab_view_current_tab?name='.$this->getModuleName().'&id=') ?]'+e.target.id;
        YAHOO.util.Connect.asyncRequest('GET', url);
      }
    })();
    </script>

    [?php include_partial('<?php echo $this->getModuleName() ?>/form_actions', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?]
  </form>
</div>