<?php $url = $record->getUrl(array('absolute' => true)) ?>
<strong><?php echo $record->getType()->getLabel() ?>: <?php echo link_to($record->getTitle(), $url) ?></strong>
<p><?php echo $record->getTeaser() ?></p>
<small><?php echo link_to($url, $url) ?></small>