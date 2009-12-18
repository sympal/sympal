<h2><?php echo __('Oops! It appears this page is currently disabled!') ?></h2>

<p><?php echo sfContext::getInstance()->getRequest()->getUri() ?></p>

<p><input type="button" name="back" value="<?php echo __('Go Back') ?>" onClick="javascript: history.go(-1);" /></p>