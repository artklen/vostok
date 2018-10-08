jQuery(document).ready(function() {

// Слайдеры
	
$('.product_card_slider').slick({
    slidesToShow: 1,
    infinite: true,
    fade: true,
    arrows: false,
    asNavFor: '.product_card_thumbs',
    responsive: [
    {
      breakpoint: 480,
      settings: {
        dots: true,
      }
    },
  ]
});

$('.product_card_thumbs').slick({
    slidesToShow: 4,
    infinite: true,
    focusOnSelect: true,
    asNavFor: '.product_card_slider',
});

$(document).on('click', '.jq-checkbox, .main_chb_label', function() {
	var _this = this;
	setTimeout(function(){
		$(_this).closest('form').submit();
		
	},400)
})
$('.js-order-balls-input').on('keydown',function(e){
 
	if( (e.keyCode == 13 ) || (e.keyCode == 13 ) ){
		
		$(this).blur();
		return
	}
        
        if ($.inArray(e.keyCode, [46, 8, 9, 27]) !== -1 ||
        
            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
        
            (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
        
            (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
        
            (e.keyCode >= 35 && e.keyCode <= 39)) {
                 return;
        }
     
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
     
})
$('.js-order-balls-input').on('input',function(){
	if($(this).val() != $(this).val()*1){
		if(isNaN($(this).val()*1)){
			$(this).val('')
			$('.js-order-total').html($(this).data('basket')*1 - $(this).val()*1)
			return
		}
		$(this).val($(this).val()*1)
		$('.js-order-total').html($(this).data('basket')*1 - $(this).val()*1)
		 
	}
	
	if( $(this).val()>$(this).data('basket')){
		$(this).val(Math.floor($(this).data('basket')*1))
	}
	
	if( $(this).val()>$(this).data('max')){
		$(this).val($(this).data('max'))
	}
	$(this).val(Math.floor($(this).val())*1)
	$('.js-order-total').html( Math.round (($(this).data('basket')*1 - $(this).val()*1 )*100)/100)
})

// Выбор города

$('.city_select_trigger').click(function(){
    $('.city_select_dd').fadeIn('fast');
    return false;
})

// Закрытие выбора города

$(document).mouseup(function (e) {
    var container = $(".city_select_wrp");
    if (container.has(e.target).length === 0){
        $('.city_select_dd').fadeOut('fast');
    }
});

// Стилизация чекбоксов и радио

$('.main_chb, .main_rad').styler();

// Ползунок цены

$( ".price-range" ).each(function(){
	var $current_element = $(this);
	
	$current_element.slider({
	  range: true,
	  min: Number($current_element.data('value-min')),
	  max: Number($current_element.data('value-max')),
	  values: [ $current_element.parent().find( ".price-min" ).val(), $current_element.parent().find( ".price-max" ).val() ],
	  slide: function( event, ui ) {
		$current_element.parent().find( ".price-min" ).val( ui.values[ 0 ] );
		$current_element.parent().find( ".price-max" ).val( ui.values[ 1 ] );
	  }
	});

	//$current_element.parent().find( ".price-min" ).val( $( ".price-range" ).slider( "values", 0 ) );
	//$current_element.parent().find( ".price-max" ).val( $( ".price-range" ).slider( "values", 1 ) );

});


// Ползунок веса

$( ".weight-range" ).slider({
  range: true,
  min: 0,
  max: 50,
  values: [ 5, 30 ],
  slide: function( event, ui ) {
    $( ".weight-min" ).val( ui.values[ 0 ] );
    $( ".weight-max" ).val( ui.values[ 1 ] );
  }
});

$( ".weight-min" ).val( $( ".weight-range" ).slider( "values", 0 ) );
$( ".weight-max" ).val( $( ".weight-range" ).slider( "values", 1 ) );

$('ul.tabs__caption.js-tabs').on('click', 'li:not(.active)', function() {
    $(this)
      .addClass('active').siblings().removeClass('active')
      .closest('div.tabs').find('div.tabs__content').removeClass('active').eq($(this).index()).addClass('active');
  });
 

$('ul.tabs__caption:not(.js-tabs) li').on('click',   function() {
	
	$('ul.tabs__caption li').each(function(){
		$('.js-order-scenarios').removeClass($(this).data('scenario'))
	});
	$('.js-order-scenarios').addClass($(this).data('scenario'))
	$('.js-delivery-type').val($(this).data('type'))
	
    $(this)
      .addClass('active').siblings().removeClass('active');
    //  .closest('div.tabs').find('div.tabs__content').removeClass('active').eq($(this).index()).addClass('active');
  });
	$('ul.tabs__caption li.active').click();
// Фиксированная шапка

$(window).scroll(function() {
  var top = $(document).scrollTop();
  if (top < 1){
    $( ".top_block" ).removeClass("fixed_header");
  }
  else {
    $( ".top_block" ).addClass("fixed_header");
  }
});

// Появление кнопки наверх

if ($(window).width() > 980) {

  $(window).scroll(function() {
       
    if($(this).scrollTop() > 150) {

      $('.to_top_btn').fadeIn('fast');

    } else {

      $('.to_top_btn').fadeOut('fast');

    }

  });

};

// Прокрутка к началу страницы

$('.to_top_btn').click(function() {

  $('body,html').animate({scrollTop:0},1000);
  return false;

});

// Попапы

$(document).on('click', '.popup_overlay, .close_popup, .close_sidebar, .js-close_popup', function() {
  $('.popup_overlay, .popup, .mob_sidebar').fadeOut('fast');
  return false;
});

$('.popup_btn-1').click(function() {
  $('.popup, .mob_sidebar').fadeOut('fast');
  $('.popup_overlay, .popup-1').fadeIn('fast');
  return false;
});

$('.popup_btn-2').click(function() {
  $('.popup').fadeOut('fast');
  $('.popup_overlay, .popup-2').fadeIn('fast');
  return false;
});

$('.popup_btn-3').click(function() {
  $('.popup').fadeOut('fast');
  $('.popup_overlay, .popup-3').fadeIn('fast');
  return false;
});

$('.popup_btn-4').click(function() {
  $('.popup_overlay, .popup-4').fadeIn('fast');
  return false;
});

$('.popup_btn-5').click(function() {
  $('.popup_overlay, .popup-5').fadeIn('fast');
  return false;
});

// Мобильное меню
//js-return-to-mobile-menu
//js-sidebar-submenu
//js-open-subpopup
//js-mobile-categories-menu


$('.js-return-to-mobile-menu').click(function() {
	var current_id = $(this).data('id')
	
	
  $('.popup_overlay, .js-sidebar-submenu').hide()
  $('.js-mobile-categories-menu').show()
  
  
  return false;
});
 
$('.js-open-subpopup').click(function() {
	var current_id = $(this).data('id')
	
	
  $('.popup_overlay, .js-sidebar-submenu').hide()
  $('.js-mobile-categories-menu').hide()
  $('.popup_overlay, .js-sidebar-submenu[data-id='+current_id+']').show();
  
  return false;
});
 
$('.mob_menu_btn-1').click(function() {
  $('.popup_overlay, .mob_sidebar-1').fadeIn('fast');
  return false;
});
 
 

$('.mob_menu_btn-2').click(function() {
  $('.popup_overlay, .mob_sidebar-2').fadeIn('fast');
  return false;
});

$('.mob_menu_btn-3').click(function() {
  $('.popup_overlay, .mob_sidebar-3').fadeIn('fast');
  return false;
});

$('.menu_btn').click(function() {
  $('.popup_overlay, .js-mobile-categories-menu').fadeIn('fast');
  return false;
});

// Раскрывающееся меню в футере

$('.footer_nav_group .ttl').click(function() {
  if ($(window).width() < 780) {
    $(this).toggleClass('opened');
    $(this).siblings('ul').slideToggle('fast');
  };
});

// Раскрывающийся текст в карточке товара

$('.product_card_mob_dd_ttl').click(function() {
  if ($(window).width() < 780) {
    $(this).toggleClass('opened');
    $(this).siblings('.product_card_mob_dd_txt').slideToggle('fast');
  };
});

// Поиск в мобильной версии

$('.mob_search_btn').click(function() {
  if ($(window).width() < 1140) {
    $('.search_field_wrp').fadeToggle('fast');
    return false;
  };
});

$(document).mouseup(function (e) {
  var container = $(".search_field_wrp, .mob_search_btn");
  if ($(window).width() < 1140) {
    if (container.has(e.target).length === 0){
        $('.search_field_wrp').fadeOut('fast');
    }
  };
});

// Корзина в мобильной версии

$('.cart_trigger').click(function() {
  if ($(window).width() < 1140 && $(window).width() > 480) {
    $('.cart_dropdown').fadeToggle('fast');
    return false;
  };
});

$(document).mouseup(function (e) {
  var container = $(".cart_wrp");
  if ($(window).width() < 1140 && $(window).width() > 480) {
    if (container.has(e.target).length === 0){
        $('.cart_dropdown').fadeOut('fast');
    }
  };
});

});

Basket.popup = function(data) {
	$('.popup-5').html(data);
	$('.popup_overlay, .popup-5').fadeIn('fast');
};
