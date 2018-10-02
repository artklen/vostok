<?php

d()->route("/basket", function() {
	d()->use_page_model();
	d()->view->render('/pages/basket.html');
});