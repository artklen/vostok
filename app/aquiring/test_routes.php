<?php

d()->route('/test_payment', static function() {
    d()->emit('aquiring.successfull_paid', [d()->Order->f(2)]);
});