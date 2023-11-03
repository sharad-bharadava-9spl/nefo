<?php
session_start();
header('Cache-control: private'); // IE 6 FIX
 
	if (isSet($_GET['lang'])) {
		
		$lang = $_GET['lang'];
		$_SESSION['lang'] = $lang;
		
	} else if (isSet($_SESSION['lang'])) {
		
		$lang = $_SESSION['lang'];
		
	} else {
		
		$lang = 'en';
		
	}
 
	switch ($lang) {
	  case 'en':
	  $lang_file = 'english.php';
	  break;
	 
	  case 'es':
	  $lang_file = 'spanish.php';
	  break;
	 
	  default:
	  $lang_file = 'english.php';
	 
	}
 
	include_once HOST_ROOT . 'cOnfig/languages/' . $lang_file;
?>