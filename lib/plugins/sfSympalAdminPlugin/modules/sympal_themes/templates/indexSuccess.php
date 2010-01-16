<h1><?php echo __('Themes') ?></h1>

<p><?php echo __('Preview the available themes in this Sympal project. Click a 
theme name below to see what it looks like!') ?></p>

<ul>
  <?php foreach ($themes as $name => $theme): ?>
    <li><?php echo link_to(sfInflector::humanize($name), '@sympal_themes_preview?preview='.$name) ?></li>
  <?php endforeach; ?>
</ul>