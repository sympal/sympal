<?php phpinfo() ?>

<?php sympal_use_jquery() ?>
<?php include_javascripts() ?>

<script type="text/javascript">
$(function() {
  var iframe = $('#phpinfo_frame', parent.document.body);
  iframe.height($(document.body).height() + 30);
});
</script>