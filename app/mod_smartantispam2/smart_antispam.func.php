<?php 

function smart_antispam($value,$params){
	
	if(d()->smart_antispam['validate_ajax']=='yes' || d()->smart_antispam['validate_ajax']=='true' || d()->smart_antispam['validate_ajax']=='1'){
		if(!AJAX){
			return false;
		}
	}
	if(d()->smart_antispam['validate_javascript']=='yes' || d()->smart_antispam['validate_javascript']=='true' || d()->smart_antispam['validate_javascript']=='1'){
		$seed = smart_antispam_real_seed();
		$found = false;
		foreach($_POST as $key=>$value){
			if($value == $seed){
				$found= true;
			}
		}
		if(!$found){
			return false;
		}
	}

	return true;
}

function smart_antispam_seed(){
	$seed1 = session_id();
	if($seed1==''){
		$seed1 = rand ( 1 , 32766 ) . rand ( 1 , 32766 ) . rand ( 1 , 32766 )  ;
	}
	$seed1 = md5($seed1) ;
	
	$seed2 = substr( md5($_SERVER['DOCUMENT_ROOT']. $_SERVER["SERVER_SOFTWARE"]. $_SERVER["HTTP_X_REAL_IP"]) ,0,9);
	return array($seed1,$seed2);
}
function smart_antispam_javascript(){
	

	$seeds = smart_antispam_seed();
	
	$seed1 = $seeds[0];
	
	
	$delim = rand(3,27);
	$seed1_1 = substr($seed1,0,$delim);
	$seed1_2 = substr($seed1,$delim);
	
	$seed2 = $seeds[1];
	$random_id =  'z'.md5( rand ( 1 , 32766 )) ;
	$random_name =  'g'.md5( rand ( 1 , 32766 )) ;
	
	$seed1_3 = $seed1[3];
	
	print '<input type="hidden" name="'.$random_name.'" id="'.$random_id.'"><script>document.getElementById("'.$random_id.'").value=("'.$seed1_1.'"+"'.$seed1_2.'").replace(/['.$seed2.']/g,"'.$seed1_3.'")</script>';

}

function smart_antispam_real_seed(){
	$seeds = smart_antispam_seed();
	$seed1 = $seeds[0];
	$seed2 = $seeds[1];
	$seed1_3 = $seed1[3];
	return preg_replace('#['.$seed2.']#',$seed1_3,$seed1);
	
}