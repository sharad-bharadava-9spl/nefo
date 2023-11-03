<?php

	session_start();
	$domain = $_SESSION['domain'];

	$file = "_club/_$domain/print-dispense.php";
	
	if (!file_exists($file)) {
		include "_club/print-dispense.php";
	} else {
		include $file;
	}
	
?>
