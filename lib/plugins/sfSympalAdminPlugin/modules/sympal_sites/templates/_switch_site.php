<?php if ($sf_sympal_site->slug != sfConfig::get('sf_app')): ?>
  <?php echo sympal_link_to_site($sf_sympal_site->slug, image_tag('/sfSympalPlugin/images/go_icon.gif'), 'admin/sites') ?>
<?php endif; ?>