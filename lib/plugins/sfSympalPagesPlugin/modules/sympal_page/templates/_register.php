<?php echo get_sympal_breadcrumbs($menuItem, $content) ?>

<?php if ($sf_user->isAuthenticated()): ?>
  <p>You are already registered.</p>
<?php else: ?>
  <h1><?php echo get_sympal_column_content_slot($content, 'title') ?></h1>

  <?php echo get_sympal_content_slot($content, 'body') ?>

  <?php echo get_component('sfGuardRegister', 'form') ?>
<?php endif; ?>