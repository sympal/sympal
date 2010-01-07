<?php sympal_use_jquery() ?>
<script type="text/javascript" src="<?php echo javascript_path('/sfSympalFrontendEditorPlugin/js/links.js') ?>"></script>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo stylesheet_path('/sfSympalFrontendEditorPlugin/css/links.css') ?>" />

<div id="sympal_links_container">
  <h1>Link Browser</h1>

  <p>
    Browse your content below and insert links into the currently focused editor by
    just clicking the page you want to link to. You can control where the link is 
    inserted by positioning the cursor in the editor.
  </p>

  <div id="content_types">
    <h2>Content Types</h2>
    <ul id="content_types">
      <?php foreach ($contentTypes as $type): ?>
        <li>
          <?php if ($type === $contentType): ?>
            <strong><?php echo $type->getLabel() ?></strong>
          <?php else: ?>
            <?php echo jq_link_to_remote($type->getLabel(), array(
              'url' => url_for('@sympal_editor_links?content_type_id='.$type->getId()),
              'update' => 'sympal_links_container'
            )) ?>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div id="links">
    <h2><?php echo $contentType->getLabel() ?> Links</h2>
    <ul>
      <?php foreach ($content as $c): ?>
        <?php $menuItem = $c->getMenuItem() ?>    
        <li id="<?php echo $c->getId() ?>"<?php if ($menuItem): ?> style="margin-left: <?php echo ($menuItem->getLevel() - 1) * 15 ?>px;"<?php endif; ?>>
          <?php if ($menuItem->getNode()->isLeaf()): ?>
            <?php echo image_tag('/sfSympalPlugin/images/page.png') ?>
          <?php else: ?>
            <?php echo image_tag('/sfSympalPlugin/images/folder.png') ?>
          <?php endif; ?>

          <a href="#link"><?php echo $c ?></a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  
  <a class="sympal_close_menu">close</a>
</div>