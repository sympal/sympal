<?php use_stylesheet('/sfSympalBlogPlugin/css/blog.css', 'first') ?>

<?php echo get_sympal_breadcrumbs($menuItem) ?>

<?php echo auto_discovery_link_tag('rss', $content->getRoute().'?sf_format=rss') ?>

<div id="sympal_blog">
  <div class="list">
    <h2><?php echo get_sympal_content_slot($content, 'title') ?></h2>

    <?php echo get_sympal_content_slot($content, 'header') ?>

    <?php $listResults = $pager->getResults() ?>

    <?php echo get_partial('sympal_blog/blog_list', array('menuItem' => $menuItem, 'pager' => $pager, 'content' => $listResults)) ?>

    <?php echo get_sympal_content_slot($content, 'footer') ?>
  </div>
</div>

<?php slot('sympal_right_sidebar') ?>
  <?php echo get_component('sympal_blog', 'sidebar') ?>
<?php end_slot() ?>