<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
		header("Location: uTil/logout-redir.php");
		exit();

/*		
	if ($_GET['abyT'] != 223) {
		echo "Incorrect parameter. No action taken.";
	} else {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < 8; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	
		$oldurl = dirname(__FILE__);
		$newurl = dirname(__DIR__) . "/_" . $randomString;
		rename($oldurl, $newurl);
		header("Location: https://www.google.es");
	}*/
