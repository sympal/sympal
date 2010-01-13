<div id="sf_admin_container">
  <h1><?php echo __('System Settings') ?></h1>

  <p><?php echo __('Manage the configuration of your Sympal project from the
  easy to use web form below. The different functionalities added by Sympal
  plugins may add settings here for you to control.') ?></p>

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