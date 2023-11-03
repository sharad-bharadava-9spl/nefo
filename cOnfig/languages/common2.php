<?php
session_start();
header('Cache-control: private'); // IE 6 FIX
 
	if (isSet($_GET['lang'])) {
		$lang = $_GET['lang'];
		$_SESSION['lang'] = $lang;
		
 
		// register the session and set the cookie
//		setcookie('lang', $lang, time() + (3600 * 24 * 30));
	} else if (isSet($_SESSION['lang'])) {
		$lang = $_SESSION['lang'];
	} /*else if (isSet($_COOKIE['lang'])) {
		$lang = $_COOKIE['lang'];
	}*/ else {
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
 
	include_once '../cOnfig/languages/' . $lang_file;
?>