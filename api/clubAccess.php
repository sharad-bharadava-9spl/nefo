<?php 
	// created by konstant for member register task on 17-01-2022
    include('connectionM.php');

    try {
        //echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
        //$pdo_master = new PDO('mysql:host=localhost;dbname=ccs_masterdb', 'root', '');
        $pdo_master = new PDO('mysql:host=127.0.0.1:3306;dbname=ccs_masterdb', 'ccs_masterdbu', 'GMjq8iG8mEkPMJRf');
        $pdo_master->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo_master->exec('SET NAMES "utf8"');
    }
    catch (PDOException $e) {
        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response); 
        die();
        
    }

	if(!empty($_POST['language'])){
		$lang = $_POST['language'];
	}else{
		$lang = ""; 
	}
    
    if(isset($_POST['club_name'])  && isset($_POST['member_id']) && isset($_POST['device_id']) && isset($_POST['platform']) && isset($_POST['fcm_key']) &&  isset($_POST['macAddress']) &&  isset($_POST['language']))
    {
		
    	try
    	{
    		if($_POST['club_name'] == '' || $_POST['member_id'] == '' || $_POST['device_id'] == '' || $_POST['platform'] == '' || $_POST['fcm_key'] == '' || $_POST['macAddress'] == '')
    		{
    			$response = array('flag'=>'0', 'message' => "Fields can not be null.");
				echo json_encode($response); 
    		}
    		else
    		{
    			$selectUser = "SELECT * from `users` WHERE `app_member` ='".$_POST['member_id']."'";
				$result = $pdo->prepare("$selectUser");
				$result->execute();
				$userCount = $result->rowCount();
				$userData = $result->fetch();

			    if($userCount > 0){

			    	//update login device and platform
			    	$update_query = "UPDATE `users` SET `device_id`='" .$_POST['device_id']."', `platform`='" .$_POST['platform']."',`fcm_key`='" .$_POST['fcm_key']."' WHERE `user_id`=".$userData['user_id'];
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




						// for fetch menu show or hide
						try
						{
							$d = $_POST['club_name'];
							$showmenu_option=1;
							$checkSystemsetting = "SELECT showmenu_option22 FROM systemsettings WHERE domain = '$d'";
							$result = $pdo->prepare("$checkSystemsetting");
							$result->execute();
							$clubSystem = $result->fetch();

							$showmenu_option = $clubSystem['showmenu_option22'];
							if($userDatatype['userGroup'] == '1'){
								$showmenu_option =1;
							}
						}
						catch (PDOException $e) {
							$showmenu_option =1;
						}
		                /* update current club for member */
						
						if($lang=='es')
						{	
							$response = array('flag' => '1','message' => 'El socio tiene acceso al club.','cart_count'=> $userCount,'count_notfication' => $countnotfication);
						}else{
							$response = array('flag' => '1','message' => 'Member has club access.','cart_count'=> $userCount,'count_notfication' => $countnotfication);
						}
	                    //$response = array('flag' => '1','message' => 'User can access the club Successfully','cart_count'=> $userCount,'count_notfication' => $countnotfication);
			            $response['data'] = array();
				        $member_nickname = '';
                        // fetch member username from app
                        $selectMember = "SELECT username,language FROM members WHERE id =".$_POST['member_id'];
                        $member_result = $pdo_master->prepare("$selectMember");
                        $member_result->execute();
                        $member_row = $member_result->fetch();
                        $member_nickname = $member_row['username'];
						$member_language = $member_row['language'];
	                    $response['data']['member_nickname'] = $member_nickname;
			            $response['data']['user_id'] = $userData['user_id'];
			            $response['data']['email'] = $userData['email'];
			            $response['data']['memberno'] = $userData['memberno'];
			            $response['data']['first_name'] = $userData['first_name'];
			            $response['data']['userGroup'] = $userData['userGroup'];
			            $response['data']['workStation'] = $userData['workStation'];
			            $response['data']['domain'] = $userData['domain'];
			            $response['data']['fcm_key'] = $userData['fcm_key'];
			            $response['data']['user_type'] = $usertype;
			            $response['data']['showmenu_option'] = $showmenu_option;
			            $memberPhoto = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/members/' . $userData['user_id'] . '.' .  $userData['photoext'];
						
						$response['data']['image'] = $memberPhoto;

						if($userData['userGroup']=='7')
						{
							if($lang=='es')
							{	
								$response = array('flag' => '0', 'message' => 'Has sido expulsado de este club, y ya no tienes acceso a su app');
							}else{
								$response = array('flag' => '0', 'message' => 'You have been banned from this club, and you no longer have access to their app');
							}
						}
						if($userData['userGroup']=='8')
						{
							if($lang=='es')
							{	
								$response = array('flag' => '0', 'message' => 'Has sido eliminado de este club, y ya no tienes acceso a su app');
							}else{
								$response = array('flag' => '0', 'message' => 'You have been deleted from this club, and you no longer have access to their app');
							}
						}

							try	{
								//echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
						 		//$pdo_master = new PDO('mysql:host=localhost;dbname=ccs_masterdb', 'root', '');
						 		$pdo_master = new PDO('mysql:host=127.0.0.1:3306;dbname=ccs_masterdb', 'ccs_masterdbu', 'GMjq8iG8mEkPMJRf');
						 		$pdo_master->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						 		$pdo_master->exec('SET NAMES "utf8"');
							}
							catch (PDOException $e)	{
								$response = array('flag'=>'0', 'message' => $e->getMessage());
							   	echo json_encode($response); 
						 		
							}
/*						$selectOldclub = "SELECT current_club FROM members WHERE id = ".$_POST['member_id'];
						$resultOldClub = $pdo_master->prepare("$selectOldclub");
						$resultOldClub->execute();
						$oldClub_row = $resultOldClub->fetch();
						$old_club = $oldClub_row['current_club'];*/
/*						$old_club = $_POST['old_club'];
						$old_user_id = $_POST['old_user_id'];*/

/*						if(!empty($old_club) || !empty($old_user_id)){

							if($old_club == '' || empty($old_club)){
								$response = array('flag'=>'0', 'message' => 'Old club can not be blank!');
							   	echo json_encode($response); 
							   	die();
							}
							if($old_user_id == ''|| empty($old_user_id)){
								$response = array('flag'=>'0', 'message' => 'Old club user id can not be blank!');
							   	echo json_encode($response); 
							   	die();
							}
						
							try
							{
								$result_old = $pdo_master->prepare("SELECT db_pwd FROM db_access WHERE domain = :domain");
								$result_old->bindValue(':domain', $old_club);
								$result_old->execute();
								
							}
							catch (PDOException $e)
							{
								$response = array('flag'=>'0', 'message' => $e->getMessage());
							   	echo json_encode($response); 
							   	die();
							}
							$old_row = $result_old->fetch();
						    $old_db_name = 'ccs_'.$old_club;
							$old_db_user = $old_db_name . "u";
							$old_db_pwd = $old_row['db_pwd'];
							try	{
								//echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
						 		$pdo_old_club = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$old_db_name, $old_db_user, $old_db_pwd);
						 		$pdo_old_club->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						 		$pdo_old_club->exec('SET NAMES "utf8"');
							}
							catch (PDOException $e)	{
								
						  		$response = array('flag'=>'0', 'message' => $e->getMessage());
								echo json_encode($response);
								die();
						 		
							}
							// update old club users

							$updateOldclub = "UPDATE users SET currentActive_app = 0 WHERE user_id=".$_POST['old_user_id'];
							$resultOldClub = $pdo_old_club->prepare("$updateOldclub")->execute();

		            	}
							$updateMember = "UPDATE members SET current_club = '".$_POST['club_name']."' WHERE id = ".$_POST['member_id'];
							$resultMember = $pdo_master->prepare("$updateMember")->execute();

			                $updateClub = "UPDATE users SET currentActive_app = 1 WHERE user_id =".$userData['user_id'];
			                $resultClub = $pdo->prepare("$updateClub")->execute();*/

					}
					catch (PDOException $e)
					{
						$response = array('flag'=>'0', 'message' => $e->getMessage());
						// echo json_encode($response); 
					}
			    }else{
					if($lang=='es')
					{	
						$response = array('flag' => '0', 'message' => 'Error al localizar el perfil del socio.');
					}else{
						$response = array('flag' => '0', 'message' => 'Error locating member profile.');
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
			$response = array('flag'=>'0', 'message' => "All fields are mendatory");
		}
    	//$response = array('flag'=>'0', 'message' => "All fields are mendatory");
		echo json_encode($response); 
    }
    
    
