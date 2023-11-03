<?php 
	// created by konstant for member register task on 17-01-2022
    include('connectionM_clubM.php');

	if(!empty($_POST['language'])){
		$lang = $_POST['language'];
	}else{
		$lang = ""; 
	}

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


    
    if(isset($_POST['club_name'])  && isset($_POST['member_id']))
    {
		// if(!empty($_POST['language'])){
        //     $lang = $_POST['language'];
        // }else{
        //     $lang = ""; 
        // }

        try
    	{
    		if($_POST['club_name'] == '' || $_POST['member_id'] == '')
    		{
				if($lang == 'es'){
					$response = array('flag'=>'0', 'message' => "Todos los campos son obligatorios.");
				}else{
					$response = array('flag'=>'0', 'message' => "All fields are mandatory.");
				}
    			//$response = array('flag'=>'0', 'message' => "Fields can not be null.");
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

			    	try
					{
						

						
						$user_id = $userData['user_id'];
						if($lang == 'es'){
							$response = array('flag' => '1','message' => 'El socio tiene acceso al club.');
						}else{
							$response = array('flag' => '1','message' => 'Member has club access.');
						}
						//$response = array('flag' => '1','message' => 'User can access the club Successfully');
			            // $selectMember = "SELECT username,language FROM members WHERE id =".$_POST['member_id'];
                        // $member_result = $pdo_master->prepare("$selectMember");
                        // $member_result->execute();
                        // $member_row = $member_result->fetch();
						// $member_language = $member_row['language'];
						
						
						try
						{
							$d = $_POST['club_name'];
							$checkSystemsetting = "SELECT showmenu_option FROM systemsettings WHERE domain = '$d'";
							$result = $pdo->prepare("$checkSystemsetting");
							$result->execute();
							$settingsCount = $result->rowCount();
							$clubSystem = $result->fetch();
							
							if($userCount > 0){
								$showmenu_option=$clubSystem['showmenu_option'];
								if($userData['userGroup']==1)
								{
									$showmenu_option=1;
								}
							}else{
								$showmenu_option=1;
							}
							
							$response['data'] = array();
							$new_arr['showmenu_option']  = $showmenu_option;
							$response['data']    = $new_arr;
							
						
						}
						catch (PDOException $e) {
							$showmenu_option=1;
							$response['data'] = array();
							$new_arr['showmenu_option']  = $showmenu_option;
							$response['data']    = $new_arr;
						}

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

							// try	{
							// 	//echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
						 	// 	$pdo_master = new PDO('mysql:host=localhost;dbname=ccs_masterdb', 'root', '');
						 	// 	//$pdo_master = new PDO('mysql:host=127.0.0.1:3306;dbname=ccs_masterdb', 'ccs_masterdbu', 'GMjq8iG8mEkPMJRf');
						 	// 	$pdo_master->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						 	// 	$pdo_master->exec('SET NAMES "utf8"');
							// }
							// catch (PDOException $e)	{
							// 	$response = array('flag'=>'0', 'message' => $e->getMessage());
							//    	echo json_encode($response); 
						 		
							// }
					}
					catch (PDOException $e)
					{
						$response = array('flag'=>'0', 'message' => $e->getMessage());
						echo json_encode($response); 
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
    
    
