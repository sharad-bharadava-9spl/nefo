<?php

	session_start();
	$domain = $_SESSION['domain'];

	$file = "_club/_$domain/status2.php";
	
	if (!file_exists($file)) {
		include "_club/status2.php";
	} else {
		include $file;
	}
	
?>
