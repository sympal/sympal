<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <?php include_http_metas() ?>
  <?php include_metas() ?>
  <?php include_title() ?>
  <?php sympal_minify() ?>
  <?php include_stylesheets() ?>
  <?php include_javascripts() ?>
</head>
<body>

  <div id="sympal_ajax_loading">
    Loading...
  </div>

  <div id="container">
    <div id="content">
      <?php echo get_sympal_flash() ?>
      <?php echo $sf_content ?>
    </div>

    <?php if ($sf_sympal_context->getSite() && $sf_user->isAuthenticated()): ?>
      <div id="footer">
        <p><?php echo __('Powered by %1% %2%', array('%1%' => link_to('Sympal', 'http://www.sympalphp.org', 'target=_BLANK'), '%2%' => sfSympalConfig::getCurrentVersion())) ?>.</p>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>