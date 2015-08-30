(function($, undefined) {

	$.nette.ext('ga', {
		success: function(payload) {
			var url = payload.url || payload.redirect;
			if (url && !$.nette.ext('redirect')) {
				var title = payload.title || document.title;
				ga('send', 'pageview', {
					'page': url,
					'title': title
				});
			}
		}
	});

})(jQuery);

(function($, undefined) {

	$.nette.ext('spinner', {
		init: function() {
			this.spinner = this.createSpinner();
			this.spinner.appendTo('body');
		},
		start: function() {
			this.spinner.show(this.speed);
		},
		complete: function() {
			this.spinner.hide(this.speed);
		}
	}, {
		createSpinner: function() {
			return $('<div>', {
				id: 'ajax-spinner',
				css: {
					display: 'none'
				}
			});
		},
		spinner: null,
		speed: undefined
	});

})(jQuery);

$(document).ready(function () {

	$.nette.ext('onChange', {
		load: function(rh) {
			$("#search").on("keyup", function(event) {
				event.preventDefault();
				if ($(this).val() !== "")
					$(this).closest('form').delay(200).submit();
			});
			$("#search").on("change", function(event) {
				event.preventDefault();
				if ($(this).val() !== "")
					$(this).closest('form').submit();
			});
		}
	});
	
	$.nette.ext('afterLoad', {
		success: function(payload) {
            $('.player-ability:not(.pre-opened) .wrap').hide();
            $('.toggle:not(.pre-opened) .wrap').hide();
			$("select.widget-multiselect").multiselect();
		}
	});

	$.nette.init();

    $('body').on('click', '#menu-toggle', function(e) {
        e.preventDefault();
        $('.menu .menu-item').toggle();
    });

	$('.player-ability:not(.pre-opened) .wrap').hide();
	$('body').on('click', '.player-ability h2', function() {
		$(this).parent().find(".wrap").stop().slideToggle(300);
	});

	$('.toggle:not(.pre-opened) .wrap').hide();
	$('body').on('click', '.toggle h2', function() {
		$(this).parent().find(".wrap").stop().slideToggle(300);
	});

	$('body').on('change', '#compare', function() {
		var buildUrl = function(base, key, value) {
			var sep = (base.indexOf('?') > -1) ? '&' : '?';
			return base + sep + key + '=' + value;
		};
		var status = $(this).is(':checked').toString();
		var playerId = parseInt($(this).parents('.player-card').data('playerid'));
		$.nette.ajax({
			url: buildUrl(document.location.href, 'compare', status)
		});
		ga('send', 'event', 'compare', status, playerId);
	});


	$("select.widget-multiselect").multiselect();
});

