<?php if ($menu = $menu->render()): ?>
  <?php use_helper('jQuery') ?>
  <?php use_javascript('/sfSympalPlugin/js/jQuery.cookie.js') ?>
  <?php use_javascript('/sfSympalPlugin/js/jQuery.hoverIntent.js') ?>
  <?php use_javascript('/sfSympalPlugin/js/editor.js') ?>
  <?php use_stylesheet('/sfSympalPlugin/css/editor') ?>

  <div id="sympal_toggle_editor"></div>

  <div id="sympal_editor">
    <h2>Sympal Editor</h2>

    <?php if ($sf_request->getParameter('module') != 'sympal_dashboard'): ?>
      <?php echo button_to('My Dashboard', '@sympal_dashboard', 'class="sympal_editor_button"') ?>
    <?php endif; ?>

    <?php if ($sf_request->getParameter('module') != 'sympal_content_renderer'): ?>
      <?php echo button_to('Go to Site', '@homepage', 'class="sympal_editor_button"') ?>
    <?php endif; ?>

    <?php echo button_to('Signout', '@sympal_signout', 'class=sympal_editor_button confirm=Are you sure you wish to signout?') ?>

    <?php echo $menu ?>
  </div>
<?php endif; ?>

<script type="text/javascript">
  $(function()
  {
    $('span.sympal_content_slot').dblclick(function()
    {
      // get the content_id and slot_id
      var content_id = $(this).find('.content_id').attr('value');
      var id = $(this).find('.content_slot_id').attr('value');

      // build the url
      var url = '<?php echo url_for('@sympal_edit_content_slot?content_id=CONTENT_ID&id=ID') ?>';
      url = url.replace('CONTENT_ID', content_id);
      url = url.replace('ID', id);

      var value = $(this).find('.value');
      var editor = $(this).find('.editor');
      var check = editor.find('.sympal_content_slot_editor');
      if (check.length == 0)
      {
        editor.load(url, null, function() {
          value.hide();
          editor.find('input').focus();
          editor.show();
        });
      } else {
        value.hide();
        editor.show();
      }
    });
  });
</script>