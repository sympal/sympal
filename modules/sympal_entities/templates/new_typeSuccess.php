<h2>Create New Entity</h2>

<p>Choose the type of entity you wish to create. Clicking each entity type will bring up the form to create a new entity record of that type.</p>

<ul>
  <?php foreach ($entityTypes as $entityType): ?>
    <li><?php echo link_to($entityType['label'], '@sympal_entities_create_type?type='.$entityType['slug']) ?></li>
  <?php endforeach; ?>
</ul>