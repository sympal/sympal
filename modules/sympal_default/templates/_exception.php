<?php use_stylesheet('/sfSympalPlugin/css/exception.css') ?>

<div class="sympal_exception">
  <h2><?php echo get_class($e) ?>: <?php echo $e->getMessage() ?></h2>

  <?php $lines = explode("\n", $e->getTraceAsString()) ?>
  <ul>
    <?php foreach ($lines as $line): ?>
      <li><?php echo $line ?></li>
    <?php endforeach; ?>
  </ul>
</div>