<?php $padding ?>
<?php if ($menuItem = $sf_sympal_content->getMenuItem()): ?>
  <?php echo $padding = str_repeat(' &nbsp; &nbsp; ', $menuItem->getLevel()) ?>
  <?php echo image_tag('/sfSympalPlugin/images/'.($menuItem->getNode()->isLeaf() ? 'page' : 'folder').'.png') ?>
<?php endif; ?>

<?php echo link_to($sf_sympal_content, $sf_sympal_content->getEditRoute()) ?>

<br/><?php echo $padding ?> &nbsp; &nbsp; &nbsp;<small><?php echo link_to($sf_sympal_content->getEvaluatedRoutePath(), $sf_sympal_content->getRoute()) ?></small>