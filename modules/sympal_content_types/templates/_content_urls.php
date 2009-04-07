<?php $routes = $form->getObject()->getRoutes() ?>
<?php if (count($routes) > 0): ?>
  <div class="sf_admin_form_row sf_admin_text sf_admin_form_field_content_urls">
    <h2>History</h2>

    <ul>
      <?php foreach ($routes as $key => $route): ?>
        <li>
          <?php echo link_to(image_tag('/sf/sf_admin/images/delete.png'), '@sympal_content_delete_route?id='.$route['id']) ?> 
          <?php echo $route['url'] ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>