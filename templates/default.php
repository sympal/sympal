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
  <div id="container">
  	<div id="header"><h1><?php echo link_to(sfSympalConfig::get('sympal_name'), '@sympal_homepage') ?></h1></div>
  	<div id="content">
      <?php echo $sf_content ?>
  	</div>
  	<div id="sidebar">
  	  <h2>Navigation</h2>
      <?php echo get_sympal_menu('primary') ?>
  	</div>
  	<div id="footer"></div>
  </div>
</body>
</html>