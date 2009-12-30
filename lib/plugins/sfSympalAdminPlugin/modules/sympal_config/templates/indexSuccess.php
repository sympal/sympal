<?php sympal_use_stylesheet('/sfSympalPlugin/css/global.css') ?>
<?php sympal_use_stylesheet('/sfSympalPlugin/css/default.css') ?>

<div id="sf_admin_container">
  <h1>Sympal Configuration</h1>

  <div id="sf_admin_configuration">
    <div class="sf_admin_form">
      <?php $groups = $form->getGroups() ?>

      <?php echo $form->renderFormTag(url_for('@sympal_config_save')) ?>
        <?php echo $form->renderHiddenFields() ?>
        <?php foreach ($groups as $group): ?>
          <fieldset id="sf_fieldset_config_<?php echo strtolower($group) ?>">
            <h2><?php echo ucwords(sfInflector::humanize($group)) ?></h2>
            <?php echo $form->renderGroup($group) ?>
          </fieldset>
        <?php endforeach; ?>
        <input type="submit" name="save" value="Save" />
      </form>
    </div>
  </div>
</div>