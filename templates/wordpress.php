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
  	<div id="header">
  	  <h1><?php echo link_to($sf_sympal_context->getSite()->getTitle(), '@sympal_homepage') ?></h1>
  	  <h2><?php echo $sf_sympal_context->getSite()->getDescription() ?></h2>
  	</div>
  	<div id="content">
      <div id="left">
        <?php echo $sf_content ?>
      </div>

  	  <?php $menu = get_sympal_menu('primary') ?>
    	<div id="sidebar">
        <?php if (has_slot('sympal_right_sidebar')): ?>
          <?php echo get_slot('sympal_right_sidebar') ?>
        <?php endif; ?>

        <h2>Navigation</h2>

    	  <?php if ($menu): ?>
          <?php echo $menu ?>
        <?php else: ?>
          <ul>
            <?php if (!$sf_user->isAuthenticated()): ?>
              <li><?php echo link_to('Register', '@sympal_register') ?></li>
              <li><?php echo link_to('Signin', '@sympal_signin') ?></li>
            <?php else: ?>
              <li><?php echo link_to('Signout', '@sympal_signout', 'confirm=Are you sure?') ?></li>
            <?php endif; ?>
          </ul>
        <?php endif; ?>
    	</div>
  	</div>
  	<div id="footer">
  	  <p>
  	    This website is proudly powered by <?php echo link_to('Sympal', 'http://www.sympalphp.org', 'target=_BLANK') ?><br/>
  	    Wordpress Default Theme
  	  </p>
  	</div>
  </div>

</body>
</html>