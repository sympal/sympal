<p>
  <?php echo link_to('View full version history for this record', '@sympal_version_history?record_type='.get_class($version->getRecord()).'&record_id='.$version->getRecord()->id) ?>
</p>

Are you sure you wish to revert the changes made in version 
<strong>#<?php echo $version['version'] ?></strong> which was created on
<strong><?php echo date('m/d/Y h:i:s', strtotime($version['created_at'])) ?></strong> 
by <strong><?php echo $version['CreatedBy'] ?></strong>.

<p>Below you will find a list of the changes that were made.</p>

<h3><?php echo $version['num_changes'] ?> Change(s)</h3>

<ul>
  <?php foreach ($version['Changes'] as $change): ?>
    <li><a href="#<?php echo $field = sfInflector::humanize($change['field']) ?>"><?php echo $field ?></a></li>
  <?php endforeach; ?>
</ul>

<?php foreach ($version['Changes'] as $change): ?>
  <div class="sympal_diff">
    <h3><?php echo sfInflector::humanize($change['field']) ?></h3>

    <table>
      <tr>
        <th>Current Value</th>
        <th>Revert Value To</th>
      </tr>
      <tr>
        <td valign="top"><?php echo $change->getRenderValue('current') ?></td>
        <td valign="top"><?php echo $change->getRenderValue('revert') ?></td>
      </tr>
    </table>
  </div>
<?php endforeach; ?>