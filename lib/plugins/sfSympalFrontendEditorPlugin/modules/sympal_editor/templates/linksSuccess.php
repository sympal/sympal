<div id="sympal_links_container">
  <script type="text/javascript" src="<?php echo javascript_path('/sfSympalFrontendEditorPlugin/js/links.js') ?>"></script>

  <h2>Links</h2>

  <ul>
    <?php foreach ($content as $c): ?>
      <li id="<?php echo $c->getId() ?>" style="margin-left: <?php echo ($c->getMenuItem()->getLevel() - 1) * 15 ?>px;">
        <a href="#link"><?php echo $c ?></a>
      </li>
    <?php endforeach; ?>
  </ul>

</div>