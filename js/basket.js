Basket = (function () {
    let itemsTimeout;
    let widgetIndex = 0;

    return Object.freeze({
        add: callback('add_item'),
        update: callback('update_item'),
        delete: callback('delete_item'),
        set_delivery_type: callback('set_delivery_type'),
        set_delivery_cdek_city: callback('set_delivery_cdek_city'),
        set_delivery_cdek_point: callback('set_delivery_cdek_point'),
        set_delivery_cdek_courier_city: callback('set_delivery_cdek_courier_city'),
        set_delivery_cdek_courier_address: callback('set_delivery_cdek_courier_address'),
        set_delivery_post_address: callback('set_delivery_post_address'),
        set_pay_type: callback('set_pay_type'),
        refresh: refresh,
        popup: popup,
        cdek_point_select: cdekPointSelect,
        cdek_courier_select: cdekCourierSelect,
        postSelect: postSelect
    });

    /**
     * @param method
     * @returns {function(object, ?Element)}
     */
    function callback(method) {
        return function (data, target) {
            if (itemsTimeout) {
                clearTimeout(itemsTimeout);
            }
            itemsTimeout = setTimeout(function (data, target) {
                return function () {
                    query(window.langlink + '/basket/' + method, data, target);
                };
            }(data, target), 200);
        };
    }

    function query(action, data, target) {
        $('.js-basket-widget:not([data-basket-widget-id])').each(function () {
            $(this).attr('data-basket-widget-id', widgetIndex).data('basket-widget-id', widgetIndex++);
        });
        var widgets = $('.js-basket-widget');
        if (target !== void 0) {
            widgets = widgets.not($(target).parents('.js-basket-widget'));
        }
        var widgets_data = {};
        widgets.each(function () {
            var t = $(this);
            widgets_data[t.data('basket-widget-id')] = t.data('basket-widget-type');
        });
        if (typeof data === 'undefined') {
            data = {};
        }
        data.widgets = widgets_data;
        ajax_send(action, data);
    }

    function refresh(data) {
        if (data.total_number) {

            $(".js-basket-not-empty").show();
            $(".js-basket-empty").hide();

            /* общая очистка нужна для объектов, удаленных из корзины */
            (function () {
                // $('.js-basket-item-exists').hide();
                // $('.js-basket-item-not-exists').show();
                // $('.js-basket-item-not-enough').show();
                // $('input[data-basket-input]').val('');
                // $('[data-basket-number]').text('');
            }());

            /* обновление виджетов */
            (function () {
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
            (function () {
                $('.js-basket-total-number').text(data.total_number);
                $('.js-basket-total-price').text(data.total_price);
                $('.js-basket-total-weight').text(data.total_weight);
                $('.js-basket-order-price').text(data.order_price);
                $('.js-basket-products-price').text(data.products_price);
                $('.js-basket-delivery-price').text(data.delivery_price);
                $('.js-basket-delivery-price-container').toggle(data.delivery_price !== '0');
                $('.js-basket-delivery-working-days').text(data.delivery_working_days);
                $('.js-basket-delivery-working-days-container').toggle(data.delivery_working_days !== '');
                $('.js-basket-is-free-delivery-container').toggle(data.is_free_delivery);
                $('.js-basket-errors-container').toggle(data.errors.length !== 0);
                $('.js-basket-errors').html(errorsHtml(data.errors));

                $('.js-basket-delivery-cdek-point-city-title').text(data.delivery_cdek_point_city_title);
                $('.js-basket-delivery-cdek-point-city-title-container').toggle(data.delivery_cdek_point_city_title !== '');
                $('.js-basket-delivery-cdek-point-title').text(data.delivery_cdek_point_title);
                $('.js-basket-delivery-cdek-point-title-container').toggle(data.delivery_cdek_point_title !== '');

                $('.js-basket-delivery-cdek-courier-address').text(data.delivery_cdek_courier_address);
                $('.js-basket-delivery-cdek-courier-address-container').toggle(data.delivery_cdek_courier_address !== '');

                $('.js-basket-cash-on-delivery-title')
                    .text(data.cash_on_delivery_title)
                    .attr('title', data.cash_on_delivery_title)
                    .closest('.js-sumoselect').each(function() {
                        this.sumo && this.sumo.reload();
                    })

                const delivery_cdek_courier_city = data.delivery_cdek_courier_city[0];
                if (delivery_cdek_courier_city !== void 0) {
                    $('.js-basket-delivery-cdek-courier-city-title-container').show();
                    set('.js-basket-delivery-cdek-courier-city-title', delivery_cdek_courier_city.title);
                    set('.js-basket-delivery-cdek-courier-city-code', delivery_cdek_courier_city.code);
                    set('.js-basket-delivery-cdek-courier-city-fias', delivery_cdek_courier_city.fias);
                } else {
                    $('.js-basket-delivery-cdek-courier-city-container').hide();
                    set('.js-basket-delivery-cdek-courier-city-title', '');
                    set('.js-basket-delivery-cdek-courier-city-code', '');
                    set('.js-basket-delivery-cdek-courier-city-fias', '');
                }

                $('.js-basket-delivery-post-address').text(data.delivery_post_address);
                $('.js-basket-delivery-post-address-container').toggle(data.delivery_post_address !== '');


                function set(selector, value) {
                    $(selector).each(function () {
                        const element = $(this);
                        if (element.is(':input')) {
                            element.val(value);
                            return;
                        }
                        element.text(value);
                    });
                }

                function errorsHtml(errors) {
                    if (errors.length === 0) {
                        return '';
                    }

                    const ul = $('<ul>');
                    ul.attr('class', 'alert alert-danger');

                    for (const i in errors) {
                        if (!errors.hasOwnProperty(i)) {
                            continue;
                        }

                        const li = $('<li>');
                        li.text(errors[i]);
                        ul.append(li);
                    }

                    return ul.wrap('<div>').parent().html()
                }
            }());

            /* добавление позиций в корзину */
            (function () {
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
        } else {
            $(".js-basket-not-empty").hide();
            $(".js-basket-empty").show();
        }
        $.fancybox.update();
    }

    function popup(data) {
        const params = Object.assign(
            {},
            window.fancybox_common_params ? window.fancybox_common_params() : {},
            {content: data}
        );
        $.fancybox(params);
    }

    function cdekPointSelect(cityInput, codeInput, map) {
        cdekCityTypeahead(cityInput, codeInput);
        codeInput.change(onChangeCity);
        if (codeInput.val()) {
            onChangeCity();
        }
        return true;

        function onChangeCity() {
            Basket.set_delivery_cdek_city({
                title: cityInput.val(),
                code: codeInput.val()
            }, this);

            $.ajax({
                url: '/basket/load_cdek_delivery_points',
                type: 'get',
                data: {
                    city_code: codeInput.val()
                },
                dataType: 'json',
                success: function (data) {
                    map.empty();

                    map.data('points', data);

                    if (data.length) {
                        map.addClass('map-popup');
                        $('.fancybox-wrap, .fancybox-inner').addClass('map-popup-container');
                    } else {
                        map.removeClass('map-popup');
                        $('.fancybox-wrap, .fancybox-inner').removeClass('map-popup-container');
                    }

                    $.fancybox.update();

                    ymaps_init(mapInit);
                }
            })
        }

        function mapInit() {
            const points = map.data('points');
            const placemarks = new Map();
            const balloonButtonClass = 'js-basket-delivery-point-map-balloon-button';
            const balloonSelectedCaptionClass = 'js-basket-delivery-point-map-balloon-selected-caption';

            if (!points || !points.length) {
                return;
            }

            const ymap = new ymaps.Map(mapId(), {
                controls: ['zoomControl'],
                center: stringToCoords(points[0].coords),
                zoom: 12
            });

            for (const i in points) {
                if (!points.hasOwnProperty(i)) {
                    continue;
                }

                const point = points[i];
                const code = point.code;

                const placemark = new ymaps.Placemark(stringToCoords(point.coords), {
                    balloonContentHeader: point.title,
                    balloonContentBody: balloonContentBody(point)
                }, {
                    iconLayout: 'islands#icon',
                    iconColor: '#e69b6c'
                });

                placemark.events.add('click', function () {
                    updateBalloonsElements();
                    activatePoint(code);
                });

                placemarks.set(code, placemark);

                ymap.geoObjects.add(placemark);
            }

            ymap.behaviors.disable('scrollZoom');

            map.on('click', '.' + balloonButtonClass, function () {
                const code = $(this).data('point-code');
                savePoint(code);
                selectPoint(code);
            });

            updateBalloonsElements();

            openPlacemark(selectedPoint());

            function mapId() {
                const existingId = map.attr('id');
                if (existingId) {
                    return existingId;
                }

                if (mapId.counter === void 0) {
                    mapId.counter = 0;
                }
                mapId.counter++;

                const newId = 'js-basket-delivery-point-map-' + mapId.counter;
                map.attr('id', newId);
                return newId;
            }

            function activePoint() {
                return map.data('active-point');
            }

            function activatePoint(code) {
                map.data('active-point', code);

                openPlacemark(code);
            }

            function selectedPoint() {
                return map.data('selected-point');
            }

            function selectPoint(code) {
                map.data('selected-point', code);

                updateBalloonsElements();
            }

            function balloonContentBody(point) {
                return '' +
                    '<div class="ymap-balloon-content">' +

                    '<p><b>' + point.name + '</b></p>' +
                    '<p>' + point.address + '</p>' +
                    '<p>' + point.comment + '</p>' +
                    '<p>' + point.workingHours + '</p>' +
                    '<div style="width:140px;height:60px;">' +

                    '<div' +
                    ' class="' + balloonSelectedCaptionClass + ' ymaps-point-selected-caption"' +
                    ' data-point-code="' + point.code + '"' +
                    ' style="display:none;"' +
                    '>' +
                    'Магазин выбран<br>' +
                    '<button' +
                    ' type="button"' +
                    ' onclick="$.fancybox.close();"' +
                    '>Готово</button>' +
                    '</div>' +

                    '<button' +
                    ' type="button"' +
                    ' class="ymaps-select-point-button ' + balloonButtonClass + '"' +
                    ' style="display:none;"' +
                    ' data-point-code="' + point.code + '"' +
                    '>Выбрать</button>' +

                    '</div>' +

                    '</div>';
            }

            function updateBalloonsElements() {
                setTimeout(function () {
                    const code = selectedPoint();
                    $('.' + balloonButtonClass).each(function () {
                        const isSelected = balloonPointCode(this) === code;
                        $(this).toggle(!isSelected);
                    });
                    $('.' + balloonSelectedCaptionClass).each(function () {
                        const isSelected = balloonPointCode(this) === code;
                        $(this).toggle(isSelected);
                    });
                }, 100);
            }

            function balloonPointCode(element) {
                return $(element).data('point-code');
            }

            function openPlacemark(code) {
                if (!placemarks.has(code)) {
                    return;
                }

                const placemark = placemarks.get(code);

                centerMapOn(placemark).then(function () {
                    open(placemark);
                });

                function centerMapOn(placemark) {
                    return placemark.getMap()
                        .panTo(coords(placemark), {
                            flying: false,
                            safe: true,
                            duration: 500,
                            delay: 0
                        });
                }

                function coords(placemark) {
                    return placemark.geometry.getBounds()[0];
                }

                function open(placemark) {
                    if (!placemark.balloon.isOpen()) {
                        placemark.balloon.open();
                        updateBalloonsElements();
                    }
                }
            }

            function savePoint(code) {
                const params = {
                    code: code,
                    title: pointTitle(code)
                };
                Basket.set_delivery_cdek_point(params, map);

                function pointTitle(code) {
                    return points.reduce(function (result, point) {
                        if (point.code === code) {
                            return point.name;
                        }
                        return result;
                    }, '');
                }
            }
        }
    }

    function cdekCourierSelect(cityInput, cityCodeInput, cityFiasInput, addressInput, token) {
        cdekCityTypeahead(cityInput, cityCodeInput, cityFiasInput);
        cityCodeInput.change(onChangeCity);
        if (cityCodeInput.val()) {
            initAddressSuggestions();
        }
        addressInput.change(onChangeAddress);
        return true;

        function onChangeCity() {
            Basket.set_delivery_cdek_courier_city({
                code: cityCodeInput.val()
            }, this);

            clearAddress();
            initAddressSuggestions();
        }

        function clearAddress() {
            addressInput.val('');

            const suggestions = addressInput.suggestions();
            if (suggestions) {
                suggestions.clear();
                suggestions.clearCache();
                suggestions.dispose();
            }
        }

        function initAddressSuggestions() {
            addressInput.suggestions(Object.assign(
                {},
                common(),
                location()
            ));

            function common() {
                return {
                    token: token,
                    type: 'ADDRESS',
                    onSelect: addressSelectHandler
                };
            }

            function location() {
                const fiasId = cityFiasInput.val();

                if (!fiasId) {
                    return {constraints: cityInput.val()};
                }

                return {
                    constraints: {
                        locations: {
                            city_fias_id: fiasId
                        }
                    },
                    restrict_value: true
                };
            }
        }

        /** @this HTMLInputElement */
        function addressSelectHandler(suggestion) {
            $(this)
                .data('dadata-source', $(this).val())
                .data('dadata', suggestion)
                .change()
        }

        function onChangeAddress() {
            const
                address = addressInput.val(),
                dadata = inputDadata(addressInput),

                params = addressParams(address, dadata);

            Basket.set_delivery_cdek_courier_address(params, this);
        }

        function addressParams(address, dadata) {
            return {
                address: address,
                street: resolveStreet(dadata),
                house: resolveHouse(dadata),
                hull: resolveHull(dadata),
                apartment: resolveApartment(dadata),
                dadata: JSON.stringify(dadata)
            };
        }
    }

    function postSelect(addressInput, token) {
        addressInput.suggestions({
            token: token,
            type: 'ADDRESS',
            onSelect: addressSelectHandler
        });

        addressInput.change(addressChange);

        /** @this HTMLInputElement */
        function addressSelectHandler(suggestion) {
            $(this)
                .data('dadata-source', $(this).val())
                .data('dadata', suggestion)
                .change()
        }

        function addressChange() {
            const dadata = inputDadata(addressInput);
            const index = resolvePostIndex(dadata);

            Basket.set_delivery_post_address({
                address: addressInput.val(),
                index: index,
                dadata: JSON.stringify(dadata)
            }, addressInput);
        }
    }

    function cdekCityTypeahead(cityInput, codeInput, fiasInput) {
        cityInput
            .typeahead({
                hint: false,
            }, {
                display: 'title',
                limit: 20,
                async: true,
                source: function (query, sync, async) {
                    $.ajax({
                        type: 'get',
                        url: '/basket/load_cdek_delivery_cities',
                        data: {
                            q: query
                        },
                        dataType: 'json',
                        success: function (data) {
                            if (data.length) {
                                async(data);
                            }
                        }
                    });
                },
                templates: {
                    suggestion: function (item) {
                        const result = $('<div>');
                        result.addClass('tt-suggestion');

                        result.text(item.title);

                        if (item.subtitle) {
                            const subtitle = $('<span>');
                            subtitle.addClass('subtitle');
                            subtitle.text(item.subtitle);
                            result.append(subtitle);
                        }

                        return result;
                    }
                }
            })

            .on('typeahead:select', function (event, suggestion) {
                if (!suggestion.code) {
                    return;
                }

                codeInput.val(suggestion.code);
                if (fiasInput) {
                    fiasInput.val(suggestion.fias || '');
                }

                codeInput.change();
            })
    }

    function inputDadata(input) {
        if (!isValueSelectedFromSuggestion()) {
            return void 0;
        }
        return input.data('dadata');

        function isValueSelectedFromSuggestion() {
            return input.val() === input.data('dadata-source');
        }
    }

    function resolveStreet(dadata) {
        if (!dadata || !dadata.data || !dadata.data.street) {
            return '';
        }
        return dadata.data.street;
    }

    function resolveHouse(dadata) {
        if (!dadata || !dadata.data || !dadata.data.house) {
            return '';
        }
        return dadata.data.house;
    }

    function resolveHull(dadata) {
        if (!dadata || !dadata.data || !dadata.data.block) {
            return '';
        }
        return dadata.data.block;
    }

    function resolveApartment(dadata) {
        if (!dadata || !dadata.data || !dadata.data.flat) {
            return '';
        }
        return dadata.data.flat;
    }

    function resolvePostIndex(dadata) {
        if (!dadata || !dadata.data || !dadata.data.postal_code) {
            return '';
        }
        return dadata.data.postal_code;
    }
}());

$(function () {
    $(document).on('click', '.js-basket-not-empty', function (e) {
        var link = $(e.target).closest('a').attr('href');

        if (link === '/basket') {
            e.preventDefault();
        }
    });

    $(document).on('click', '.js-basket-not-empty a', function (e) {
        location.href = $(this).attr('href');
    });

    var get_basket_data = function (t) {
        var data = {};
        var p = t.closest('[data-product_id]');
        if (p.length) {
            data.product_id = p.data('product_id');
        }

        p = t.closest('[data-products_variant_id]');

        if (!p.length) {
            p = $('.list-1:checked[data-products_variant_id]');
        }

        if (p.length) {
            data.products_variant_id = p.data('products_variant_id');
        }

        return data;
    };

    $(document).on('click', '.js-basket-add', function () {
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

    $(document).on('click', '.js-basket-add-without-hide', function () {
        var that = $(this);

        var data = get_basket_data($(this));
        var input = $('.js-basket-add-input');
        if (input.length) {
            data.number = input.val();
        }
        Basket.add(data);

        that.parent().find(".js-in-basket").removeClass("hide");
    });

    $(document).on('click', '.js-basket-update', function () {
        Basket.update(get_basket_data($(this)));
    });

    $(document).on('click', '.js-basket-delete', function () {
        var data = get_basket_data($(this));

        $('.js-basket-item-exists[data-product_id="' + data.product_id + '" ][data-products_variant_id="' + data.products_variant_id + '"]').remove();

        Basket.delete(data);
    });

    $(document).on('click', '.js-basket-add-minus', function () {
        var t = $(this).closest('.js-basket-add-container').find('.js-basket-add-input');
        var v = t.val();
        if (v > 1) {
            t.val(v - 1);
        }
    });

    $(document).on('click', '.js-basket-add-plus', function () {
        var t = $(this).closest('.js-basket-add-container').find('.js-basket-add-input');
        t.val(1 * t.val() + 1);
    });

    var basket_change_input_callbacks = {};

    var basket_change_input_update = function (t) {
        var item_key = t.closest('[data-basket-item-key]').data('basket-item-key');
        var data = get_basket_data(t);
        data.number = t.val();
        clearTimeout(basket_change_input_callbacks[item_key]);
        basket_change_input_callbacks[item_key] = setTimeout(function (arg) {
            var t = arg;
            return function () {
                Basket.update(data, t);
            };
        }(t), 100);
    };

    $(document).on('change keyup', '.js-basket-change-input', function () {
        basket_change_input_update($(this));
    });

    $(document).on('click', '.js-basket-change-minus', function () {
        var t = $(this).closest('.js-basket-change').find('.js-basket-change-input');
        var v = t.val();
        if (v > 1) {
            t.val(v - 1);
            basket_change_input_update(t);
        }
    });

    $(document).on('click', '.js-basket-change-plus', function () {
        var t = $(this).closest('.js-basket-change').find('.js-basket-change-input');
        t.val(1 * t.val() + 1);
        basket_change_input_update(t);
    });

    $(document).on('change keyup', '.js-delivery-type-select', function () {
        $('.delivery_message').html($('option:selected', this).data('message'));

        const params = {
            delivery_type: $(this).val()
        };
        Basket.set_delivery_type(params, this);
    });

    $(document).on('change keyup', '.js-pay-type-select', function () {
        const params = {
            pay_type: $(this).val()
        };
        Basket.set_pay_type(params, this);
    });
});

(function () {
    const container = '.js-basket-delivery-type-container';
    const content = '.js-basket-delivery-type-details';
    const select = '.js-delivery-type-select';

    $(document).on('change', select, change);

    $(function () {
        $(select, container).each(change);
    });

    /** @this HTMLInputElement */
    function change() {
        const actualType = +$(this).val();

        $(content, $(this).closest(container)).each(function () {
            const isSelected = $(this).data('basket-delivery-type') === actualType;
            $(this).toggleClass('hidden', !isSelected);
        });
    }
}());
