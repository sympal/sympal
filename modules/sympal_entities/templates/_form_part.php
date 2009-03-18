<fieldset>
  <legend><?php echo $name ?></legend>
  <?php foreach ($form as $key => $value): ?>
    <?php if (isset($fields) && !empty($fields) && !in_array($key, $fields)) continue; ?>
    <?php if (!$value instanceof sfFormFieldSchema && !$value->getWidget() instanceof sfWidgetFormInputHidden): ?>
      <div class="form_row">
        <?php echo $value->renderError() ?>
        <label><?php echo $value->renderLabel() ?></label>
        <?php echo $value ?>
        <?php echo $value->renderHelp() ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>
</fieldset>