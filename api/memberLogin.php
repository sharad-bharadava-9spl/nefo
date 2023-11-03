<?php 
	// created by konstant for member register task on 11-01-2022
    include('connectionM_login.php');
    
	if(!empty($_POST['language'])){
		$lang = $_POST['language'];
	}else{
		$lang = ""; 
	}
    
    if(isset($_REQUEST['password']) && isset($_REQUEST['email']) && isset($_REQUEST['device_id']) && isset($_REQUEST['platform']) && isset($_REQUEST['fcm_key']) && isset($_REQUEST['macAddress']))
    {
   
    		if($_REQUEST['email'] == '' || $_REQUEST['password'] == '')
    		{
				if($lang=='es')
				{	
					$response = array('flag' => '0', 'message' => 'Todos los campos son obligatorios.');
				}else{
					$response = array('flag' => '0', 'message' => 'All fields are mandatory.');
				}
    			//$response = array('flag'=>'0', 'message' => "Fields can not be null.");
				echo json_encode($response); 
    		}
    		else
    		{
    			$crypt_pass = md5($_REQUEST['password'].$_REQUEST['email']);
			    $selectUser = "SELECT * from members WHERE email = '".$_REQUEST['email']."' AND password = '".$crypt_pass."'";  
				$result = $pdo->prepare("$selectUser");
				$result->execute();
				$userCount = $result->rowCount(); 

			    if($userCount == 0){
					if($lang=='es')
					{	
						$response = array('flag'=>'0', 'message' => "E-mail o contraseña incorrectos, inténtelo de nuevo.");
					}else{
						$response = array('flag'=>'0', 'message' => "Wrong e-mail or password, please try again!");
					}
			    	//$response = array('flag'=>'0', 'message' => "Wrong credentials, try again !");
					echo json_encode($response);
			    }else{
			    	
			    	// check if user is verified

			    	$member_row = $result->fetch();
					$member_id = $member_row['id'];

					$verified = $member_row['verified'];

					if($verified == 0){
						if($lang=='es')
						{	
							$response = array('flag'=>'0', 'message' => "La miembro no está verificada todavía!");
						}else{
							$response = array('flag'=>'0', 'message' => "Member is not verified yet!");
						}
						//$response = array('flag'=>'0', 'message' => "Member is not verified yet!");
						echo json_encode($response);
						die();
					}

			    	$email = $_REQUEST['email'];
			    	$device_id = $_REQUEST['device_id'];
			    	$platform = $_REQUEST['platform'];
			    	$fcm_key = $_REQUEST['fcm_key'];
			    	$macAddress = $_REQUEST['macAddress'];
			    	
			    	// update member

			    	$updateMember = sprintf("UPDATE members SET device_id = '%s', platform = '%s', fcm_key = '%s', macAddress = '%s' WHERE email = '%s';", $device_id, $platform, $fcm_key, $macAddress, $email);

		    		try
					{
						$pdo->prepare("$updateMember")->execute();
					}
					catch (PDOException $e)
					{
							$response = array('flag'=>'0', 'message' => $e->getMessage());
					}


					if($lang=='es')
					{	
						$response = array('flag'=>'1', 'message' => '¡Has iniciado la sesión!', 'member_id' => $member_id);
					}else{
						$response = array('flag'=>'1', 'message' => 'Logged in successfully!', 'member_id' => $member_id);
					}
					//$response = array('flag'=>'1', 'message' => 'Member Logged in successfully!', 'member_id' => $member_id);
					$response['data'] = array();
		            $response['data']['username'] = $member_row['username'];
		            $response['data']['email'] = $member_row['email'];

						echo json_encode($response);
			    }

			}
    }
    else
    {
		if($lang=='es')
		{	
			$response = array('flag' => '0', 'message' => 'Todos los campos son obligatorios.');
		}else{
			$response = array('flag' => '0', 'message' => 'All fields are mandatory.');
		}
    	//$response = array('flag'=>'0', 'message' => "All fields are mendatory");
		echo json_encode($response); 
    }
    
