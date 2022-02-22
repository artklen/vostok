<?php

d()->route('/cdek/load_cities', function() {
    set_time_limit(0);
    if (d()->CdekCitiesLoader->run()) {
        print 'OK';
    }
    exit;
});
