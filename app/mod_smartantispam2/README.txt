Как подключить (форма должна быть Аяксовая):

1. Добавить в форму вывод {{smart_antispam_javascript}} (или сделать это в функции feedback_form, если используется).
Это безопасно, можно хоть в каждую форму подключать.

Пример 1:
	{{form '/send_blabla', 'type' => 'ajax'}}
		{{smart_antispam_javascript}}
		<div class="text">
			<span>Введите свой e-mail</span> 
			<span>и мы вышлем вам DWG-проект</span>
		</div>
		<!-- тут всякие поля -->
	{{/form}}
	
Пример 2:
	d()->feedback_form = function($params) {
		$result = d()->simple_ajax_form($params);
		// ..
		
		$result .= d()->smart_antispam_javascript();
		return $result;
	};



2. добавить в валидатор smart_antispam, например:

	[validator./feedback/request_exclusive_color.email]
	valid_email_or_empty.message=Введите корректный адрес электронной почты

	[validator./feedback/call.phone]
	check_no_letters.message=Телефон не должен содержать буквы
	valid_phone.message=Введите корректный номер телефона
	smart_antispam.message=Ваше сообщение похоже на спам

	
