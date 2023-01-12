var mobileDetect = new MobileDetect(window.navigator.userAgent);

$(document).on('click', '.js-close_popup', function() {
	$.fancybox.close();
});
$(function() {
	$(document).on('keyup input click', '.registration input[name="email"]', function () {
		email_reg = /^((([0-9A-Za-z]{1}[-0-9A-z\.]{1,}[0-9A-Za-z]{1})|([0-9А-Яа-я]{1}[-0-9А-я\.]{1,}[0-9А-Яа-я]{1}))@([-A-Za-z]{1,}\.){1,2}[-A-Za-z]{2,})$/u ;
		email = $(this).val();
		if(email_reg.test(email) == false) {
			$(this).removeClass('verified').addClass('error');
			$(this).parent().find('.field-description').addClass('error');
		}else{
			$(this).removeClass('error').addClass('verified');
			$(this).parent().find('.field-description').removeClass('error');
		}
	 });
	 $(document).on('keyup input click', '.registration input[name="name"]', function () {
		name_reg = /^([A-Za-zА-Яа-я\s]+)$/u ;
		name = $(this).val();
		if(name_reg.test(name) == false) {
			$(this).removeClass('verified').addClass('error');
			$(this).parent().find('.field-description').addClass('error');
		}else{
			$(this).removeClass('error').addClass('verified');
			$(this).parent().find('.field-description').removeClass('error');
		}
	 });
	 $(document).on('keyup input click', '.registration input[name="password"]', function () {
		name_reg = /^([A-Za-z0-9\p{P}\$\^+=<>\\]{6,})$/u ;
		name = $(this).val();
		if(name_reg.test(name) == false) {
			$(this).removeClass('verified').addClass('error');
			$(this).parent().find('.field-description').addClass('error');
		}else{
			$(this).removeClass('error').addClass('verified');
			$(this).parent().find('.field-description').removeClass('error');
		}
	 });
	 $(document).on('click', 'a.as-submit', function () {
		$(this).closest('form').submit();
		return false;
	 });
	 $(document).on('click', '.js-input-address-save-disabled', function () {
		$('.js-button-address-save-disabled').attr('disabled',true)
		return false;
	 });
	 
});


$(function(){
	$('.js-pseudo-filter').hide();
	
	$(document).on('submit', '.js-filter-form', function() {
		$(this).find(':input').each(function() {
			var t = $(this);
			console.log('' + t.data('value-limit'));
			if ((t.val() === '') || (t.val() === '' + t.data('value-limit'))) {
				t.removeAttr('name');
			}
		});
	});

	$(document).on('change', '.js-filter-form :input', function() {
		if (! (mobileDetect.phone() || mobileDetect.tablet())) {
			$(this).closest('form').submit();
			return true;
		}
	});

	$('.js-slider-1').slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		dots: true,
		customPaging : function(slider, i) {
			var title = $(slider.$slides[i]).data('title');
			return '<a href="javascript:void(0);" class="dots-1"></a>';
		},
		infinite: true,
		arrows: true,
		fade: true,
		cssEase: 'linear',
		prevArrow: '<a href="" class="left-1"></a>',
		nextArrow: '<a href="" class="right-1"></a>',
	});

	$('.js-slider-2').slick({
		slidesToShow: 4,
		slidesToScroll: 1,
		dots: true,
		customPaging : function(slider, i) {
			var title = $(slider.$slides[i]).data('title');
			return '<a href="javascript:void(0);" class="dots-2"></a>';
		},
		infinite: true,
		arrows: true,
		prevArrow: '<a href="" class="button-bg-1"><span class="left-2"></span></a>',
		nextArrow: '<a href="" class="button-bg-2"><span class="right-2"></span></a>',
		responsive: [
			{
				breakpoint: 1200,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 1	
				}
			},
			{
				breakpoint: 992,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 1
				}
			},
			{
				breakpoint: 768,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			}
		]
	});
});

$(function(){
	// скрывать кнопки показать еще итд если нет фильтров
	// место для костылей
	if ($('.js-filter-form .js-unfolding-container').length === 0)
		$('.js-facets-1').remove();

	var temp;
	if (temp = $('.content-tab-1').html())
	{
		// скрывать описание если его нет
		if (temp.indexOf('<') == -1)
		{
			$('.content-tab-1').remove();
			$('label[for="tab-1"]').remove();

			$('label[for="tab-2"]').click();
		}
	}

	$(document).on('click', '.js-city-confirm', function() {
		var t = $(this);
		set_cookie('is_city_confirmed', 1);
		set_cookie('city_id', t.data('city_id'));
		document.location.href = t.data('href');
	});
	// костылей дальше нет (но это не точно)

	// города
	var cities_filter_update = function(c) {
		var unfiltered_list = $('.js-cities-filter-unfiltered-list', c);
		var filtered_list = $('.js-cities-filter-filtered-list', c);
		var items = $('.js-cities-filter-item', c);
		var input = $('.js-cities-filter-input', c);
		var value = input.val().trim().toLowerCase();
		if (value === '') {
			filtered_list.stop(true, true).hide();
			unfiltered_list.stop(true, true).show();
			items.stop(true, true).hide();
		} else {
			unfiltered_list.stop(true, true).hide();
			filtered_list.stop(true, true).show();
			items.not('[data-cities-filter-title*="' + value + '"]').stop(true, true).hide();
			items.filter('[data-cities-filter-title*="' + value + '"]').stop(true, true).show();
		}
	};

	var cities_filter_timeout;
	$(document).on('change keyup', '.js-cities-filter-input', function() {
		var c = $(this).closest('.js-cities-filter-container');
		clearTimeout(cities_filter_timeout);
		cities_filter_timeout = setTimeout(function() {
			cities_filter_update(c);
		}, 50);
	});

	$(document).on('click', '.js-facets-2', function() {
		if ($('.js-facets-1').hasClass('active')) {
			close_facets();
		} else {
			open_facets();
			$(".send-8").width($("form.js-filter-form").width());
		}
	});
	var open_facets = function() {
		$('.js-facets-1').addClass('active').stop(true, false).fadeIn(100);
		$('.js-bg-1').addClass('active').stop(true, false).fadeIn(100);
	};
	var close_facets = function() {
		$('.js-facets-1').removeClass('active').stop(true, false).fadeOut(100);
		$('.js-bg-1').removeClass('active').stop(true, false).fadeOut(100);
	};
	$(document).on('click', '.js-bg-1, .js-close', function(e) {
		var $t = $(e.target);
		if (!$t.closest('.js-facets-1').length || $t.closest('.js-close').length) {
			close_facets();
		}
	});

	$(document).on('click', '.js-menu-1-xs', function() {
		if ($('.js-menu-2-xs').hasClass('active')) {
			close_menu();
		} else {
			open_menu();
		}
	});
	var open_menu = function() {
		$('.js-menu-2-xs').addClass('active').stop(true, false).fadeIn(100);
		$('.js-bg-1').addClass('active').stop(true, false).fadeIn(100);
	};
	var close_menu = function() {
		$('.js-menu-2-xs').removeClass('active').stop(true, false).fadeOut(100);
		$('.js-bg-1').removeClass('active').stop(true, false).fadeOut(100);
	};
	$(document).on('click', '.js-bg-1, .js-close', function(e) {
		var $t = $(e.target);
		if (!$t.closest('.js-menu-2-xs').length || $t.closest('.js-close').length) {
			close_menu();
		}
	});
	
	$(document).on('click', '.js-open-1', function() {
		if ($('.js-open-2').hasClass('active')) {
			close_categories();
		} else {
			open_categories();
		}
	});
	var open_categories = function() {
		$('.js-open-2').addClass('active').stop(true, false).fadeIn(100);
		$('.js-bg-2').addClass('active').stop(true, false).fadeIn(100);
	};
	var close_categories = function() {
		$('.js-open-2').removeClass('active').stop(true, false).fadeOut(100);
		$('.js-bg-2').removeClass('active').stop(true, false).fadeOut(100);
	};
	$(document).on('click', '.js-bg-2, .js-close', function(e) {
		var $t = $(e.target);
		if (!$t.closest('.js-open-2').length || $t.closest('.js-close').length) {
			close_categories();
		}
	});
});

$(function(){
	var slider_for_params = {
		slidesToShow: 1,
		slidesToScroll: 1,
		asNavFor: '.js-slider-nav',
		verticalSwiping: false,
		arrows: false,
		dots: false,
		fade: true,
		infinite: true,
		speed: 500,
		cssEase: 'linear'
		
	};
	var is_custom_sliders_relation = ($('.js-slider-nav').children().length <= 4);
	if (!is_custom_sliders_relation) {
		slider_for_params.asNavFor = '.js-slider-nav';
	}
	$('.js-slider-for').slick(slider_for_params);
	if (is_custom_sliders_relation) {
		$('.js-slider-for').on('afterChange', function(event, slick, currentSlide) {
			if ($('.js-slider-nav').slick('slickCurrentSlide') !== currentSlide) {
				$('.js-slider-nav').slick('slickGoTo', currentSlide, true);
			}
		});
	}
	$('.js-slider-nav').slick({
		slidesToShow: 5,
		slidesToScroll: 1,
		asNavFor: '.js-slider-for',
		focusOnSelect: true,
		vertical: true,
		arrows: false,
			responsive: [
			{
				breakpoint: 768,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 1
				}
			}
		]
	});
	//if (is_custom_sliders_relation) {
	//	$('.js-slider-nav').on('setPosition', function(event, slick) {
	//		if ($('.js-slider-nav').slick('slickCurrentSlide') !== 0) {
	//			$('.js-slider-nav').slick('slickGoTo', 0);
	//		}
	//	});
	//}
	
});

$('.js-interval-slider').each(function() {
	console.log('found js-interval-slider');
	var t = $(this);
	var input_min = t.parent().find('.js-slider-input-min');
	var input_max = t.parent().find('.js-slider-input-max');
	noUiSlider.create(this, {
		start: [1 * input_min.val(), 1 * input_max.val()],
		step: 1,
		connect: true,
		format: {
			from: Math.round,
			to:   Math.round
		},
		/*pips: {
			mode: 'range',
			density: 100
		},*/
		range: {
			'min': 1 * input_min.data('value-limit'),
			'max': 1 * input_max.data('value-limit')
		}
	});

	this.noUiSlider.on('update', function(values, handle, unencoded, isTap, positions) {
		if (handle === 0) {
			input_min.val(values[0]);
		}
		if (handle === 1) {
			input_max.val(values[1]);
		}
	});

	this.noUiSlider.on('set', function() {
		setTimeout(function() {
			if ((mobileDetect.phone() || mobileDetect.tablet())) {
				return false;
			}

			$('.js-filter-form').submit();
		}, 100);
	});
});
$('.js-number-slider').each(function() {
	var t = $(this);
	var input = t.find('.js-number-input');
	var bar = t.find('.js-slider-bar')[0];
	var data_values = t.data('slider-values');
	var start_value = t.data('slider-value');
	var l = data_values.unshift('');
	var unit = t.data('unit');
	/*var pips_values = new Array(l - 1);
	for (var i in pips_values) {
		pips_values[i] = i + 1;
	}*/
	noUiSlider.create(bar, {
		start: start_value,
		step: 1,
		tooltips: {
			to: function(value) {
				if (!value) {
					return 'Все';
				}
				return data_values[Math.round(value)] + ' ' + unit;
			}
		},
		range: {
			min: 0,
			max: l - 1
		},
		/*pips: {
			mode: 'values',
			values: pips_values,
			density: 100.0 / (l - 1)
		},*/
		format: {
			to: function (value) {
				return data_values[Math.round(value)];
			},
			from: function (value) {
				return data_values.indexOf(value);
			}
		}
	});
	bar.noUiSlider.on('update', function (values, handle) {
		input.val(values[handle]);
		$(bar).find('.active-delenie').width(100.0 * data_values.indexOf(values[handle]) / (l - 1) + '%');
		//t.find('.js-slider-value').html(values[handle]);
	});
	bar.noUiSlider.on('set', function() {
		setTimeout(function() {
			/*if (input.val() === '') {
				input.removeAttr('name');
			}*/
			if ((mobileDetect.phone() || mobileDetect.tablet())) {
				return false;
			}

			$('.js-filter-form').submit();
		}, 100);
	});
	
	$(bar).find('.noUi-base').append('<div class="delenie-container"><div class="active-delenie"></div>');
	for (var i = 0; i < data_values.length; i++) {
		$(bar).find('.delenie-container').append('<div class="sz-delenie"><span>' + data_values[i] + '</span></div>');
	}
	var left_sz = 0;
	var procent = 100 / (data_values.length - 1);
	$(bar).find('.sz-delenie').each(function(i) {
		$(this).css('left', left_sz + '%');
		left_sz += procent;
	});
	$(bar).find('.active-delenie').width(100.0 * data_values.indexOf('' + start_value) / (l - 1) + '%');
});

$(document).ready(function () {
	$('.js-sumoselect').SumoSelect({
		placeholder: 'Вариант доставки',
		csvDispCount: 3
	});
});

function fancybox_common_params() {
	
	return {
		padding: 0,

		helpers : {
			overlay : {
				locked : true
			}
		},
		tpl: {
			closeBtn : '<a class="close" href="javascript:void(0);" onclick="$.fancybox.close();"></a>'
		}
	};
}
function fancybox_common_params_fancy() {
	
	return {
		padding: 5
	};
}
var maps = $('.js-map');
if (maps.length) {
	ymaps_init(function() {
		maps.each(function(i) {
			var t = $(this);
			if (!t.attr('id')) {
				t.attr('id', 'js-map-' + i);
			}
			var coords = t.data('map-coords').split(',').map(function(x) { return 1 * x.replace(/^\s+|\s+$/g, ''); });
			var address = t.data('map-address');
			var title = t.data('map-title');
			if (!t.height()) {
				t.height(505);
			}
			var map = new ymaps.Map(t.attr('id'), {
				center: coords,
				zoom: 16,
				controls: ['zoomControl', 'typeSelector',  'fullscreenControl', 'routeButtonControl']
			});
			map.geoObjects.add(new ymaps.Placemark(coords, {
				balloonContent: '<b>' + title + '</b><br>' + address
			}, {
				iconLayout: 'default#image',
				iconImageHref: '/images/marker-02.svg',
				iconImageSize: [40, 40],
				iconImageOffset: [-21, -27]
			}));
		});
	});
}

$(document).on('click', '.js-unfolding-button, .js-unfolding-button-menu', function(event) {
	event.preventDefault();
	$(this).parent().toggleClass('arrow');
	$('.js-unfolding-block, .js-unfolding-block-menu, .js-unfolding-block-faq, .js-unfolding-block-product', $(this).closest('.js-unfolding-container, .js-unfolding-container-menu')).stop(true, false).slideToggle(300);
});


$('.js-unfolding-block-faq').hide();
$('.js-unfolding-block-menu').hide();
$('.js-unfolding-block-product').hide();

$(document).on('click', '.js-unfolding-button-2', function() {
	var t = $(this);
	t.parent().toggleClass('arrow');
	$('.js-unfolding-block-2', t.closest('.js-unfolding-container-2')).stop(true, false).slideDown(300);
	$(this).remove();
});

$('.js-unfolding-block-2').hide();
$('.js-unfolding-button-2').show();

/*function feedback_success() {
	_current_form.hide().after('<div class="success-message">Спасибо<br>Наши специалисты свяжутся с Вами в кратчайшие сроки</div>').remove();
}*/

$(function(){
	(function () {
		if (! (mobileDetect.phone() || mobileDetect.tablet())) {
			return false;
		}

		$(".send-8").css({
			"z-index": "9",
			"position": "fixed",
			"bottom": "0"
		});
	})();
});

$(function () {
	const commonParams = fancybox_common_params();

	const params = {
		type: 'ajax',
		tpl: {
			wrap: wrap()
		}
	};

	const deepMergedParams = $.extend(true, {}, commonParams, params);

	$('.js-fancybox-modal').fancybox(deepMergedParams);

	function wrap() {
		const deepMergedDefaults = $.extend(true, {}, $.fancybox.defaults, commonParams);

		const
			wrap = $(deepMergedDefaults.tpl.wrap),
			closeBtn = $(deepMergedDefaults.tpl.closeBtn);

		$('.fancybox-inner', wrap).addClass('fancybox-inner-modal');
		$('.fancybox-skin', wrap).append(closeBtn);

		return wrap.html();
	}
});

$(document).on('click', '.js-fancybox-personal', function() {
	$.fancybox.open($(this), {
		type: 'ajax',
		padding: 0,
		helpers: {
			overlay: {
				closeClick: false,
				locked: true,
				css: {
					'background': 'rgba(64,63,63,.7)'
				}
			}
		},
		tpl: {
			closeBtn: '<a class="modal-close-button" href="javascript:;"></a>',
			wrap: '<div class="fancybox-wrap" tabIndex="-1"><div class="fancybox-skin fancybox-my-radius"><div class="fancybox-outer"><div class="fancybox-inner fancybox-inner-modal"></div></div></div></div>'
		}
	});
});

$(document).on('change input', '.error :input', function() {
	$(this).closest('.error').removeClass('error');
	$(this).closest('.has-error').removeClass('has-error');
});

$(document).on('click', '.js-popover-open', function() {
	$('.js-discount-popover').toggleClass('popover-open').stop(true, false);		
});
//
// $(document).on('click', '.js-catalog-open', function() {
// 	$('.catalog-button').toggleClass('catalog-open-menu').stop(true, false);
// 	$('.js-catalog-menu').toggleClass('catalog-open-container').stop(true, false);
// });




(function (formSelector) {
	const inputSelector = '.js-input';
	const editButtonSelector = '.js-edit-button';
	const saveButtonSelector = '.js-save-button';

	$(document).on('click', `${formSelector} ${editButtonSelector}`, function() {
		enableForm($(this).closest(formSelector));
	});

	$(document).on('submit', formSelector, function() {
		if (this.disableForm === undefined) {
			const form = $(this);
			this.disableForm = function() {
				disableForm(form)
			}
		}
	});

	$(document).on('change input', `${formSelector} ${inputSelector}`, function() {
		makeAvailableForSaving($(this).closest(formSelector));
	});

	function enableForm(form) {
		$(inputSelector, form).removeAttr('readonly');
		$(editButtonSelector, form).removeClass('black-button').attr('disabled', 'disabled');
	}

	function makeAvailableForSaving(form) {
		$(saveButtonSelector, form).addClass('black-button').removeAttr('disabled');
	}

	function disableForm(form) {
		$(inputSelector, form).attr('readonly', 'readonly');
		$(editButtonSelector, form).addClass('black-button').removeAttr('disabled');
		$(saveButtonSelector, form).removeClass('black-button').attr('disabled', 'disabled');
	}
}('.js-cabinet-personal-edit-form'));

function dadataFormatResult(value, currentValue, suggestion) {
	return dadataMakeAddressString(suggestion.data);
}

function dadataFormatSelected(suggestion){
	const addressValue = dadataMakeAddressString(suggestion.data);
	suggestion.value = addressValue;
	return addressValue;
}

function dadataMakeAddressString(address){
	return join([
		join([address.region_type, address.region], " "),
		join([address.area_type, address.area], " "),
		(address.city !== address.region && join([address.city_type, address.city], " ") || ""),
		join([address.settlement_type, address.settlement], " "),
		join([address.street_type, address.street], " "),
		join([address.house_type, address.house, address.block_type, address.block], " "),
		join([address.flat_type, address.flat], " ")
	]);

	function join(a, s) {
		return a.filter(function(v) {return v}).join(s || ", ");
	}
}
