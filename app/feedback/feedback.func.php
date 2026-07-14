<?php
$url_base = '/feedback/';

d()->get($url_base . ':name', function($name) {
	if (strpos($name, '.') !== false || !is_file(__DIR__ . "/$name.html")) {
		d()->page_not_found();
	}
	d()->form_type = $name;
	$result = '' . d()->view->render("/feedback/$name.html");
	if (AJAX) {
		print $result;
		exit;
	}
	return $result;
});

d()->post($url_base . ':name', function($name) {
	if (isset(d()->feedback_form[$name]) && d()->validate(d()->url_path) && strpos(d()->params['email'],"example.com") === false) {
		$stmt = d()->db->query('show tables like "feedbacks"');
		if (!$stmt || !$stmt->fetchColumn()) {
			d()->Scaffold->create_table('feedbacks');
		}
		$feedback = d()->Feedback->new;
		$feedback->type = $name;
		$feedback->title = d()->feedback_form[$name]['title'];
		$template = "feedback/$name";
		if (!is_file(__DIR__ . "/../letter/$template.html")) {
			$template = 'feedback/default';
		}
		$feedback->text = d()->letter->render($template, 'clear');
		if (isset(d()->feedback_form[$name]['fields'])) {
			if (is_array(d()->feedback_form[$name]['fields'])) {
				foreach (d()->feedback_form[$name]['fields'] as $field) {
					$feedback->$field = isset(d()->params[$field]) ? d()->params[$field] : '';
				}
			} else {
				$field = d()->feedback_form[$name]['fields'];
				$feedback->$field = isset(d()->params[$field]) ? d()->params[$field] : '';
			}
		}
		unset(d()->params['_element'], d()->params['_action'], d()->params['_is_simple_names']);
		$feedback->form_data = json_encode(d()->params, JSON_FORCE_OBJECT);
		$feedback->save();
		
		if (!empty(d()->feedback_form[$name]['tables'])) {
			foreach (d()->feedback_form[$name]['tables'] as $table => $fields) {
				$orm = activerecord_factory_from_table($table);
				$create = array();
				foreach ($fields as $table_field => $params_field) {
					if (is_numeric($table_field)) {
						$table_field = $params_field;
					}
					if ($params_field === 'NOW') {
						$create[$table_field] = date('Y-m-d');
					} else {
						$create[$table_field] = d()->params[$params_field];
					}
				}
				$orm->create($create);
			}
		}
		
		d()->notification->feedback($name);
		$redirect = d()->langlink . (isset(d()->feedback_form[$name]['redirect']) ? d()->feedback_form[$name]['redirect'] : '/thankyou');
		if (AJAX) {
			if (isset($_POST['is_modal'])) {
				print 'fancybox_unlock();';
			}
			if (!empty(d()->feedback_form[$name]['success_function'])) {
				print '_current_form.find(".error").removeClass("error");';
				print '_current_form.find(".has-error").removeClass("has-error");';
				print '_current_form.find(".js-clear-on-success").val("");';
				print 'window.' . d()->feedback_form[$name]['success_function'] . '();';
			} else if (!empty(d()->feedback_form[$name]['success_message'])) {
				print '_current_form.find(".error").removeClass("error");';
				print '_current_form.find(".has-error").removeClass("has-error");';
				print '_current_form.find(".js-clear-on-success").val("");';
				print '_current_form.prepend(' . json_encode('<div class="alert alert-success js-closeable"><button type="button" class="close js-close-button" aria-label="' . t('Закрыть') . '"><span aria-hidden="true">×</span></button>' . d()->feedback_form[$name]['success_message'] . '</div>') .  ');';
			} else {
				print 'document.location.href="' . $redirect . '";';
			}
		} else {
			header('Location: ' . $redirect);
		}
	} else {
		if (AJAX) {
			if (d()->notice()) {
				print '_current_form.find(".js-notice").html(' .  json_encode(d()->notice(array('style' => '', 'class' => 'alert alert-danger'))) .  ');';
			}
			if (isset($_POST['is_modal'])) {
				print 'fancybox_unlock();';
			}
			d()->reload();
		}
	}
	exit;
});
