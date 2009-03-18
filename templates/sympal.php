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