<?php if ($sf_sympal_site->slug != sfConfig::get('sf_app')): ?>
  <?php echo sympal_link_to_site($sf_sympal_site->slug, 'Open', 'admin/sites') ?>
<?php endif; ?>