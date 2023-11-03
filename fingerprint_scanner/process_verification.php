<?php

if (isset($_POST['VerPas']) && !empty($_POST['VerPas'])) {
		
	include 'include/global.php';
	include 'include/function.php';
	
	$data 		= explode(";",$_POST['VerPas']);
	$user_id	= $data[0];
	$vStamp 	= $data[1];
	$time 		= $data[2];
	$sn 		= $data[3];
	
	$fingerData = getUserFinger($user_id);
	$device 	= getDeviceBySn($sn);
	$sql1 		= "SELECT * FROM users WHERE user_id='".$user_id."'";
	$result1 	= mysql_query($sql1);
	$data 		= mysql_fetch_array($result1);
	$user_name	= $data['first_name'];
		
	$salt = md5($sn.$fingerData[0]['finger_data'].$device[0]['vc'].$time.$user_id.$device[0]['vkey']);
	
	if (strtoupper($vStamp) == strtoupper($salt)) {
		
		$log = createLog($user_name, $time, $sn);
		
		if ($log == 1) {
		
			//echo $base_path."messages.php?user_name=$user_name&time=$time";//new page to open
			echo str_replace('/fingerprint_scanner','',$base_path)."mini-profile.php?finger=yes&user_id=$user_id";//new page to open
		
		} else {
		
			echo $base_path."messages.php?msg=$log";
			
		}
	
	} else {
		
		$msg = "Parameter invalid..";
		
		echo $base_path."messages.php?msg=$msg";
		
	}
}

?>