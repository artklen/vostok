<?php 
d()->as_legal_person =function($v,$f,$object){
	if($object->users_type!=='legal_person'){
		return '';
	}
	return

  "Юридическое название"             .": " .     h($object->legal_name    )                     .  "<br>".
  "Юридический адрес"                .": " .     h($object->legal_address    )                  .  "<br>".
  "Банк"                             .": " .     h($object->bank       )                        .  "<br>".
  "Расчетный счет"                   .": " .     h($object->operating_account  )                .  "<br>".
  "Кор. счет"                        .": " .     h($object->correspondent_account    )          .  "<br>".
  "ИНН"                              .": " .     h($object->tin      )                          .  "<br>".
  "КПП"                              .": " .     h($object->tax_registration_reason_code)       .  "<br>".
  "БИК"                              .": " .     h($object->bic    )                            .  "<br>".
  "ОКПД"                             .": " .     h($object->okpdtr   )                          .  "<br>".
  "ОКПО"                             .": " .     h($object->okpo   )                            .  "<br>".
  "ОКОНХ/ОКВЭД"                      .": " .     h($object->okved )                             .  "<br>";
	
};