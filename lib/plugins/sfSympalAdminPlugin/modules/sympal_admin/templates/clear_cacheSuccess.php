<h1><?php echo __("Clearing Cache"); ?></h1>

<div style="height: 150px;" id="sympal_clear_cache_log">
  <ul>
    <li style="font-weight: bold; margin-bottom: 5px;"><?php echo __('Starting cache clearing process...'); ?></li>
  </ul>
</div>

<div id="clear_cache_finished" style="display: none;">
  <hr/>
  <h2>Cache Clear Successful!</h2>
</div>

<script type="text/javascript">

  // function called recursively, my attempt at synchronous javascript so
  // that we know when the whole process has finished
  function clearCache(types, i)
  {
    if (i < types.length)
    {
      $.get('<?php echo url_for('@sympal_clear_cache') ?>?type=' + types[i], function(data) {
        $('#sympal_clear_cache_log ul').append('<li>' + data + '</li>');
        
        clearCache(types, i+1);
      });
    }
    else
    {
      $('#clear_cache_finished').slideDown();
    }
  }

  var types = new Array();
  <?php foreach ($types as $key => $type): ?>
    types[<?php echo $key ?>] = '<?php echo $type ?>';
  <?php endforeach; ?>
  
  $(document).ready(function() {
    clearCache(types, 0);
  });

</script>

