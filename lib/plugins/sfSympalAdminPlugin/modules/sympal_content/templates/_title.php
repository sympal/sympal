<?php $padding = null; ?>
<?php if ($menuItem = $sf_sympal_content->getMenuItem()): ?>
  <?php echo $padding = str_repeat(' &nbsp; &nbsp; ', $menuItem->getLevel() > 0 ? ($menuItem->getLevel() - 1) : 0) ?>
  <?php echo image_tag('/sfSympalPlugin/images/'.($menuItem->getNode()->isLeaf() ? 'page' : 'folder').'.png') ?>
<?php else: ?>
  <?php $padding = ' &nbsp; &nbsp; ' ?>
  <?php echo image_tag('/sfSympalPlugin/images/page.png') ?>
<?php endif; ?>

<?php echo link_to($sf_sympal_content, $sf_sympal_content->getRoute()) ?>

<br/>

<?php echo $padding ?>
<small><?php echo link_to($sf_sympal_content->getEvaluatedRoutePath(), $sf_sympal_content->getRoute()) ?></small>