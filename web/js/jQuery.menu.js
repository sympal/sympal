function initMenus() {
	$('ul.menu ul').hide();
	$.each($('ul.menu'), function(){
		var cookie = $.cookie(this.id);
		if(cookie === null || String(cookie).length < 1) {
			$('#' + this.id + '.expandfirst ul:first').show();
		}
		else {			
			$('#' + this.id + ' .' + cookie).next().show();
		}
	});
	$('ul.menu li a').click(
		function() {

			var checkElement = $(this).next();
			var parent = this.parentNode.parentNode.id;

			if($('#' + parent).hasClass('noaccordion')) {
				if((String(parent).length > 0) && (String(this.className).length > 0)) {
					if($(this).next().is(':visible')) {
						$.cookie(parent, null);
					}
					else {
						$.cookie(parent, this.className);
					}
					$(this).next().slideToggle('normal');
				}				
			}
			if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
				if($('#' + parent).hasClass('collapsible')) {
					$('#' + parent + ' ul:visible').slideUp('normal');
				}
				return false;
			}
			if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
				$('#' + parent + ' ul:visible').slideUp('normal');
				if((String(parent).length > 0) && (String(this.className).length > 0)) {
					$.cookie(parent, this.className);
				}
				checkElement.slideDown('normal');
				return false;
			}
		}
	);
}
$(document).ready(function() {initMenus();});