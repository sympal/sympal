<h2>Create New Content</h2>

<p>Choose the type of content you wish to create. Clicking each content type will bring up the form to create a new content for that type.</p>

<ul>
  <?php foreach ($entityTypes as $entityType): ?>
    <li><?php echo link_to($entityType['label'], '@sympal_entities_create_type?type='.$entityType['slug']) ?></li>
  <?php endforeach; ?>
</ul>