<?php echo get_sympal_breadcrumbs(array('Home' => '@homepage', 'Create New Content' => null)) ?>

<div id="create_new_content">
  <h1>Create New Content</h2>

  <p>Below you will find a list of the available Sympal content types.</p>

  <ul>
    <?php foreach ($contentTypes as $contentType): ?>
      <li>
        <h2>
          <?php echo $contentType['label'] ?>
          <?php echo link_to(image_tag('/sf/sf_admin/images/add.png').' Add New', '@sympal_content_create_type?type='.$contentType['slug']) ?>
          <?php echo link_to(image_tag('/sf/sf_admin/images/list.png').' List', '@sympal_content') ?>
          <?php echo link_to(image_tag('/sf/sf_admin/images/edit.png').' Edit', '@sympal_content_types_edit?id='.$contentType['id']) ?>
        </h2>
        <p><?php echo $contentType['description'] ?></p>
      </li>
    <?php endforeach; ?>
  </ul>
</div>