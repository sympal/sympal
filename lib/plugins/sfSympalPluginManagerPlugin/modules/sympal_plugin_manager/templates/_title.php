<?php use_helper('Text') ?>

<?php if ($sf_sympal_plugin->getAuthorName()): ?>
  <div class="author">
    Author: <?php echo mail_to($sf_sympal_plugin->getAuthorEmail(), $sf_sympal_plugin->getAuthorName()) ?>
  </div>
<?php endif; ?>

<strong><?php echo link_to($sf_sympal_plugin->getTitle(), $sf_sympal_plugin->getRoute()) ?></strong>
<p><?php echo truncate_text(strip_tags($sf_sympal_plugin->getDescription()), 200) ?></p>
<?php echo get_partial('sympal_plugin_manager/actions', array('sf_sympal_plugin' => $sf_sympal_plugin)) ?>