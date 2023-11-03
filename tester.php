<?php

	session_start();
	
	if ($_COOKIE['chkc'] == '84315681') {
		
		echo "Cookie set";
		
	} else {
		
		echo "No cookie set :-(" . $_COOKIE['chkc'];
		
	}