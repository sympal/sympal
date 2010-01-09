<h1>Themes</h1>

<p>
  Click a theme to preview it.
</p>

<ul>
  <?php foreach ($themes as $name => $theme): ?>
    <li><?php echo link_to(sfInflector::humanize($name), '@sympal_themes_preview?preview='.$name) ?></li>
  <?php endforeach; ?>
</ul>