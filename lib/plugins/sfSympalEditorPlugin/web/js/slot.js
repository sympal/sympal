(function($) {

$.widget('ui.sympalSlot', {
  
  options: {
    edit_mode: 'popup'
  },
  
  _init: function() {
    this.initialize();
  },
  
  openEditor: function() {
    var self = this;
    
    // disable the non-edit-mode controls
    this._disableControls();
    
    /*
     * 1) Ajax load the contents into the editor element
     * 2) Calls initializeEditor, which creates a slotEditor object
     */
    if (this._getData('edit_mode') == 'inline')
    {
      var editorElement = $('.sympal_slot_content', self.element);
      editorElement.load(self._getData('edit_url'), function() {
        self._initializeEditor(editorElement);
      });
    }
    else
    {
      $.fancybox(self._getData('edit_url'), {
        'zoomSpeedIn': 300,
        'zoomSpeedOut': 300,
        'overlayShow': true,
        'autoDimensions': true,
        'hideOnContentClick': false,
        'type': 'ajax',
        'onComplete': function() {
          self._initializeEditor($('#fancybox-inner'));
          
          // a VERY dirty hack to get the tinymce sizing correct
          if (self._getData('slotType') == 'TinyMCE')
          {
            $('#fancybox-inner').width(400);
            $('#fancybox-wrap').width(420);
          }
          
          $.fancybox.resize();
        },
        'onCleanup': function() {
          self.closeEditor();
        },
      });
    }
  },
  
  closeEditor: function() {
    var self = this;
    
    if (!this.getEditor())
    {
      return;
    }
    
    // kill the editor
    var editor = this.getEditor();
    this._setData('slotEditor', null);
    
    $.blockUI();
    
    // ajax in the content, and then set things back up
    $('.sympal_slot_content', self.element).load(
      self._getData('view_url'),
      function() {
        // reinitialize the non-edit-mode controls
        self._enableControls();
        
        // make sure fancybox is closed
        $.fancybox.close();
        
        // destroy the editor
        editor.sympalSlotEditor('destroy');
        
        $.unblockUI();
      }
    );
  },
  
  initialize: function() {
    var self = this;
    
    // register non-edit-handlers: effects for when the slot is not being edited    
    this._initializeControls();
    // attach the nonEditHandler events
    this._enableControls();
    
    // set the edit_url option
    this._setData('edit_url', $('.sympal_slot_button', this.element).attr('href'));
    
    // enable editing if the slot button is clicked
    $('a.sympal_slot_button', this.element).click(function() {
      self.openEditor()
      return false;
    });
    
    // highlight editable area on edit button hover
    $('a.sympal_slot_button', this.element).hover(function() {
      $('.sympal_slot_content', self.element)
        .css('opacity', .2)
        .children().css('opacity', .2);
    }, function() {
      $('.sympal_slot_content', self.element)
        .css('opacity', 1)
        .children().css('opacity', 1);
    });
  },
  
  _initializeEditor: function(editorSelector) {
    // initializes the editor object on the given selector
    var self = this;
    
    editorSelector.sympalSlotEditor({
      slotType: self._getData('slotType')
    });
    
    editorSelector.bind('close', function() {
      self.closeEditor();
    });
    
    this._setData('slotEditor', editorSelector);
  },
  
  _initializeControls: function() {
    var self = this;
    
    controlEvents = {};
    controlEvents['dblclick'] = function() {
      self.openEditor()
    }
    controlEvents['mouseover'] = function() {
      $('.sympal_slot_button', self.element).show();
    }
    controlEvents['mouseout'] = function() {
      $('.sympal_slot_button', self.element).hide();
    }
    this._setData('controlEvents', controlEvents);
  },
  
  _enableControls: function() {
    var self = this;
    
    // bind all of the non-edit-mode handlers
    $.each(this._getData('controlEvents'), function(key, value) {
      self.element.bind(key, value);
    });
  },
  
  _disableControls: function() {
    var self = this;
    
    // disable all of the non-edit-mode handlers
    $.each(this._getData('controlEvents'), function(key, value) {
      self.element.unbind(key, value);
    });
    
    // make sure the edit button is hidden
    $('.sympal_slot_button', this.element).hide();
  },
  
  getEditor: function(){
    return this._getData('slotEditor');
  }
  
});

})(jQuery);



jQuery(document).ready(function(){    
  
  $('.sympal_slot_wrapper').each(function() {
    $(this).sympalSlot({
      edit_mode: $(this).metadata().edit_mode,
      slotType: $(this).metadata().type,
      view_url:  $(this).metadata().view_url,
    });
  });
  

  $('.sympal_inline_edit_bar_edit_buttons').show();
  
  // globally save slots on the "save" edit button
  $('#inline-edit-bar-buttons-menu .sympal_save_content_slots').click(function(){
    $('form.sympal_slot_form').submit();
  });
  
  // globally hide slot forms on the "cancel" edit button
  $('#inline-edit-bar-buttons-menu .sympal_disable_edit_mode').click(function(){
    $('form.sympal_slot_form input.cancel').click();
  });
});