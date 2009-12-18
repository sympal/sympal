<?php if ($site->slug != sfConfig::get('sf_app')): ?>
  <?php echo sympal_link_to_site($site->slug, 'Open', 'admin/sites') ?>
<?php endif; ?>