<?php 

function dd($string, $exit = true, $text = ''){
	echo '<pre>';
	echo $text;
	var_dump($string);
	echo '</pre>';
	if( $exit == true ){
		exit();
	}
}
function shuffle_assoc(&$array){
	
	$keys = array_keys($array);
	shuffle($keys);

	foreach( $keys as $key ){
		$new[$key] = $array[$key];
	}
	$array = $new;
	return true;
}
