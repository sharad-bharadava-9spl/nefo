<?php
require_once 'cOnfig/connection.php';
session_start();

 $insertTime = date("Y-m-d H:i:s");

if (!isset($_POST['video_action'])) {
	echo "ERROR - df67!";
	exit();
} else {

	$video_action = $_POST['video_action'];
	$domain = $_POST['domain'];
	$user_id = $_POST['user_id'];
	$video_id = $_POST['video_id'];


	try
	{
		$result = $pdo2->prepare("INSERT INTO video_views (insertTime, domain, user_id, video_id, action) VALUES ('$insertTime','$domain','$user_id','$video_id', '$video_action')");
		$result->execute();
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching user: ' . $e->getMessage();
		echo $error;
		exit();
	}

}

die;