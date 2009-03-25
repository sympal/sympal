<?php use_stylesheet('/sfSympalPlugin/css/configuration') ?>
<?php use_javascript('/sfSympalPlugin/js/configuration') ?>

<div id="sympal_configuration">
  <h2>Sympal Configuration</h2>

  <?php $groups = $form->getGroups() ?>

  <ul>
    <?php foreach ($groups as $group): ?>
      <li><a href="#<?php echo $group ?>"><?php echo $group ?></a></li>
    <?php endforeach; ?>
  </ul>

  <div class="sympal_form">
    <?php echo $form->renderFormTag(url_for('@sympal_config_save')) ?>
      <?php foreach ($groups as $group): ?>
        <?php echo $form->renderGroup($group) ?>
      <?php endforeach; ?>

      <input type="submit" name="save" value="Save" />
    </form>
  </div>
</div>