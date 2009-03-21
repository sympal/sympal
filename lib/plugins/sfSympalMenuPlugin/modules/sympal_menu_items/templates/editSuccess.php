<?php echo get_sympal_breadcrumbs($menu_item, null, 'Edit Menu Item', true) ?>

<?php use_helper('I18N', 'Date') ?>
<?php include_partial('sympal_menu_items/assets') ?>

<div id="sf_admin_container">
  <h2><?php echo __('Edit Sympal menu items', array(), 'messages') ?></h2>

  <div id="sf_admin_header">
    <?php include_partial('sympal_menu_items/form_header', array('menu_item' => $menu_item, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>

  <div id="sf_admin_content">
    <?php include_partial('sympal_menu_items/form', array('menu_item' => $menu_item, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('sympal_menu_items/form_footer', array('menu_item' => $menu_item, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>
</div>

<?php echo get_sympal_editor($menu_item, ($menu_item->getMainEntity() && $menu_item->getMainEntity()->exists() ? $menu_item->getMainEntity():null)) ?>