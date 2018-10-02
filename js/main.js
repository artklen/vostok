$(function(){
	$('.js-pseudo-filter').hide();

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
	$(document).on('click', '.js-facets-2', function() {
		if ($('.js-facets-1').hasClass('active')) {
			close_facets();
		} else {
			open_facets();
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
	if (is_custom_sliders_relation) {
		$('.js-slider-nav').on('setPosition', function(event, slick) {
			if ($('.js-slider-nav').slick('slickCurrentSlide') !== 0) {
				$('.js-slider-nav').slick('slickGoTo', 0);
			}
		});
	}
});

$(function(){
	var uislider = document.getElementById('js-interval-slider');
	noUiSlider.create(uislider,{
		start: [ 0, 90000 ],
		step: 10,
		connect: true,
		range: {
			'min': 0,
			'max': 105500
		}
	});
	
	var inputNumber1 = document.getElementById('num-1');
	var inputNumber2 = document.getElementById('num-2');
	uislider.noUiSlider.on('update', function( values, handle ){
		inputNumber1.value = values[0];
		inputNumber2.value = values[1];	
	});
	inputNumber1.addEventListener('change', function(){
		uislider.noUiSlider.set([inputNumber1.value, inputNumber2.value]);
	});
	inputNumber2.addEventListener('change', function(){
		uislider.noUiSlider.set([inputNumber1.value, inputNumber2.value]);
	});
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
		}
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
				zoom: 16
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

$(document).on('click', '.js-unfolding-button, .js-unfolding-button-menu', function() {
	var t = $(this);
	t.parent().toggleClass('arrow');
	$('.js-unfolding-block, .js-unfolding-block-menu', t.closest('.js-unfolding-container, .js-unfolding-container-menu')).stop(true, false).slideToggle(300);
});

/*$('.js-unfolding-block').hide();
$('.js-unfolding-button, .js-unfolding-block-menu').show();*/
$('.js-unfolding-block-menu').hide();

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
