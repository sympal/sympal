<?php if (!$form->isNew()): ?>
  <div class="sf_admin_form_row sf_admin_text sf_admin_form_field_content_urls">
    <label>Content Url</label>
    <?php $url = url_for($form->getObject()->getRoute(), 'absolute=true'); echo link_to($url, $url, 'target=_BLANK') ?>
  </div>
<?php endif; ?>

<?php $routes = $form->getObject()->getRoutes() ?>
<?php if (count($routes) > 0): ?>
  <div class="sf_admin_form_row sf_admin_text sf_admin_form_field_content_urls">
    <h2>History</h2>

    <ul>
      <?php foreach ($routes as $key => $route): ?>
        <?php if ($r = $form->getObject()->getRoute($route->getRouteName(), $route['url'])): ?>
          <li>
            <?php echo link_to(image_tag('/sf/sf_admin/images/delete.png'), '@sympal_content_delete_route?id='.$route['id']) ?> 
            <?php $url = url_for('@'.$r, 'absolute=true'); echo link_to($url, $url, 'target=_BLANK'); ?>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>