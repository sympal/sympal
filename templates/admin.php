<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <?php $ui = get_sympal_ui() ?>
  <?php $editor = get_sympal_editor() ?>
  <?php $flash = get_sympal_flash() ?>
  <?php include_http_metas() ?>
  <?php include_metas() ?>
  <?php include_title() ?>
  <?php include_stylesheets() ?>
  <?php include_javascripts() ?>
</head>
<body class="yui-skin-sam">

  <?php echo $ui ?>

  <div id="container">

  <!-- content -->
  <div id="content">

  <?php echo $flash ?>

  <!-- left column -->
  <div id="column_left">
    <?php echo $sf_content ?>
  </div>
  <!-- end left column -->

  </div>
  <!-- end content -->
  <br style="clear: both;" />
  </div>

  <script type="text/javascript">
   var uservoiceJsHost = ("https:" == document.location.protocol) ? "https://uservoice.com" : "http://cdn.uservoice.com";
   document.write(unescape("%3Cscript src='" + uservoiceJsHost + "/javascripts/widgets/tab.js' type='text/javascript'%3E%3C/script%3E"))
  </script>
  <script type="text/javascript">
  UserVoice.Tab.show({ 
   key: 'sympal',
   host: 'sympal.uservoice.com', 
   forum: 'general', 
   alignment: 'left',
   background_color:'#f00', 
   text_color: 'white',
   hover_color: '#06C',
   lang: '<?php echo $sf_user->getCulture() ?>'
  })
  </script>

  <?php echo $editor ?>
</body>
</html>