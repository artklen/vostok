<?php

function dadata_address_deferred_init($input_id, $city) {
    d()->input_id = $input_id;
    d()->city = $city;
    d()->deferred_scripts .= d()->View->partial('/dadata/_init_input.html');
}
