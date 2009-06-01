[?php include_stylesheets_for_form($form) ?]
[?php include_javascripts_for_form($form) ?]

[?php use_sympal_yui_css('tabview/assets/skins/sam/tabview') ?]

[?php use_sympal_yui_js('yahoo-dom-event/yahoo-dom-event') ?]
[?php use_sympal_yui_js('element/element') ?]
[?php use_sympal_yui_js('connection/connection') ?]
[?php use_sympal_yui_js('tabview/tabview') ?]

<div class="sf_admin_form sympal_form">
  [?php echo $helper->getSymfonyResource('<?php echo $this->getModuleName() ?>', 'outside_form_header', array('form' => $form)) ?>
  [?php echo form_tag_for($form, '@<?php echo $this->params['route_prefix'] ?>') ?]
  [?php echo $helper->getSymfonyResource('<?php echo $this->getModuleName() ?>', 'inside_form_header', array('form' => $form)) ?>
    [?php echo $form->renderHiddenFields() ?]

    [?php if ($form->hasGlobalErrors()): ?]
      [?php echo $form->renderGlobalErrors() ?]
    [?php endif; ?]

    [?php $fields = $configuration->getFormFields($form, $form->isNew() ? 'new' : 'edit') ?]
    [?php $currentTab = $sf_user->getAttribute('<?php echo $this->getModuleName() ?>.current_form_tab', null, 'admin_module'); ?]
    <div id="sympal_admin_gen_tab_view" class="yui-navset">
      <ul class="yui-nav">
        [?php foreach ($fields as $fieldset => $f): ?]
          [?php $id = sfInflector::tableize($fieldset); ?]
          <li[?php if ($id == $currentTab || is_null($currentTab)) echo ' class="selected"'; if (is_null($currentTab)) $currentTab = false; ?]><a href="#[?php echo $fieldset ?]"><em id="[?php echo $id ?]">[?php echo __($fieldset == 'NONE' ? ucwords(sfInflector::humanize(sfInflector::tableize($form->getAdminGenMainTabLabel()))):$fieldset, array(), '<?php echo $this->getI18nCatalogue() ?>') ?]</em></a></li>
        [?php endforeach; ?]

        [?php foreach ($form as $key => $value): ?]
          [?php if ($value instanceof sfFormFieldSchema): ?]
            [?php $id = sfInflector::tableize($key) ?]
            [?php $label = $value->getWidget()->getLabel() ? $value->getWidget()->getLabel():$key ?]
            <li[?php if ($id == $currentTab) echo ' class="selected"'; ?]><a href="#[?php echo $key ?]"><em id="[?php echo $id ?]">[?php echo __($label, array(), '<?php echo $this->getI18nCatalogue() ?>') ?]</em></a></li>
          [?php endif; ?]
        [?php endforeach; ?]

        [?php $tabs = array() ?]
        [?php $tabs = sfApplicationConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($this, '<?php echo $this->getModuleName() ?>.extra_tabs'), $tabs)->getReturnValue() ?]
        [?php foreach ($tabs as $tab => $resource): ?]
          <li[?php if ($tab == $currentTab) echo ' class="selected"'; ?]><a href="#[?php echo $tab ?]"><em id="[?php echo $tab ?]">[?php echo $tab ?]</em></a></li>
        [?php endforeach; ?]
      </ul>

      <div class="yui-content">
        [?php foreach ($fields as $fieldset => $fields): ?]
          <div id="[?php echo $fieldset ?]">
            <p>
              [?php echo $helper->getTabExtras($fieldset, 'header', array('form' => $form)) ?]
              [?php echo $helper->getSymfonyResource('<?php echo $this->getModuleName() ?>', $fieldset.'_tab_header', array('form' => $form)) ?]
              [?php include_partial('<?php echo $this->getModuleName() ?>/form_fieldset', array('content' => '', 'form' => $form, 'fields' => $fields, 'fieldset' => $fieldset)) ?]
              [?php echo $helper->getSymfonyResource('<?php echo $this->getModuleName() ?>', $fieldset.'_tab_footer', array('form' => $form)) ?]
              [?php echo $helper->getTabExtras($fieldset, 'footer', array('form' => $form)) ?]
            </p>
          </div>
        [?php endforeach; ?]

        [?php $embeddedForms = $form->getEmbeddedForms() ?]
        [?php foreach ($form as $key => $value): ?]
          [?php if ($value instanceof sfFormFieldSchema): ?]
            <div id="[?php echo $key ?]">
              <p>
                [?php echo $helper->getTabExtras($key, 'header', array('form' => $form)) ?]
                [?php echo $helper->getSymfonyResource('<?php echo $this->getModuleName() ?>', $key.'_tab_header', array('form' => $form)) ?]
                [?php echo include_partial('<?php echo $this->getModuleName() ?>/render_embedded_form', array('name' => $key, 'form' => $embeddedForms[$key], 'embeddedForm' => $value)) ?]
                [?php echo $helper->getSymfonyResource('<?php echo $this->getModuleName() ?>', $key.'_tab_footer', array('form' => $form)) ?]
                [?php echo $helper->getTabExtras($key, 'footer', array('form' => $form)) ?]
              </p>
            </div>
          [?php endif; ?]
        [?php endforeach; ?]

        [?php foreach ($tabs as $tab => $resource): ?]
          <div id="[?php echo $tab ?]">
            <p>
              [?php echo $helper->getTabExtras($tab, 'header', array('form' => $form)) ?]
              [?php echo $helper->getSymfonyResource('<?php echo $this->getModuleName() ?>', $tab.'_tab_header', array('form' => $form)) ?]
              [?php $e = explode('/', $resource) ?]
              [?php echo $helper->getSymfonyResource($e[0], $e[1], array('form' => $form)) ?]
              [?php echo $helper->getSymfonyResource('<?php echo $this->getModuleName() ?>', $tab.'_tab_footer', array('form' => $form)) ?]
              [?php echo $helper->getTabExtras($tab, 'footer', array('form' => $form)) ?]
            </p>
          </div>
        [?php endforeach; ?]
      </div>
    </div>

    [?php use_sympal_yui_js('connection/connection') ?]

    <script type="text/javascript">
    (function() {
      var tabView = new YAHOO.widget.TabView('sympal_admin_gen_tab_view');
      tabView.addListener('click', handleClick);
    })();
    function handleClick(e)
    {
      if (e.target.toString() == '[object HTMLSpanElement]')
      {
        var url = '[?php echo url_for('@sympal_save_form_tab_view_current_tab?name='.$this->getModuleName().'&id=') ?]'+e.target.id;
        YAHOO.util.Connect.asyncRequest('GET', url);
      }
    }
    </script>

    [?php echo $helper->getSymfonyResource('<?php echo $this->getModuleName() ?>', 'inside_form_footer', array('form' => $form)) ?>

    [?php include_partial('<?php echo $this->getModuleName() ?>/form_actions', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?]
  </form>
  [?php echo $helper->getSymfonyResource('<?php echo $this->getModuleName() ?>', 'outside_form_footer', array('form' => $form)) ?>
</div>