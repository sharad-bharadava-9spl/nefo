<?php

if (isset($_POST['RegTemp']) && !empty($_POST['RegTemp'])) {
		
    	include 'include/global.php';
    	include 'include/function.php';
		
		$data 		= explode(";",$_POST['RegTemp']);
		$vStamp 	= $data[0];
		$sn 		= $data[1];
		$user_id	= $data[2];
		$regTemp 	= $data[3];
		
		$device = getDeviceBySn($sn);
		
		$salt = md5($device[0]['ac'].$device[0]['vkey'].$regTemp.$sn.$user_id);
		
		if (strtoupper($vStamp) == strtoupper($salt)) {
			
			//$sql1 		= "SELECT MAX(finger_id) as fid FROM demo_finger WHERE user_id=".$user_id;
			$sql1 		= "SELECT MAX(finger_id) as fid FROM employees WHERE empno=".$user_id;
			$result1 	= mysql_query($sql1);
			$data 		= mysql_fetch_array($result1);
			$fid 		= $data['fid'];
			
			if ($fid == 0) {
			//	$sq2 		= "INSERT INTO demo_finger SET user_id='".$user_id."', finger_id=".($fid+1).", finger_data='".$regTemp."' ";
				$sq2 		= "INSERT INTO employees SET empno='".$user_id."', finger_id=".($fid+1).", finger_data='".$regTemp."' ";
				$result2	= mysql_query($sq2);
				if ($result1 && $result2) {
					$res['result'] = true;
					mysql_query('update users set fptemplate1=1 where user_id='.$user_id);				
				} else {
					$res['server'] = "Error insert registration data!";
				}
			} else {
				$res['result'] = false;
				$res['user_finger_'.$user_id] = "Template already exist.";
			}
			
			echo "empty"; //new page to open
			
		} else {
			
			$msg = "Parameter invalid..";
			
			echo $base_path."messages.php?msg=$msg";
			
		}

		
}

?>