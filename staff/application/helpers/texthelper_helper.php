<?php 

function dd($string, $exit = true){
	echo '<pre>';
	var_dump($string);
	echo '</pre>';
	if( $exit == true ){
		exit();
	}
}
