<?php
$mysql_hostname = "127.0.0.1:3306";

$mysql_user = "ccs_licornou";
$mysql_password = "DiJdu7OTntWooJtN";
$mysql_database = "ccs_licorno";
$con = mysqli_connect($mysql_hostname, $mysql_user, $mysql_password, $mysql_database);

//Check connection
if(mysqli_connect_errno()){
	echo "Failed to connect to MySQL:".mysqli_connect_errno();
}
?>