$(function() {
	
	$(document).on('submit', 'form', function() {
		window._current_form = $(this);
	});
	
	if (typeof $.fn.fancybox !== 'undefined') {
		
		$('.js-fancybox').fancybox(window.fancybox_common_params_fancy ? window.fancybox_common_params_fancy() : {});
		
		$(document).on('click', '.js-modal-button', function() {
			fancybox_lock();
			var f = $(this).closest('form');
			if (f.length && !f.find('input[type="hidden"][name="is_modal"]').length) {
				f.append('<input type="hidden" name="is_modal" value="">');
			}
		});
		
		$(document).on('click', 'a.js-feedback', function(e) {
			e.stopPropagation();
			var t = $(this);
			var href = t.attr('href');
			var params = t.data('feedback-params');
			if ((typeof params === 'undefined') || (params === '')) {
				params = {};
			}
			if (typeof params.url === 'undefined') {
				params.url = document.location.pathname + document.location.search;
			}
			href += ((href.indexOf('?') === -1) ? '?' : '&')
					+ $.param(params);
			var fancybox_params = window.fancybox_common_params ? window.fancybox_common_params() : {};
			fancybox_params.type = 'ajax';
			fancybox_params.href = href;
			//fancybox_params.tpl = {closeBtn : '<a title="close" class="close" href="javascript:;"></a>',};
			$.fancybox(fancybox_params);
			return false;
		});
		
		$(document).on('click', '.js-map-popup', function() {
			var t = $(this);
			ymaps_init(function() {
				var fancybox_params = window.fancybox_common_params ? window.fancybox_common_params() : {};
				fancybox_params.content = '<div id="map-popup"></div>';
				fancybox_params.beforeShow = function() {
					$('#map-popup').width(Math.max(280, 3 * $(window).width() >> 2)).height(Math.max(280, 3 * $(window).height() >> 2));
					var coords = t.data('map-coords').split(',').map(function(x) { return 1 * x.replace(/^\s+|\s+$/g, ''); });
					var address = t.data('map-address');
					var title = t.data('map-title');
					var map = new ymaps.Map('map-popup', {
						center: coords,
						zoom: 16
					});
					map.geoObjects.add(new ymaps.Placemark(coords, {
						balloonContent: '<b>' + title + '</b><br>' + address
					}, {
						preset: 'islands#redDotIcon'
					}));
				};
				$.fancybox(fancybox_params);
			});
			return false;
		});
	}
	
	if (typeof $.fn.slick !== 'undefined') {
		$('.js-slick-slider').slick();
	}
	
});

function fancybox_lock() {
	fancybox_unlock();
	$('body').addClass('fancybox-lock');
	if (!$('html').hasClass('fancybox-margin')) {
		$('body').addClass('fancybox-margin');
	}
	$('body').append('<div class="js-fancybox-lock fancybox-overlay fancybox-overlay-fixed" style="width: auto; height: auto; display: block; opacity: 0.01;overflow-y: auto;"></div>');
	$.fancybox.showLoading();
}

function fancybox_unlock() {
	$('body').removeClass('fancybox-margin').removeClass('fancybox-lock');
	$('.js-fancybox-lock').remove();
    $.fancybox.hideLoading();
}

var is_ymaps_inited = false;
function ymaps_init(callback) {
	if (!is_ymaps_inited) {
		is_ymaps_inited = true;
		var script = document.createElement('script');
		script.src = '//api-maps.yandex.ru/2.1/?lang=ru_RU';
		document.body.appendChild(script);
	}
	var init = function() {
		if (typeof ymaps === 'undefined') {
			setTimeout(init, 100);
		} else {
			ymaps.ready(callback);
		}
	};
	init();
}

function ajax_send(action, data) {
	var sendData = {
		_element: 'data',
		_action: action,
		_is_simple_names: 1
	};
	if (typeof data !== 'undefined') {
		sendData = jQuery.extend(true, sendData, data);
	}
	$.ajax({
		type: 'post',
		url: action,
		data: sendData,
		success: function(result) {
			eval(result);
		}
	});
}

function get_cookie(name) {
	var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
	return matches ? decodeURIComponent(matches[1]) : void(0);
}

function set_cookie(name, value, options) {
	options = options || {};
	if (typeof options.expires === 'undefined' && typeof window.cookie_lifetime !== 'undefined') {
		options.expires = window.cookie_lifetime;
	}
	if (typeof options.expires === 'number' && options.expires) {
		var d = new Date();
		d.setTime(d.getTime() + options.expires * 1000);
		options.expires = d;
	}
	if (options.expires && options.expires.toUTCString) {
		options.expires = options.expires.toUTCString();
	}
	if (typeof options.domain === 'undefined' && typeof window.site_main_domain !== 'undefined') {
		options.domain = window.site_main_domain;
	}
	if (typeof options.path === 'undefined') {
		options.path = '/';
	}
	
	value = encodeURIComponent(value);
	var updatedCookie = name + "=" + value;
	for (var propName in options) {
		updatedCookie += "; " + propName;
		var propValue = options[propName];
		if (propValue !== true) {
			updatedCookie += "=" + propValue;
		}
	}
	document.cookie = updatedCookie;
}
