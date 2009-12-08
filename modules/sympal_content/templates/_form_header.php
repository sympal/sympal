<div id="toggle_menu_tab">
  <?php $url = $form->isNew() ? url_for('@sympal_content_create_type?type='.$sf_request->getParameter('type')) : url_for('@sympal_content_edit?id='.$form->getObject()->getId()) ?>
  <?php if ($sf_request->getParameter('menu')): ?>
    <a href="#" onClick="javascript: document.getElementById('save').value = '0'; document.getElementById('menu').value = '0'; document.getElementById('sympal_content_form').submit();">- Hide Menu Tab</a>
  <?php else: ?>
    <a href="#" onClick="javascript: document.getElementById('save').value = '0'; document.getElementById('menu').value = '1'; document.getElementById('sympal_content_form').submit();">+ Show Menu Tab</a>
  <?php endif; ?>
</div>