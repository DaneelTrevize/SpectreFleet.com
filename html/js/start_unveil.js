
(function ($) {
	"use strict";

	$(function(){

		$('#main img[data-src]').unveil(200, function() {
			$(this).on('load', function() {
				this.style.opacity = 1;
			});
		});

	});

})(window.jQuery);