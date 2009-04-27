<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <?php include_http_metas() ?>
  <?php include_metas() ?>
  <?php include_title() ?>
</head>

<body class="yui-skin-sam">

  <?php echo get_sympal_ui() ?>

  <div id="container">
   <!-- header -->
   <div id="header">
    <div id="logo"><?php echo link_to(image_tag('/sfSympalPlugin/images/spacer.gif'), '@homepage', 'id=logo_spacer') ?></div>

    <!-- top navigation -->
    <div id="top_navigation">

     <div class="top_navigation_head"></div>
     <div class="top_navigation_body">
       <?php $menus = get_sympal_split_menus('primary', false, 6, true) ?>
       <?php echo $menus['primary'] ?>

       <?php $menus['secondary']->callRecursively('showChildren', true) ?>
       <?php if ($secondary = (string) $menus['secondary']): ?>
         <?php slot('sympal_right_sidebar', $secondary.get_slot('sympal_right_sidebar')) ?>
       <?php endif; ?>
     
     </div>
    </div>
    <!-- end top navigation -->

   </div>
   <!-- end header -->

   <!-- content -->
   <div id="content">

    <?php echo get_sympal_flash() ?>

    <!-- left column -->
    <div id="column_left">
      <?php echo $sf_content ?>
    </div>
    <!-- end left column -->

    <?php $subMenu = get_sympal_menu(sfSympalToolkit::getCurrentMenuItem(), true) ?>
    <?php if (has_slot('sympal_right_sidebar') || $subMenu): ?>
      <?php use_stylesheet('/sfSympalPlugin/css/right.css') ?>
      <!-- right column -->
      <div id="column_right">
       <br />
       <div class="roundedbox">
        <div class="roundedbox_head"><div></div></div>
        <div class="roundedbox_body">
          <?php echo get_slot('sympal_right_sidebar') ?>

          <?php if ($subMenu): ?>
            <div id="sympal_sub_menu">
              <?php echo $subMenu ?>
            </div>
          <?php endif; ?>
        </div>
       </div>
      </div>
      <!-- end right column -->
    <?php endif; ?>

    <br style="clear: both;" />

   </div>
   <!-- end content -->

   <!-- box_footer -->
   <div id="box_footer">
   </div>
   <!-- end box_footer -->
   </div>

   <!-- footer -->
   <div id="footer">
    <p>
      Brought to you by <?php echo link_to(image_tag('/sfSympalPlugin/images/sensio_labs_button.gif'), 'http://www.sensiolabs.com', 'target=_BLANK') ?> 
      and <?php echo link_to('centre{source}', 'http://www.centresource.com', 'target=_BLANK') ?><br/>
      Powered by <?php echo link_to(image_tag('/sfSympalPlugin/images/symfony_button.gif'), 'http://www.symfony-project.org', 'target=_BLANK') ?> 
      and <?php echo link_to(image_tag('/sfSympalPlugin/images/doctrine_button.gif'), 'http://www.doctrine-project.org', 'target=_BLANK') ?>
    </p>
    <?php echo get_sympal_menu('footer') ?>
   </div>
   <!-- end footer -->

   <?php echo get_sympal_editor() ?>
 </body>
</html>