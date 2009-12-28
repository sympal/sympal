<?php if ($menu = $menu->render()): ?>
  <div id="sympal_editor">
    <h2>Sympal Editor</h2>

    <a class="sympal_editor_button sympal_sitemap" href="<?php echo url_for('@sympal_editor_sitemap', 'absolute=true') ?>">Sitemap</a>

    <?php if ($sf_request->getParameter('module') != 'sympal_dashboard'): ?>
      <?php echo button_to('My Dashboard', '@sympal_dashboard', 'class="sympal_editor_button"') ?>
    <?php endif; ?>

    <?php if ($sf_request->getParameter('module') != 'sympal_content_renderer'): ?>
      <?php echo button_to('Go to Site', '@homepage', 'class="sympal_editor_button"') ?>
    <?php endif; ?>

    <?php echo button_to('Signout', '@sympal_signout', 'class=sympal_editor_button confirm=Are you sure you wish to signout?') ?>

    <?php echo $menu ?>
    
    Viewing <?php echo $content->getType()->getLabel() ?> titled <strong>"<?php echo $content ?>"</strong> 
    created by <strong><?php echo $content->getAuthorName() ?></strong> on <strong><?php echo format_datetime($content->getDatePublished()) ?></strong>.
  </div>
<?php endif; ?>