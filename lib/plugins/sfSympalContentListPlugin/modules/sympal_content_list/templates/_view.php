<?php echo auto_discovery_link_tag('rss', $content->getRoute().'.rss') ?>

<?php echo get_sympal_breadcrumbs($menuItem) ?>

<h2><?php echo get_sympal_column_content_slot($content, 'title') ?></h2>

<?php echo get_sympal_content_slot($content, 'header') ?>

<?php $pagerNav = get_sympal_pager_navigation($pager, url_for($content->getRoute())) ?>

<?php $listResults = $pager->getResults() ?>

<?php echo get_sympal_pager_header($pager, $listResults) ?>

<?php echo $pagerNav ?>

<table>
  <thead>
    <tr>
      <th>Title</th>
      <th>Created By</th>
      <th>Date Published</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($listResults as $contentRecord): ?>
      <tr>
        <td><?php echo link_to($contentRecord, $contentRecord->getRoute()) ?></td>
        <td><?php echo $contentRecord->CreatedBy ?></td>
        <td><?php echo date('m/d/Y', strtotime($contentRecord->date_published)) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php echo $pagerNav ?>

<?php echo get_sympal_content_slot($content, 'footer') ?>