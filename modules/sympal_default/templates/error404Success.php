<h2><?php echo __('Oops! The page you asked for could not be found.') ?></h2>

<p><?php echo sfContext::getInstance()->getRequest()->getUri() ?></p>

<p><input type="button" name="back" value="<?php echo __('Go Back') ?>" onClick="javascript: history.go(-1);" /></p>