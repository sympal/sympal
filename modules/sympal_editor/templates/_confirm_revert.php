Are you sure you wish to revert this record back to version 
<strong>#<?php echo $version['version'] ?></strong> which was created on
<strong><?php echo date('m/d/Y h:i:s', strtotime($version['created_at'])) ?></strong> 
by <strong><?php echo $version['CreatedBy'] ?></strong>. Below you will find a 
list of the <strong><?php echo $version['num_changes'] ?></strong> change(s) that were made.

<table>
  <thead>
    <tr>
      <th>Property</th>
      <th>Current Value</th>
      <th>Reverting To</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($version['Changes'] as $change): ?>
      <tr>
        <td><strong><?php echo sfInflector::humanize($change['field']) ?></strong></td>
        <td><?php echo $version->getRecord()->get($change['field']) ?></td>
        <td><?php echo $change['revert_value'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>