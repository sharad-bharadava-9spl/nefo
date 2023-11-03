<?php
  include_once 'fix_mysql.inc.php';
	/*ini_set("display_errors", 0);
	error_reporting(0);*/

	$base_path		= "https://ccsnube.com/ttt/fingerprint_scanner/";
	$db_name		="ccs_demo";// "demo_flexcodesdk";
	$db_user		= "konstant";
	$db_pass		= "0UXKS7QeMNeayHbJ";
	$db_host		= "127.0.0.1:3306";
	$time_limit_reg = "15";
	$time_limit_ver = "10";

	$conn = mysql_connect($db_host, $db_user, $db_pass);
	if (!$conn) die("Connection for user $db_user refused!");
	mysql_select_db($db_name, $conn) or die("Can not connect to database!");
?>