<h1>New Sympal Site</h1>

<p>
  You have successfully created a new Site but no content could be found.
  <?php if ($sf_user->isAuthenticated()): ?>
    Go to your <?php echo link_to('Dashboard', '@sympal_dashboard') ?> to get started working with Sympal.
  <?php endif; ?>
</p>