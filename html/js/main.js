// Avoid `console` errors in browsers that lack a console.
if (!(window.console && console.log)) {
	(function() {
		var noop = function() {};
		var methods = ['assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error', 'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log', 'markTimeline', 'profile', 'profileEnd', 'markTimeline', 'table', 'time', 'timeEnd', 'timeStamp', 'trace', 'warn'];
		var length = methods.length;
		var console = window.console = {};
		while (length--) {
			console[methods[length]] = noop;
		}
	}());
}


(function ($) {
	"use strict";

	$(function(){

		$(window).scroll(function() {
			var top = $(window).scrollTop();
			
			if ( top > ( $('#header').height() ) ) {
				$('body').addClass('scrolled');
			} else {
				$('body').removeClass('scrolled');
			}

		});

		$(document)
		.on('click', function(e) {

			if ( $(this).is( '[data-toggle]' ) === false ) {
				$('[data-toggled]').each(function(e) {

					if ( $(this).hasClass( $(this).attr('data-toggled') ) ) {
						$(this).toggleClass( $(this).attr('data-toggled') );
						$(this).removeAttr('data-toggled');
					}
				});
			}

		})
		.on('click', '[data-toggle][href="#"]', function(e) {
			e.stopPropagation();
			e.preventDefault();

			var $target = $(this).closest( $(this).data('toggle') ),
			class_name = ( $(this).data('toggle-class') ) ? $(this).data('toggle-class') : 'toggled-in';

			$target.toggleClass( class_name );

			if ( $target.hasClass( class_name ) ) {
				$target.attr('data-toggled', class_name );
				/*var $input = $target.find('input');
				
				if ( $input.size() > 0 ) {	// .size() isn't a function..?
					$input[0].focus();
				}*/
			} else {
				$target.removeAttr('data-toggled');
			}
			/*
			if ( $( '.subnav-tabbed-panel:first', $target ).size() > 0 ) {
				$( '.subnav-tabbed-panel:first img[data-src]', $target ).unveil();
			}
			*/
			var $siblings = $target.siblings('.' + class_name );

			$siblings.find( '.' +class_name ).toggleClass(class_name).removeAttr('data-toggled');
			$siblings.toggleClass( class_name ).removeAttr( 'data-toggled' );

		})

		.on('mouseover', '[data-toggle][href="#"]', function(e) {
			var $target = $(this).closest( $(this).data('toggle') ),
			class_name = ( $(this).data('toggle-class') ) ? $(this).data('toggle-class') : 'toggled-in',
			$siblings = $target.siblings('.' + class_name );

			$siblings.find( '.' +class_name ).toggleClass(class_name).removeAttr('data-toggled');
			$siblings.toggleClass( class_name ).removeAttr( 'data-toggled' );
		})

		.on( 'click', '.js-stoppropagation', function(e) {
			e.stopPropagation();
		})
		/*
		.on('click', '.js .collapsible-widgets .widget-title', function(e) {
			if ( $(this).closest('.widget').hasClass('active') ) {
				$(this).closest('.widget').removeClass('active');
			} else {
				$(this).closest('.widget').addClass('active').siblings().removeClass('active');
			}
			
			$(window).trigger('scroll');
		})*/
		.on('mouseover', '.subnav-tabbed-tabs a', function(e) {
			e.preventDefault();
			$(this).closest('li').addClass('active').siblings().removeClass('active');
			$( $(this).attr('href') ).addClass('active').siblings().removeClass('active');

			//$('img[data-src]', $(this).attr('href') ).unveil();

		})/*
		.on('mouseover', '.full-subnav-wrapper', function(e) {

			if ( $( '.subnav-tabbed-panel:first', $(this) ).size() > 0 ) {
				$( '.subnav-tabbed-panel:first img[data-src]', $(this) ).unveil();
			}

		})*/
		.on('click', '.nav-tabs a', function (e) {
			e.preventDefault();
			$(this).tab('show');
		});

		//$('.js .collapsible-widgets .widget:first .widget-title').trigger('click');
		/*
		$('#brand img[data-src]').unveil();

		$('#main img[data-src]').unveil(200, function() {
			$(this).load(function() {
				this.style.opacity = 1;
			});
		});
		*/
		$(window).trigger('scroll');
		
		$('[data-toggle="tooltip"]').tooltip();
		
		$('[data-submenu]').submenupicker();
		
	});

})(window.jQuery);