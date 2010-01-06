<div id="sympal_server_check">
  <?php echo $renderer->render() ?>
</div>

<iframe src="<?php echo url_for('@sympal_phpinfo') ?>" id="phpinfo_frame" width="100%" height="1000" style="border: 0;"></iframe>