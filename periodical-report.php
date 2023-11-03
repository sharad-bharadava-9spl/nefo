<?php

	session_start();
	$domain = $_SESSION['domain'];

	$file = "_club/_$domain/periodical-report.php";
	
	if (!file_exists($file)) {
		include "_club/periodical-report.php";
	} else {
		include $file;
	}
	
?>
