<?php if ($sf_sympal_menu_item->getIsPublished()): ?>
  <?php echo image_tag('/sfSympalPlugin/images/published.png', 'title='.__('Published on %date%', array('%date%' => format_date($sf_sympal_menu_item->getDatePublished(), 'g')))) ?>
<?php elseif ($sf_sympal_menu_item->getIsPublishedInTheFuture()): ?>
  <?php echo image_tag('/sfSympalPlugin/images/published_in_future.png', 'title='.__('Will publish on %date%', array('%date%' => format_date($sf_sympal_menu_item->getDatePublished(), 'g')))) ?>
<?php else: ?>
  <?php echo image_tag('/sfSympalPlugin/images/not_published.png', 'title='.__('Has not been published yet.')) ?>
<?php endif; ?>