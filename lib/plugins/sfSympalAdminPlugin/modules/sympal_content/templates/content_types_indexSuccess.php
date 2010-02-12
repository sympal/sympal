<h1><?php echo __('Manage Content') ?></h1>

<?php echo get_sympal_breadcrumbs(array(
  'Dashboard' => '@sympal_dashboard',
  'Site Content' => '@sympal_content_types_index'
)) ?>

<div id="sf_admin_container">
  <div id="sf_admin_content">
    <div class="sf_admin_list">
      <table cellspacing="0">
        <thead>
          <tr>
            <th>Name</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($contentTypes as $contentType): ?>
            <tr>
              <td>
                <strong><?php echo link_to($contentType->getLabel(), '@sympal_content_list_type?type='.$contentType->getSlug()) ?></strong><br/>
                <small><?php echo $contentType->getDescription() ?></small>
              </td>

              <td><?php echo link_to(image_tag('/sf/sf_admin/images/add.png').' Create New', '@sympal_content_create_type?type='.$contentType->getSlug()) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>