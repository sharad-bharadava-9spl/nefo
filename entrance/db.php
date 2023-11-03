<?php
$mysql_hostname = "172.30.205.116:3306";

$mysql_user = "ccs45_greensensationu";
$mysql_password = "l453ewqO128fjorI";
$mysql_database = "ccs45_greensensation";
$con = mysqli_connect($mysql_hostname, $mysql_user, $mysql_password, $mysql_database);

//Check connection
if(mysqli_connect_errno()){
	echo "Failed to connect to MySQL:".mysqli_connect_errno();
}
?>