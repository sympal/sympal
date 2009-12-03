<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <?php $flash = get_sympal_flash() ?>
  <?php include_http_metas() ?>
  <?php include_metas() ?>
  <?php include_title() ?>
  <?php include_stylesheets() ?>
  <?php include_javascripts() ?>
</head>
<body>
  <div id="sympal_install">
    <?php echo $flash ?>
    <?php echo $sf_content ?>
  </div>
</body>
</html>