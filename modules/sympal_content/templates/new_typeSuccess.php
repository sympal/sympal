<h2>Create New Content</h2>

<p>Choose the type of content you wish to create. Clicking each content type will bring up the form to create a new content for that type.</p>

<ul>
  <?php foreach ($contentTypes as $contentType): ?>
    <li><?php echo link_to($contentType['label'], '@sympal_content_create_type?type='.$contentType['slug']) ?></li>
  <?php endforeach; ?>
</ul>