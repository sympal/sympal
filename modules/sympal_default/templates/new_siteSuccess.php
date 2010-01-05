<h1><?php echo __('New %1% Site', array('%1%' => __('Sympal'))) ?></h1>

<p>
  <?php echo __('You have successfully created a new Site but no content could be found.') ?>
  <?php if ($sf_user->isAuthenticated()): ?>
    <?php echo __('Go to your %1% to get started working with %2%.', array(
      '%1%' => link_to('Dashboard', '@sympal_dashboard'), '%2%' => __('Sympal'))
    ) ?>  
  <?php endif; ?>
</p>