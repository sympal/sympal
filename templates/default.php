<?php $cmfInstalled = $sf_sympal_context->getSympalConfiguration()->pluginExists('sfSympalCMFPlugin') ?>
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
  <div id="container">
  	<div id="header">
  	  <h1><?php echo link_to($cmfInstalled ? $sf_sympal_site->getTitle() : 'Homepage', '@homepage') ?></h1>
  	  <h2><?php echo $cmfInstalled ? $sf_sympal_site->getDescription() : 'Site Description...' ?></h2>
  	</div>
  	<div id="content">
      <?php echo $sf_content ?>
  	</div>
  	<?php $menu = $cmfInstalled ? get_sympal_menu('primary') : false; ?>
  	<div id="sidebar">
      <?php if ($cmfInstalled): ?>
        <h2><?php echo __('Search') ?></h2>
        <?php echo get_partial('sympal_search/form') ?>
      <?php endif; ?>

      <?php if (has_slot('sympal_right_sidebar')): ?>
        <?php echo get_slot('sympal_right_sidebar') ?>
      <?php endif; ?>

      <h2><?php echo __('Navigation') ?></h2>

  	  <?php if ($menu): ?>
        <?php echo $menu->render() ?>
      <?php else: ?>
        <ul>
          <?php if ($cmfInstalled): ?>
            <?php if (!$sf_user->isAuthenticated()): ?>
              <li><?php echo link_to(__('Register'), '@sympal_register') ?></li>
              <li><?php echo link_to(__('Signin'), '@sympal_signin') ?></li>
            <?php else: ?>
              <li><?php echo link_to(__('Signout'), '@sympal_signout', 'confirm='.__('Are you sure?')) ?></li>
            <?php endif; ?>
          <?php else: ?>
            <li><a href="http://symfony-project.org">symfony</a></li>
            <li><a href="http://sympalphp.org">Sympal</a></li>
          <?php endif; ?>
        </ul>
      <?php endif; ?>
  	</div>
  	<div id="footer"></div>
  </div>
</body>
</html>