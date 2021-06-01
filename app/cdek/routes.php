<?php

d()->route('/cdek/load_cities', function() {
    if (d()->CdekCitiesLoader->run()) {
        print 'OK';
    }
    exit;
});
