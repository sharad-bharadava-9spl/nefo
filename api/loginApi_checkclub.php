<?php 
    include('connectionM_login.php');

    //echo "<pre>";print_r($_REQUEST);exit;

	if(!empty($_POST['language'])){
		$lang = $_POST['language'];
	}else{
		$lang = ""; 
	}
    
    if(isset($_REQUEST['username']) && isset($_REQUEST['password']) && isset($_REQUEST['device_id']) && isset($_REQUEST['platform']) && isset($_REQUEST['fcm_key']))
    {
    	try
    	{
			
    		if($_REQUEST['username'] == '' && $_REQUEST['password'] == '')
    		{
				if($lang=='es')
				{	
					$response = array('flag'=>'0', 'message' => "Todos los campos son obligatorios.");
				}else{
					$response = array('flag'=>'0', 'message' => "All fields are mandatory.");
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
			    $selectUser = "SELECT * from app_access WHERE email = '".$_REQUEST['username']."' AND password = '".base64_encode($_REQUEST['password'])."' AND allow_access = '1'";  
				$result = $pdo->prepare("$selectUser");
    			/*$selectUser = "SELECT * from `users` WHERE `login_code` = '".base64_encode($_REQUEST['login_code'])."' AND `memberno` ='".$_REQUEST['member_no']."'";
				$result = $pdo->prepare("$selectUser");*/
				$result->execute();
				$userCount = $result->rowCount(); 
				$userData = $result->fetch();

			    if($userCount > 0){


			    	    $selectClub = "SELECT * from app_access WHERE email= '".$_REQUEST['username']."' AND allow_access = '1'";
			    	    $club_result = $pdo->prepare("$selectClub");
			    	    $club_result->execute();
			    	    $clubCount = $club_result->rowCount();

			    	    if($clubCount == 1){
			    	    	
				    	 		try
								{
									$result = $pdo->prepare("SELECT db_pwd FROM db_access WHERE domain = :domain");
									$result->bindValue(':domain', $userData['domain']);
									$result->execute();
									//print_r($results); exit;
								}
								catch (PDOException $e)
								{
									$response = array('flag'=>'0', 'message' => $e->getMessage());
								   // echo json_encode($response); 
								}

								$row = $result->fetch();
								
				    			$db_name = 'ccs_'.$userData['domain'];
								//echo $db_user = $db_name . "u"; echo "<br>";
								//echo $db_pwd = $row['db_pwd']; exit;

								$db_user = "ccs_masterdbu";
								$db_pwd = "GMjq8iG8mEkPMJRf";
							    try	{
									//echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
							 		$pdo2 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
							 		$pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
							 		$pdo2->exec('SET NAMES "utf8"');
								}
								catch (PDOException $e)	{
									
							  		$response = array('flag'=>'0', 'message' => $e->getMessage());
									// echo json_encode($response);
							 		
								}
					    	//update login device and platform
					    	$update_query = "UPDATE `users` SET `device_id`='" .$_REQUEST['device_id']."', `platform`='" .$_REQUEST['platform']."',`fcm_key`='" .$_REQUEST['fcm_key']."' WHERE `user_id`=".$userData['user_id'];
							try
							{
								$results = $pdo2->prepare($update_query);
								$res = $results->execute();
		                        $user_id = $userData['user_id'];
		                        $selectUserdetail = "SELECT * from `users` WHERE user_id = '$user_id'";
								$result = $pdo2->prepare("$selectUserdetail");
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
				                $result = $pdo2->prepare("$cartCountData");
				                $result->execute();
				                $userCount = $result->rowCount();

				                /*count for user notification*/
				                $notificntdata= "SELECT DISTINCT unique_num ,title,description,image FROM pushnotification";
				                $resultcntdata = $pdo2->prepare("$notificntdata");
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
					            $response['data']['user_id'] = $userDatatype['user_id'];
					            $response['data']['email'] = $userDatatype['email'];
					            $response['data']['memberno'] = $userDatatype['memberno'];
					            $response['data']['first_name'] = $userDatatype['first_name'];
					            $response['data']['userGroup'] = $userDatatype['userGroup'];
					            $response['data']['workStation'] = $userDatatype['workStation'];
					            $response['data']['domain'] = $userDatatype['domain'];
					            $response['data']['fcm_key'] = $userDatatype['fcm_key'];
					            $response['data']['user_type'] = $usertype;
					            $memberPhoto = SITE_ROOT.'/images/_' . $userDatatype['domain'] . '/members/' . $userDatatype['user_id'] . '.' .  $userDatatype['photoext'];
								
								$response['data']['image'] = $memberPhoto;
							}
							catch (PDOException $e)
							{
								$response = array('flag'=>'0', 'message' => $e->getMessage());
								// echo json_encode($response); 
							}
					}else{
						// fetch all clubs related to user
						if($lang=='es')
						{	
							$response = array('flag' => '2','message' => 'lista de clubes conectados al usuario');
						}else{
							$response = array('flag' => '2','message' => 'list of clubs connected to user');
						}
						//$response = array('flag' => '2','message' => 'list of clubs connected to user');
						$i=0;
						while($clubData  =  $club_result->fetch()){

							$response['club_data'][$i]['club_name']  = $clubData['domain'];
							$response['club_data'][$i]['member_no'] = $clubData['member_number']; 
							$response['club_data'][$i]['user_id']  = $clubData['user_id'];
							$response['club_data'][$i]['email']  = $clubData['email'];
							$response['club_data'][$i]['login_code']  = base64_decode($clubData['password']);
							$i++;
						}

					}
					
			    }else{
					if($lang=='es')
					{	
						$response = array('flag' => '0', 'message' => 'Wrong credenatials or you don\'t have access to app !');
					}else{
						$response = array('flag' => '0', 'message' => 'Wrong credenatials or you don\'t have access to app !');
					}
			    	//$response = array('flag' => '0', 'message' => 'Wrong credenatials or you don\'t have access to app !');
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
    
    