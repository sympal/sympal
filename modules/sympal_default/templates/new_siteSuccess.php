<h1><?php echo __('New %1% Site', array('%1%' => sfSympalConfig::get('sympal_name'))) ?></h1>

<p>
  <?php echo __('You have successfully created a new Site but no content could be found.') ?>
  <?php if ($sf_user->isAuthenticated()): ?>
    <?php echo __('Go to your %1% to begin building out the content for your site!', array(
      '%1%' => link_to('Dashboard', '@sympal_dashboard'))
    ) ?>
  <?php else: ?>
    <?php echo __('%1% to begin building out the content for your site!', array(
      '%1%' => link_to('Signin', '@sympal_signin')
    )) ?>
  <?php endif; ?>
</p>