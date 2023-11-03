<?php
$mysql_hostname = "127.0.0.1:3306";

$mysql_user = "ccs_impactgreenu";
$mysql_password = "tcHP7RlGkDv6bNxT";
$mysql_database = "ccs_impactgreen";
$con = mysqli_connect($mysql_hostname, $mysql_user, $mysql_password, $mysql_database);

//Check connection
if(mysqli_connect_errno()){
	echo "Failed to connect to MySQL:".mysqli_connect_errno();
}
?>