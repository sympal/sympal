<div id="sf_admin_container">
  <div id="sympal_configuration">
    <h2>Sympal Configuration</h2>

    <?php $groups = $form->getGroups() ?>

    <?php echo $form->renderFormTag(url_for('@sympal_config_save')) ?>
      <div id="configuration" class="sympal_form>
        <ul>
          <?php $count = 0; foreach ($groups as $group): $count++; ?>
            <li<?php if ($count == 1): ?> class="selected" <?php endif; ?>><a href="#<?php echo $group ?>"><em><?php echo ucwords(sfInflector::humanize($group)) ?></em></a></li>
          <?php endforeach; ?>
        </ul>

        <div>
          <?php foreach ($groups as $group): ?>
            <div id="<?php echo $group ?>"><p><fieldset id="sf_fieldset_<?php echo $group ?>"><?php echo $form->renderGroup($group) ?></fieldset></p></div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="black_bar">
        <input type="submit" name="save" value="Save" />
      </div>
    </form>
  </div>
</div>