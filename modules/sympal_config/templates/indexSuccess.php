<?php use_stylesheet('/sfSympalPlugin/css/configuration') ?>
<?php use_sympal_yui_css('tabview/assets/skins/sam/tabview') ?>

<?php use_sympal_yui_js('yahoo-dom-event/yahoo-dom-event') ?>
<?php use_sympal_yui_js('element/element') ?>
<?php use_sympal_yui_js('connection/connection') ?>
<?php use_sympal_yui_js('tabview/tabview') ?>

<div id="sf_admin_container">
  <div id="sympal_configuration">
    <h2>Sympal Configuration</h2>

    <?php $groups = $form->getGroups() ?>

    <?php echo $form->renderFormTag(url_for('@sympal_config_save')) ?>
      <div id="configuration" class="sympal_form yui-navset">
        <ul class="yui-nav">
          <?php $count = 0; foreach ($groups as $group): $count++; ?>
            <li<?php if ($count == 1): ?> class="selected" <?php endif; ?>><a href="#<?php echo $group ?>"><em><?php echo $group ?></em></a></li>
          <?php endforeach; ?>
        </ul>

        <div class="yui-content">
          <?php foreach ($groups as $group): ?>
            <div id="<?php echo $group ?>"><p><fieldset id="sf_fieldset_<?php echo $group ?>"><?php echo $form->renderGroup($group) ?></fieldset></p></div>
          <?php endforeach; ?>
        </div>
      </div>
      <input type="submit" name="save" value="Save" />
    </form>

    <script type="text/javascript">
    var myTabs = new YAHOO.widget.TabView("configuration");
    </script>
  </div>
</div>