<?php

	session_start();
	$domain = $_SESSION['domain'];

	$file = "_club/_$domain/provider2.php";
	
	if (!file_exists($file)) {
		include "_club/provider2.php";
	} else {
		include $file;
	}
	
?>
