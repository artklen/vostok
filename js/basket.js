Basket = (function() {
	var Basket = {};
	var itemsTimeout;
	var widget_i = 0;

	$('body').on('click', '.js-basket-not-empty', function (e)
	{
		var link = $(e.target).closest('a').attr('href');

		if (link == '/basket')
			e.preventDefault();
	});

	$('body').on('click', '.js-basket-not-empty a', function (e)
	{
		location.href = $(e.target).closest('a').attr('href');
	});
	
	var query = function(action, data, target) {
		$('.js-basket-widget').not('[data-basket-widget-id]').each(function() {
			$(this).attr('data-basket-widget-id', widget_i).data('basket-widget-id', widget_i++);
		});
		var widgets = $('.js-basket-widget');
		if (typeof target !== 'undefined') {
			widgets = widgets.not(target.parents('.js-basket-widget'));
		}
		var widgets_data = {};
		widgets.each(function() {
			var t = $(this);
			widgets_data[t.data('basket-widget-id')] = t.data('basket-widget-type');
		});
		if (typeof data === 'undefined') {
			data = {};
		}
		data.widgets = widgets_data;
		ajax_send(action, data);
	};
	
	var create_item_callback = function(method) {
		return function(arg) {
			var method = arg;
			return function(data, target) {
				if (itemsTimeout) {
					clearTimeout(itemsTimeout);
				}
				itemsTimeout = setTimeout(function(arg_data, arg_target) {
					var data = arg_data;
					var target = arg_target;
					return function() {
						query(window.langlink + '/basket/' + method, data, target);
					};
				}(data, target), 200);
			};
		}(method);
	};
	
	Basket.add = create_item_callback('add_item');
	Basket.update = create_item_callback('update_item');
	Basket.delete = create_item_callback('delete_item');
	Basket.delivery = create_item_callback('delivery');
	
	Basket.refresh = function(data) {
		if (data.total_number) {
			console.log("Now basket showing");

			$(".js-basket-not-empty").show();
			$(".js-basket-empty").hide();

			/* общая очистка нужна для объектов, удаленных из корзины */
			(function() {
				// $('.js-basket-item-exists').hide();
				// $('.js-basket-item-not-exists').show();
				// $('.js-basket-item-not-enough').show();
				// $('input[data-basket-input]').val('');
				// $('[data-basket-number]').text('');
			}());

			/* обновление виджетов */
			(function() {
				if (data.widgets_ids && data.widgets) {
					for (var widget_type in data.widgets_ids) {
						if (typeof data.widgets[widget_type] !== 'undefined') {
							var widget_content = data.widgets[widget_type];
							for (var widget_id in data.widgets_ids[widget_type]) {
								$('[data-basket-widget-id="' + data.widgets_ids[widget_type][widget_id] + '"]').html(widget_content);
							}
						}
					}
				}
			}());

			/* отображение общих данных */
			(function() {
				$('.js-basket-total-number').text(data.total_number);
				$('.js-basket-total-price').text(data.total_price);
				$('.js-basket-total-weight').text(data.total_weight);
				$('.js-basket-order-price').text(data.order_price);
			}());

			/* добавление позиций в корзину */
			(function() {
				var list = $("ul.js-basket-list");

				// очистка корзины
				list.find("li.js-basket-item-exists").remove();

				// добавление корзины заново
				for (var key in data.items) {
					var element = data.items[key], template = list.find("template").html();

					template = template.split(':id:').join(element.id);
					template = template.split(':variant_id:').join(element.variant_id);
					template = template.split(':basket_item_id:').join(element.basket_item_id);
					template = template.split(':title:').join(element.title);
					template = template.split(':img_link:').join(element.img_link);
					template = template.split(':price:').join(element.price);
					template = template.split(':count:').join(element.count);
					template = template.split(':link:').join(element.link);

					list.find("li.js-basket-link").before(template);
				}
			}());

			/* товары в корзине */
			// (function() {
			// 	for (var key in data.items) {
			// 		var n = data.items[key];
			// 		$('.js-basket-item-not-exists[data-basket-item-key="' + key +'"], [data-basket-item-key="' + key +'"] .js-basket-item-not-exists').hide();
			// 		$('.js-basket-item-exists[data-basket-item-key="' + key +'"], [data-basket-item-key="' + key +'"] .js-basket-item-exists').show();
			// 		$('input.js-basket-input[name="' + key + '"]').val(1 * n ? n : '');
			// 		$('.js-basket-number[data-basket-item-key="' + key +'"]').text(n);
			// 		$('.js-basket-item-number[data-basket-item-key="' + key +'"], [data-basket-item-key="' + key +'"] .js-basket-item-number').text(n);
			// 		$('.js-basket-item-total[data-basket-item-key="' + key +'"], [data-basket-item-key="' + key +'"] .js-basket-item-total').text(n);
			// 		$('.js-basket-item-total-price[data-basket-item-key="' + key +'"], [data-basket-item-key="' + key +'"] .js-basket-item-total-price').text((data.items_total_price[key]));
			// 		$('.js-basket-item-total-weight[data-basket-item-key="' + key +'"], [data-basket-item-key="' + key +'"] .js-basket-item-total-weight').text((data.items_total_weight[key]));
			// 	}
			// }());

			// /* товары */
			// (function() {
			// 	$('[data-product_id] .js-basket-product-not-exists, .js-basket-product-not-exists[data-product_id]').each(function() {
			// 		var t = $(this);
			// 		if (1 * data.products[t.closest('[data-product_id]').data('product_id')]) {
			// 			t.hide();
			// 		} else {
			// 			t.show();
			// 		}
			// 	});
			// 	$('[data-product_id] .js-basket-product-exists, .js-basket-product-exists[data-product_id]').each(function() {
			// 		var t = $(this);
			// 		if (1 * data.products[t.closest('[data-product_id]').data('product_id')]) {
			// 			t.show();
			// 		} else {
			// 			t.hide();
			// 		}
			// 	});
			// }());
		} else {
			console.log("Now basket hiding");

			$(".js-basket-not-empty").hide();
			$(".js-basket-empty").show();

			// $('.js-basket-item-exists').hide();
			// $('.js-basket-item-not-exists').show();
			// $('.js-basket-product-exists').hide();
			// $('.js-basket-product-not-exists').show();
		}
	};
	
	Basket.popup = function(data) {
		var fancybox_params = window.fancybox_common_params ? window.fancybox_common_params() : {};
		fancybox_params.content = data;
		$.fancybox(fancybox_params);
	};
	
	return Basket;
}());

$(function() {
	
	var get_basket_data = function(t) {
		var data = {};
		var p = t.closest('[data-product_id]');
		if (p.length) {
			data.product_id = p.data('product_id');
		}

		p = t.closest('[data-products_variant_id]');

		if (!p.length)
		{
			p = $('.list-1:checked[data-products_variant_id]');
		}

		if (p.length) {
			data.products_variant_id = p.data('products_variant_id');
		}
		console.log('Basket data:', data);
		return data;
	};
	
	$(document).on('click', '.js-basket-add', function() {
		var that = $(this);

		var data = get_basket_data($(this));
		var input = $('.js-basket-add-input');
		if (input.length) {
			data.number = input.val();
		}
		Basket.add(data);

		that.addClass("hide");
		that.parent().find(".js-in-basket").removeClass("hide");
	});

	$(document).on('click', '.js-basket-add-without-hide', function() {
		var that = $(this);

		var data = get_basket_data($(this));
		var input = $('.js-basket-add-input');
		if (input.length) {
			data.number = input.val();
		}
		Basket.add(data);

		that.parent().find(".js-in-basket").removeClass("hide");
	});
	
	$(document).on('click', '.js-basket-update', function() {
		Basket.update(get_basket_data($(this)));
	});
	
	$(document).on('click', '.js-basket-delete', function() {
		var data = get_basket_data($(this));

		$('.js-basket-item-exists[data-product_id="' + data.product_id + '" ][data-products_variant_id="' + data.products_variant_id + '"]').remove();

		Basket.delete(data);
	});

	//var basket_add_input_callbacks = {};
	//var basket_add_input_update = function(t) {
	//	var item_key = t.closest('[data-basket-item-key]').data('basket-item-key');
	//	var data = get_basket_data(t);
	//	data.number = t.val();
	//	clearTimeout(basket_add_input_callbacks[item_key]);
	//	basket_add_input_callbacks[item_key] = setTimeout(function(arg) {
	//		var t = arg;
	//		return function() {
	//			Basket.add(data, t);
	//		};
	//	}(t), 100);
	//};
	//$(document).on('change keyup', '.js-basket-add-input', function() {
	//	basket_add_input_update($(this));
	//});
	$(document).on('click', '.js-basket-add-minus', function() {
		var t = $(this).closest('.js-basket-add-container').find('.js-basket-add-input');
		var v = t.val();
		if (v > 1) {
			t.val(v - 1);
		}
	});
	$(document).on('click', '.js-basket-add-plus', function() {
		var t = $(this).closest('.js-basket-add-container').find('.js-basket-add-input');
		t.val(1 * t.val() + 1);
	});
	
	var basket_change_input_callbacks = {};
	var basket_change_input_update = function(t) {
		var item_key = t.closest('[data-basket-item-key]').data('basket-item-key');
		var data = get_basket_data(t);
		data.number = t.val();
		clearTimeout(basket_change_input_callbacks[item_key]);
		basket_change_input_callbacks[item_key] = setTimeout(function(arg) {
			var t = arg;
			return function() {
				Basket.update(data, t);
			};
		}(t), 100);
	};
	$(document).on('change keyup', '.js-basket-change-input', function() {
		basket_change_input_update($(this));
	});
	$(document).on('click', '.js-basket-change-minus', function() {
		var t = $(this).closest('.js-basket-change').find('.js-basket-change-input');
		var v = t.val();
		if (v > 1) {
			t.val(v - 1);
			basket_change_input_update(t);
		}
	});
	$(document).on('click', '.js-basket-change-plus', function() {
		var t = $(this).closest('.js-basket-change').find('.js-basket-change-input');
		t.val(1 * t.val() + 1);
		basket_change_input_update(t);
	});
	$(document).on('change keyup', '.js-delivery', function() {
		$('.delivery_message').html($(this).find('option:selected').data('message'));
		var data = {};
		data.delivery_id = $(this).val();
		Basket.delivery(data);
		Basket.refresh;
	});
});
