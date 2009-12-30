<?php if ($menu = $menu->render()): ?>
  <div id="sympal_editor">
    <h2><?php echo __('Sympal Editor') ?></h2>

    <?php if ($sf_request->getParameter('module') != 'sympal_dashboard'): ?>
      <?php echo button_to(__('My Dashboard'), '@sympal_dashboard', 'class="sympal_editor_button"') ?>
    <?php endif; ?>

    <?php if ($sf_request->getParameter('module') != 'sympal_content_renderer'): ?>
      <?php echo button_to(__('Go to Site'), '@homepage', 'class="sympal_editor_button"') ?>
    <?php endif; ?>

    <?php echo button_to(__('Signout'), '@sympal_signout', 'class=sympal_editor_button confirm=Are you sure you wish to signout?') ?>

    <?php echo $menu ?>

    <?php use_helper('Date') ?>
    Viewing <?php echo $content->getType()->getLabel() ?> titled <strong>"<?php echo $content ?>"</strong> 
    created by <strong><?php echo $content->getAuthorName() ?></strong> on <strong><?php echo format_datetime($content->getDatePublished()) ?></strong>.
  </div>
<?php endif; ?>