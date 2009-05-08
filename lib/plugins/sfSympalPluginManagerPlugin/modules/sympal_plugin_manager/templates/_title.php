<?php use_helper('Text') ?>

<?php if ($plugin->getAuthorName()): ?>
  <div class="author">
    Author: <?php echo mail_to($plugin->getAuthorEmail(), $plugin->getAuthorName()) ?>
  </div>
<?php endif; ?>

<strong><?php echo link_to($plugin->getTitle(), $plugin->getRoute()) ?></strong>
<p><?php echo truncate_text(strip_tags($plugin->getDescription()), 200) ?></p>
<?php echo get_partial('sympal_plugin_manager/actions', array('plugin' => $plugin)) ?>