<?php

	session_start();
	
	if ($_COOKIE['chkck'] == '15984335') {
		
		setcookie( "chkck", "15984335", time() - 3600 );
		
	}
	
	echo "Done";