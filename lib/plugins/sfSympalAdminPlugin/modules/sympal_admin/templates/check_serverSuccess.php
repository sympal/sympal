<h1><?php echo __('Sympal Server Check') ?></h1>

<?php echo get_sympal_breadcrumbs(array(
  'Dashboard' => '@sympal_dashboard',
  'Global Setup' => '@sympal_sites',
  'Check Server' => '@sympal_server_check',
)) ?>

<div id="sympal_server_check">
  <?php echo $renderer->render() ?>
</div>

<iframe src="<?php echo url_for('@sympal_phpinfo') ?>" id="phpinfo_frame" width="100%" height="1000" style="border: 0;"></iframe>