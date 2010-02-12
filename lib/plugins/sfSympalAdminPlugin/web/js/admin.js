
$(function()
{
  $('#sympal_clear_cache_fancybox').fancybox();

  $('.sf_admin_form input:text:visible:first').focus();

  $('.sf_admin_form fieldset h2.sf_fieldset_h2').click(function() {
    
    var name = $('.sf_admin_form').parent('div').attr('id');
    var current = $.cookie($('.sf_admin_form').parent('div').attr('id'));
    var clicked = $(this).parent('fieldset').attr('id');
    var h2 = $(this);

    if (current && current !== clicked && $('#' + current).length)
    {
      $('#' + current + ' h2.sf_fieldset_h2').css('background-image', $('#' + current + ' h2.sf_fieldset_h2').css('background-image').replace('collapse', 'expand'));
      $('#' + current + ' .sf_admin_form_row').slideUp('fast');
    }

    if (h2.css('background-image').indexOf('expand') > -1)
    {
      h2.css('background-image', h2.css('background-image').replace('expand', 'collapse'));
    } else {
      h2.css('background-image', h2.css('background-image').replace('collapse', 'expand'));
    }

    h2.parent('fieldset').find('.sf_admin_form_row').slideToggle('fast');
    
    if (current == clicked)
    {
      $.cookie(name, null);
    } else {
      $.cookie(name, clicked);
    }
  });

  if ($('.sf_admin_form fieldset h2.sf_fieldset_h2').length)
  {
    $('.sf_admin_form_row').hide();

    var current = $.cookie($('.sf_admin_form').parent('div').attr('id'));
    if (!current)
    {
      current = $('.sf_admin_form fieldset h2.sf_fieldset_h2:first-child').parent('fieldset').attr('id');
      $.cookie($('.sf_admin_form').parent('div').attr('id'), current);
    }

    if (current && $('#' + current).length)
    {
      $('#' + current + ' h2.sf_fieldset_h2').css('background-image', $('#' + current + ' h2.sf_fieldset_h2').css('background-image').replace('expand', 'collapse'));
      $('#' + current + ' .sf_admin_form_row').show();
    }
  }

  if ($('.sf_admin_filter').html())
  {
    $('.sf_admin_filter').hide();

    var append = '<span style="float: left;">' + $('#sf_admin_container h1').html() + '</span><div id="sympal_filters_button"></div>';
    $('#sf_admin_container h1').html(append);

    $('#sympal_filters_button').click(function() {
      $('.sf_admin_filter').slideToggle();
      
      var pos = $('.sf_admin_list').offset();  
      var width = $('.sf_admin_list').width();
      $("#sf_admin_bar").css( { "position": "absolute", "left": (pos.right + width) + "px", "top":pos.top + 1 + "px" } );
    });
  }
});