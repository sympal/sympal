<p>
  <?php echo link_to('View full version history for this record', '@sympal_version_history?record_type='.get_class($version->getRecord()).'&record_id='.$version->getRecord()->id) ?>
</p>

Are you sure you wish to revert the changes made in version 
<strong>#<?php echo $version['version'] ?></strong> which was created on
<strong><?php echo date('m/d/Y h:i:s', strtotime($version['created_at'])) ?></strong> 
by <strong><?php echo $version['CreatedBy'] ?></strong>.

<p>Below you will find a list of the changes that were made.</p>

<div class="sympal_diff">

  <h2><?php echo $version['num_changes'] ?> Change(s)</h2>

  <ul>
    <?php foreach ($version['Changes'] as $change): ?>
      <li><a href="#<?php echo $field = sfInflector::humanize($change['field']) ?>"><?php echo $field ?></a></li>
    <?php endforeach; ?>
  </ul>

  <hr/>

  <?php foreach ($version['Changes'] as $change): ?>
    <h3><?php echo sfInflector::humanize($change['field']) ?></h3>

    <?php echo sfSympalDiff::diff($change['new_value'], $change['old_value']) ?>

    <div class="legend">
      <span><ins>&nbsp; &nbsp; </ins> &nbsp; <strong>Add</strong></span>
      &nbsp; &nbsp; 
      <span><del>&nbsp; &nbsp; </del> &nbsp; <strong>Delete</strong></span>
    </div>
  <?php endforeach; ?>
</div>