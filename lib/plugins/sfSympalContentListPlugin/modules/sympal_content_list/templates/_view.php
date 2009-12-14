<?php echo auto_discovery_link_tag('rss', $content->getRoute().'?sf_format=rss') ?>

<?php echo get_sympal_breadcrumbs($menuItem) ?>

<h2><?php echo get_sympal_column_content_slot($content, 'title') ?></h2>

<?php echo get_sympal_content_slot($content, 'header') ?>

<?php $pagerNav = $dataGrid->getPagerNavigation($content->getRoute()) ?>

<?php echo $dataGrid->getPagerHeader() ?>

<?php echo $pagerNav ?>

<?php echo $dataGrid->render() ?>

<?php echo $pagerNav ?>

<?php echo get_sympal_content_slot($content, 'footer') ?>