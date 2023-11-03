<?php 
    include('connectionM.php');

    //echo "<pre>";print_r($_REQUEST);exit;

	if(!empty($_POST['language'])){
		$lang = $_POST['language'];
	}else{
		$lang = ""; 
	}
    
    if(isset($_REQUEST['login_code']) && isset($_REQUEST['club_name']) && isset($_REQUEST['member_no']) && isset($_REQUEST['device_id']) && isset($_REQUEST['platform']))
    {
    	try
    	{
    		if($_REQUEST['login_code'] == '' && $_REQUEST['club_name'] == '' && $_REQUEST['member_no'] == '' && $_REQUEST['device_id'] == '' && $_REQUEST['platform'] == '' && $_REQUEST['fcm_key'])
    		{
				if($lang=='es')
				{	
					$response = array('flag'=>'0', 'message' => "Todos los campos son obligatorios.");
				}else{
					$response = array('flag'=>'0', 'message' => "All fields are mendatory.");
				}
    			//$response = array('flag'=>'0', 'message' => "Fields can not be null.");
				echo json_encode($response); 
    		}
    		else
    		{
    			/*try
				{
					$result = $pdo->prepare("SELECT db_pwd FROM db_access WHERE domain = :domain");
					$result->bindValue(':domain', $_REQUEST['club_name']);
					$result->execute();
					//print_r($results); exit;
				}
				catch (PDOException $e)
				{
					$response = array('flag'=>'0', 'message' => $e->getMessage());
				   // echo json_encode($response); 
				}

				$row = $result->fetch();
			    $db_name = 'ccs_'.$_REQUEST['club_name'];
				//echo $db_user = $db_name . "u"; echo "<br>";
				//echo $db_pwd = $row['db_pwd']; exit;
				$db_user = "root";
				$db_pwd = "";
			    try	{
					//echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
			 		$pdo = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
			 		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			 		$pdo->exec('SET NAMES "utf8"');
				}
				catch (PDOException $e)	{
					
			  		$response = array('flag'=>'0', 'message' => $e->getMessage());
					// echo json_encode($response);
			 		
				}*/
				
    			$selectUser = "SELECT * from `users` WHERE `login_code` = '".base64_encode($_REQUEST['login_code'])."' AND `memberno` ='".$_REQUEST['member_no']."'";
				$result = $pdo->prepare("$selectUser");
				$result->execute();
				$userCount = $result->rowCount();
				$userData = $result->fetch();

			    if($userCount > 0){

			    	//update login device and platform
			    	$update_query = "UPDATE `users` SET `device_id`='" .$_REQUEST['device_id']."', `platform`='" .$_REQUEST['platform']."',`fcm_key`='" .$_REQUEST['fcm_key']."' WHERE `user_id`=".$userData['user_id'];
					try
					{
						$results = $pdo->prepare($update_query);
						$res = $results->execute();
                        $user_id = $userData['user_id'];
                        $selectUserdetail = "SELECT * from `users` WHERE user_id = '$user_id'";
						$result = $pdo->prepare("$selectUserdetail");
						$result->execute();
						$userDatatype = $result->fetch();

						/*set user usertype */
		                if($userDatatype['userGroup'] == '1'){
		                    $usertype = 'Admin';
		                }elseif($userDatatype['userGroup'] == '2'){
		                    $usertype = 'Customer';
		                }elseif($userDatatype['userGroup'] == '3'){
		                    $usertype = 'Customer';
		                }elseif($userDatatype['userGroup'] == '4'){
		                    $usertype = 'Customer';
		                }elseif($userDatatype['userGroup'] == '5'){
		                    $usertype = 'Customer';
		                }elseif($userDatatype['userGroup'] == '6'){
		                    $usertype = 'Customer';
		                }elseif($userDatatype['userGroup'] == '7'){
		                    $usertype = 'Customer';
		                }elseif($userDatatype['userGroup'] == '8'){
		                    $usertype = 'Deleted';
		                }elseif($userDatatype['userGroup'] == '9'){
		                    $usertype = 'Inactive';
		                }else{
		                    $usertype = '';
		                }

		                /*if(isset($userData['userGroup']) == 1){
		                    $usertype = 'Admin';
		                }elseif(isset($userData['userGroup']) == 2){
		                    $usertype = 'Worker';
		                }elseif(isset($userData['userGroup']) == 3){
		                    $usertype = 'Volunteer';
		                }elseif(isset($userData['userGroup']) == 4){
		                    $usertype = 'Professional contact';
		                }elseif(isset($userData['userGroup']) == 5){
		                    $usertype = 'Member';
		                }elseif(isset($userData['userGroup']) == 6){
		                    $usertype = 'Visitor';
		                }elseif(isset($userData['userGroup']) == 7){
		                    $usertype = 'Banned';
		                }elseif(isset($userData['userGroup']) == 8){
		                    $usertype = 'Deleted';
		                }elseif(isset($userData['userGroup']) == 9){
		                    $usertype = 'Inactive';
		                }else{
		                    $usertype = '';
		                }*/

		                /*count for user product*/
		                $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
		                $result = $pdo->prepare("$cartCountData");
		                $result->execute();
		                $userCount = $result->rowCount();

		                /*count for user notification*/
		                $notificntdata= "SELECT DISTINCT unique_num ,title,description,image FROM pushnotification";
		                $resultcntdata = $pdo->prepare("$notificntdata");
		                $resultcntdata->execute();
		                $countnotfication = $resultcntdata->rowCount();
						if($lang=='es')
						{	
							$response = array('flag' => '1','message' => '¡Has iniciado la sesión!','cart_count'=> $userCount,'count_notfication' => $countnotfication);
						}else{
							$response = array('flag' => '1','message' => 'Logged in successfully!','cart_count'=> $userCount,'count_notfication' => $countnotfication);
						}
	                    //$response = array('flag' => '1','message' => 'User Logged in Successfully','cart_count'=> $userCount,'count_notfication' => $countnotfication);
			            $response['data'] = array();
			            $response['data']['user_id'] = $userData['user_id'];
			            $response['data']['email'] = $userData['email'];
			            $response['data']['memberno'] = $userData['memberno'];
			            $response['data']['first_name'] = $userData['first_name'];
			            $response['data']['userGroup'] = $userData['userGroup'];
			            $response['data']['workStation'] = $userData['workStation'];
			            $response['data']['domain'] = $userData['domain'];
			            $response['data']['fcm_key'] = $userData['fcm_key'];
			            $response['data']['user_type'] = $usertype;
			            $memberPhoto = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/members/' . $userData['user_id'] . '.' .  $userData['photoext'];
						
						$response['data']['image'] = $memberPhoto;
					}
					catch (PDOException $e)
					{
						$response = array('flag'=>'0', 'message' => $e->getMessage());
						// echo json_encode($response); 
					}
			    }else{
					if($lang=='es')
					{	
						$response = array('flag' => '0', 'message' => 'Error al localizar el perfil del socio');
					}else{
						$response = array('flag' => '0', 'message' => 'Error locating member profile');
					}
			    	//$response = array('flag' => '0', 'message' => 'User does not exist');
			    }
			    echo json_encode($response);
    		}
			

	    }catch(PDOException $e){

	    	$response = array('flag'=>'0', 'message' => $e->getMessage());
			echo json_encode($response); 	

	    }
    }
    else
    {
		if($lang=='es')
		{	
			$response = array('flag'=>'0', 'message' => "Todos los campos son obligatorios.");
		}else{
			$response = array('flag'=>'0', 'message' => "All fields are mendatory.");
		}
    	//$response = array('flag'=>'0', 'message' => "All fields are mendatory");
		echo json_encode($response); 
    }
    
    