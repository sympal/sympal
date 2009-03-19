<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <?php include_http_metas() ?>
  <?php include_metas() ?>
  <?php include_title() ?>
</head>

<body>

  <?php echo get_sympal_admin_bar() ?>

  <div id="container">
   <!-- header -->
   <div id="header">
    <div id="logo"><?php echo link_to(image_tag('/sfSympalPlugin/images/spacer.gif'), '@homepage', 'id=logo_spacer') ?></div>

    <!-- top navigation -->
    <div id="top_navigation">

     <div class="top_navigation_head"></div>
     <div class="top_navigation_body">
       <?php echo get_sympal_menu('primary') ?>
     </div>
    </div>
    <!-- end top navigation -->

   </div>
   <!-- end header -->

   <!-- content -->
   <div id="content">

    <!-- left column -->
    <div id="column_left">
      <?php echo $sf_content ?>
    </div>
    <!-- end left column -->

    <?php if (has_slot('right_sidebar')): ?>
      <?php use_stylesheet('/sfSympalPlugin/css/right.css') ?>
      <!-- right column -->
      <div id="column_right">
       <br />
       <div class="roundedbox">
        <div class="roundedbox_head"><div></div></div>
        <div class="roundedbox_body">
         <?php echo get_slot('right_sidebar') ?>
        </div>
       </div>
      </div>
      <!-- end right column -->
    <?php endif; ?>

    <br style="clear: both;" />

   </div>
   <!-- end content -->

   <!-- footer -->
   <div id="footer">
   </div>
   <!-- end footer -->
  </div>

   <!-- trademark -->
   <div id="trademark">
    <p>&copy;Copyright 2008 SympalPHP</p>
   </div>
   <!-- end trademark -->

   <?php echo get_sympal_editor() ?>
 </body>
</html>


<?php /*
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
</head>

<body id="sympal">

  <?php echo get_sympal_admin_bar() ?>

  <div id="doc3" class="yui-t1">
    <div id="hd">
      <div>
        <?php if ($entity = sfSympalTools::getCurrentEntity()): ?>
          <h1><?php echo link_to($entity->Site->title, '@sympal_homepage') ?></h1>
          <h2><?php echo $entity->Site->description ?></h2>
        <?php else: ?>
          <h1><?php echo link_to('Sympal', '@sympal_homepage') ?></h1>
          <h2>Flexible content management framework built on top of symfony.</h2>
        <?php endif; ?>
      </div>
    </div>
    <div id="bd">
  	  <div id="yui-main">
  	    <div class="yui-b">
  	      <div class="yui-g">
  	        <?php if ($sf_user->hasFlash('notice')): ?>
              <div class="notice"><?php echo $sf_user->getFlash('notice') ?></div>
            <?php endif; ?>

            <?php if ($sf_user->hasFlash('error')): ?>
              <div class="error"><?php echo $sf_user->getFlash('error') ?></div>
            <?php endif; ?>

            <div id="sympal_content">
  	          <?php echo $sf_content ?>
  	        </div>
  	      </div>
        </div>
  	  </div>
	    <div class="yui-b" id="sympal_primary_menu">
	      <?php echo get_sympal_menu('primary') ?>
	    </div>
  	</div>
    <div id="ft">
      <div id="copyright">
        Copyright 2008-<?php echo date('Y') ?> Sympal
      </div>
      <div id="sympal_footer_menu">
        <?php echo get_sympal_menu('footer') ?>
      </div>
    </div>
  </div>

  <?php echo get_sympal_editor() ?>
</body>
</html>
*/ ?>