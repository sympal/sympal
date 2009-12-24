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
      $('#toggle_edit_mode').click();
    });
  });
</script>