$(function() {
  
  // Ajax for folder browsering
  $('a.up, #sympal_assets_list li.folder a.go').click(function() {
    $('#sympal_assets_container').load(this.href, {}, function() {
      $.reloadChooserDrawer();
    });
    return false;
  });

  $('#sympal_assets_list li.asset a.insert').hover(
    function() {
      $(this).parents('li').find('.preview').show();
    },
    function() {
      $(this).parents('li').find('.preview').hide();
    }
  );

  // Toggle upload form display
  $('.sympal_assets_upload').click(function() {
    $('#sympal_assets_mkdir').hide();
    $('#sympal_assets_upload').toggle();
    $('#sympal_assets_mkdir input#upload_file').focus();
  });

  // Toggle directory form display
  $('.sympal_create_directory').click(function() {
    $('#sympal_assets_upload').hide();
    $('#sympal_assets_mkdir').toggle();
    $('#sympal_assets_mkdir input#directory_name').focus();
  });

  // Hide all asset forms by default
  $('#sympal_assets_upload').hide();
  $('#sympal_assets_mkdir').hide();

  // ajax for the delete folder link
  $('#sympal_assets_list .folder .delete').click(function() {
    $.post($(this).attr('href'), {}, function(data) {
      $('#sympal_assets_container').html(data);
    });
    
    return false;
  });

  // ajax for the edit asset link
  $('#sympal_assets_list .asset .edit').click(function() {
    $('#sympal_assets_container').load($(this).attr('href'), {}, function() {
      $.reloadChooserDrawer();
    });
    
    return false;
  });

  // ajax for the delete asset link
  $('#sympal_assets_list .asset .delete').click(function() {
    $.post($(this).attr('href'), {}, function(data) {
      $('#sympal_assets_container').html(data);
    });
    
    return false;
  });

  // Ajax for the upload form submit
  $('#sympal_assets_container form').submit(function() {
    $(this).ajaxSubmit({
      beforeSubmit: function() {
        $('#upload_is_ajax').attr('value', 1);
      },
      success: function() {
        refreshSympalAssets();
      }
    });
    return false;
  });
});

function refreshSympalAssets()
{
  $('#sympal_assets_container').load(
    $('#sympal_assets_container #current_url').attr('value'),
    {},
    function() {
      $.reloadChooserDrawer();
    }
  );
}