<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php $ui = get_sympal_ui() ?>
    <?php $editor = get_sympal_editor() ?>
    <?php $flash = get_sympal_flash() ?>
    <?php $menu = get_sympal_menu('primary') ?>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="/favicon.ico" />
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
  </head>
  <body class="yui-skin-sam">
    <?php echo $ui ?>

    <?php echo $menu ?>

    <?php echo $flash ?>

    <?php echo $sf_content ?>
    
    <?php echo $editor ?>
  </body>
</html>