<?php if ($sf_sympal_content->getIsPublished()): ?>
  <?php echo image_tag('/sfSympalPlugin/images/published.png', 'title=Published on '.format_date($sf_sympal_content->getDatePublished(), 'g')) ?>
<?php elseif ($sf_sympal_content->getIsPublishedInTheFuture()): ?>
  <?php echo image_tag('/sfSympalPlugin/images/published_in_future.png', 'title=Will publish on '.format_date($sf_sympal_content->getDatePublished(), 'g')) ?>
<?php else: ?>
  <?php echo image_tag('/sfSympalPlugin/images/not_published.png', 'title=Has not been published yet.') ?>
<?php endif; ?>