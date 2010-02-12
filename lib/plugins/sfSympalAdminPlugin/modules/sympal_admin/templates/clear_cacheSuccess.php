<h1>Clearing Cache</h1>

<div id="sympal_clear_cache_log">
  <ul>
    <li>Starting cache clearing process...</li>
  </ul>
</div>

<script type="text/javascript">
  var types = new Array();

  <?php foreach ($types as $key => $type): ?>

    types[<?php echo $key ?>] = '<?php echo $type ?>';

  <?php endforeach; ?>

  function clearCache(type, nextType)
  {
    $.get('<?php echo url_for('@sympal_clear_cache') ?>?type=' + type, function(data) {
      $('#sympal_clear_cache_log ul').append('<li>' + data + '</li>');
    });
  }
  
  for (i = 0; i < types.length; i++)
  {
    clearCache(types[i]);
  }
</script>