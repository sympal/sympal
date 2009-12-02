[?php include_stylesheets_for_form($form) ?]
[?php include_javascripts_for_form($form) ?]

<div class="yui-skin-sam">
  <div id="test" class="sf_admin_filter">
    <div class="hd">Sympal Editor Panel</div>
    <div class="bd">
      [?php if ($form->hasGlobalErrors()): ?]
        [?php echo $form->renderGlobalErrors() ?]
      [?php endif; ?]

      <form action="[?php echo url_for('<?php echo $this->getUrlForAction('collection') ?>', array('action' => 'filter')) ?]" method="post">
        <table cellspacing="0">
          <tfoot>
            <tr>
              <td colspan="2">
                [?php echo $form->renderHiddenFields() ?]
                [?php echo link_to(__('Reset', array(), 'sf_admin'), '<?php echo $this->getUrlForAction('collection') ?>', array('action' => 'filter'), array('query_string' => '_reset', 'method' => 'post')) ?]
                <input type="submit" value="[?php echo __('Filter', array(), 'sf_admin') ?]" />
              </td>
            </tr>
          </tfoot>
          <tbody>
            [?php foreach ($configuration->getFormFilterFields($form) as $name => $field): ?]
            [?php if ((isset($form[$name]) && $form[$name]->isHidden()) || (!isset($form[$name]) && $field->isReal())) continue ?]
              [?php include_partial('<?php echo $this->getModuleName() ?>/filters_field', array(
                'name'       => $name,
                'attributes' => $field->getConfig('attributes', array()),
                'label'      => $field->getConfig('label'),
                'help'       => $field->getConfig('help'),
                'form'       => $form,
                'field'      => $field,
                'class'      => 'sf_admin_form_row sf_admin_'.strtolower($field->getType()).' sf_admin_filter_field_'.$name,
              )) ?]
            [?php endforeach; ?]
          </tbody>
        </table>
      </form>
    </div>
  </div>
</div>

[?php use_stylesheet('/sfSympalPlugin/css/editor') ?]

[?php use_sympal_yui_css('container/assets/skins/sam/container') ?]

[?php use_sympal_yui_js('yahoo-dom-event/yahoo-dom-event') ?]

[?php use_sympal_yui_js('dragdrop/dragdrop') ?]
[?php use_sympal_yui_js('container/container') ?]
[?php use_sympal_yui_js('connection/connection') ?]

[?php use_sympal_yui_css('assets/skins/sam/skin') ?]
[?php use_sympal_yui_js('yahoo-dom-event/yahoo-dom-event') ?]
[?php use_sympal_yui_js('element/element') ?]
[?php use_sympal_yui_js('container/container_core') ?]
[?php use_sympal_yui_js('menu/menu') ?]
[?php use_sympal_yui_js('button/button') ?]
[?php use_sympal_yui_js('editor/editor') ?]

[?php use_sympal_yui_css('resize/assets/skins/sam/resize') ?]
[?php use_sympal_yui_js('utilities/utilities') ?]
[?php use_sympal_yui_js('resize/resize') ?]

[?php use_javascript('/sfSympalPlugin/js/bubbling/dispatcher/dispatcher-min') ?]

[?php use_sympal_yui_js('animation/animation') ?]

<script>
myPanel = new YAHOO.widget.Panel('test', {
	underlay:"shadow",
	width:"400px",
	x:800,
	close:true,
	visible:true,
	draggable:true,
	context:['sf_admin_bar', 'tr', 'tl']} );

myPanel.setHeader('Filters');
myPanel.render();
myPanel.hide();

YAHOO.util.Event.addListener("filters", "click", myPanel.show, myPanel, true);
</script>