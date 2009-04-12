<h2><?php echo sfInflector::humanize(sfInflector::tableize(get_class($record))) ?> record #<?php echo $record->getId() ?> Version History</h2>

<table>
  <tr>
    <th>#</th>
    <th>Date</th>
    <th>Created By</th>
    <th></th>
  </tr>
  <?php foreach ($versions as $version): ?>
    <tr>
      <td><?php echo $version['version'] ?></td>
      <td><?php echo date('m/d/Y h:i:s', strtotime($version['created_at'])) ?></td>
      <td><?php echo $version['CreatedBy'] ?></td>
      <td><?php echo link_to(image_tag('/sfSympalPlugin/images/revert.png'), '@sympal_revert_data?id='.$version['id']) ?></td>
    </tr>
  <?php endforeach; ?>
</table>