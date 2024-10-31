/**
 * @author BR0kEN, <broken@firstvector.org>
 */

;(function($){
	'use strict';

	if (typeof $ !== undefined) {

		var
		add 		= $('#gns_add'),
		loc 		= $('#gns_loc'),
		lang 		= $('#gns_lang'),
		stock 	= $('#gns_stock'),
		access 	= $('#gns_access'),
		genres	= $('#gns_genres'),
		status	= $('#gns_status'),

		getValues = function(){
			return {
				add: 		add.is(':checked') ? add.val() : add.prev().val(),
				loc: 		loc.val(),
				lang: 	lang.val(),
				stock: 	stock.val(),
				access: access.val(),
				genres: genres.val()
			};
		},

		defaults = getValues(),

		Message = function(name, message){
			status.attr('class', name).fadeOut(200, function(){
				status.html(message).fadeIn(200);
			});
		};

		$('#gns_default button').click(function(e){
			e.preventDefault();

			var values = getValues();

			if (JSON.stringify(defaults) === JSON.stringify(values)) {

				Message('fail', "Data haven't changed!");

			} else {

				$.post(ajaxurl, {action:'gns_default', options:values}, function(data){

					Message(data.status, data.message);

					defaults = values;

				}, 'json');

			}

		});

	}

})(window.jQuery);