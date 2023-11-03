<?php

	ini_set("display_errors", "off");
	
	// Defining constants.. Perhaps better served for a separate cfg file?
	define("DEBUG_MODE", false);

	// Define constants for success/error messages
	define("MESSAGESUCCESS", "success");
	define("MESSAGEERROR", "error");

	define("DATABASE_HOST", "172.30.205.116:3306");
	define("USERNAME", "ccs_theplugbcnu");
	define("PASSWORD", "fud/787u%dyxO");
	define("DATABASE_NAME", "ccs_theplugbcn");
	
	
	mysql_connect(DATABASE_HOST, USERNAME, PASSWORD)
		or header("Location: error.html");
	 
	mysql_select_db(DATABASE_NAME)	
		or header("Location: error.html");
