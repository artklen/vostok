<?php

d()->route('/test_payment', static function() {
    if (iam('developer')) {
        d()->emit('aquiring.successfull_paid', [d()->Order->f(2)]);
    }
});