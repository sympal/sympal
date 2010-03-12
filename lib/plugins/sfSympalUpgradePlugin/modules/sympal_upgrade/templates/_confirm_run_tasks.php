<p><?php echo __('Continuing will execute the following Sympal upgrade tasks:') ?></p>

<ul>
  <?php foreach ($upgrades as $upgrade): ?>
    <li><?php echo $upgrade ?></li>
  <?php endforeach; ?>
</ul>

<p><?php echo __('Do you wish to continue?'); ?></p>
