<?php

class Notification {
	
	function feedback($name)
	{
		$template = "feedback/$name";
		if (!is_file(__DIR__ . "/../letter/$template.html")) {
			$template = 'feedback/default';
		}
		$message = d()->letter->render($template);
		$emails = explode(',', d()->Option->feedback_email);
		foreach ($emails as $email) {
			$email = trim($email);
			if ($email === '') {
				continue;
			}
			d()->mail->setFrom(array($_ENV['EMAIL_FROM_ADDRESS'] => $_ENV['EMAIL_FROM_NAME']));
			d()->mail->setTo($email);
			d()->mail->setSubject(d()->feedback_form[$name]['title']);
			d()->mail->setBody($message, 'text/html');
			d()->mail->send();
		}
	}
	
}
