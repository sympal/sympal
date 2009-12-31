<div id="sympal_links_container">
  <script type="text/javascript" src="<?php echo javascript_path('/sfSympalFrontendEditorPlugin/js/links.js') ?>"></script>

  <h2>Links</h2>

  <ul>
    <?php foreach ($content as $c): ?>
      <?php $menuItem = $c->getMenuItem() ?>    
      <li id="<?php echo $c->getId() ?>"<?php if ($menuItem): ?> style="margin-left: <?php echo ($menuItem->getLevel() - 1) * 15 ?>px;"<?php endif; ?>>
        <a href="#link"><?php echo $c ?></a>
      </li>
    <?php endforeach; ?>
  </ul>

</div>