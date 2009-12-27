<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <?php include_http_metas() ?>
  <?php include_metas() ?>
  <?php include_title() ?>
  <?php include_stylesheets() ?>
  <?php include_javascripts() ?>
</head>
<body>

  <div id="sympal_ajax_loading">
    Loading...
  </div>

  <div id="container">

  <!-- content -->
  <div id="content">

  <div id="header">
    <h1>Sympal <?php echo sfSympalConfig::getCurrentVersion() ?> Admin</h1>
  </div>

  <div id="column_left">
    <?php echo get_sympal_admin_menu() ?>
  </div>

  <!-- right column -->
  <div id="column_right">
    <?php echo get_sympal_flash() ?>
    <?php echo $sf_content ?>
  </div>
  <!-- end left column -->

  </div>
  <!-- end content -->
  <br style="clear: both;" />
  </div>
</body>
</html>