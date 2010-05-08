$.extend({

  sympalInitLinksAjax : function()
  {
    // initializes the ajax needed for the links chooser
    $('#sympal_links_container #content_types a').click(function() {
      container = $('#sympal_links_container');
      container.parent().block('loading');
      
      container.load(
        $(this).attr('href'),
        {},
        function() {
          container.parent().unblock();
        }
      );
      
      return false;
    });
  }

});