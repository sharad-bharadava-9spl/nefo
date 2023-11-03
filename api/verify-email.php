<?php 
	// created by konstant for member register task on 11-01-2022
    include('connectionM_login.php');


    $key = $_REQUEST['key'];

    // check link for verification

    if(!isset($key) || $key == ''){
    	echo "Invalid link, please check!";
    	die();
    }

    $very_param = base64_decode($key);
    $param_arr = explode(",", $very_param);
    $email = $param_arr[0];
    $token = $param_arr[1];

    $selectUser = "SELECT * from members WHERE email = '".$email."' AND token = '".$token."'";  
	$result = $pdo->prepare("$selectUser");
	$result->execute();
	$userCount = $result->rowCount(); 

	if($userCount > 0){
		$updateUser = "UPDATE members SET verified = 1 WHERE email = '".$email."' AND token = '".$token."'";
		$pdo->prepare("$updateUser")->execute();
		echo "Email verified, please login into the APP!";
		die();
	}else{
		echo "Invalid link, please check!";
    	die(); 
	}