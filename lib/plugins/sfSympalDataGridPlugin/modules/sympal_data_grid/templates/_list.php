<div class="sympal_data_grid_container">
  <table class="<?php echo $dataGrid->getClass() ?>" id="<?php echo $dataGrid->getId() ?>">
    <thead>
      <tr>
        <?php foreach ($dataGrid->getColumns() as $column): ?>
          <th><?php echo $dataGrid->getColumnSortLink($column) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($dataGrid->getRows() as $row): ?>
        <tr>
          <?php foreach ($row as $value): ?>
            <td><?php echo $value ?></td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>