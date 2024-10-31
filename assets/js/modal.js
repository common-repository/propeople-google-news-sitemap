/**
 * @author BR0kEN, <broken@firstvector.org>
 */

;(function($, D){
	'use strict';

	if (typeof $ !== undefined) {

		$.fn.gnsModal = function(){
			var layer = $('<div id="gns_layer" />').appendTo(D.body),
					modal = $('#gns_modal');

			modal.css('margin-top', -(modal.outerHeight() / 2));

			this.add(modal.find('span')).add(layer).click(function(e){
				e.preventDefault();

				modal.add(layer)[e.target.nodeName.length > 1 ? 'fadeOut' : 'fadeIn'](500);
			});
		};

		$('#gns_open').gnsModal();

	}

})(window.jQuery, document);