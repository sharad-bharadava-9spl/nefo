<?php 
	// created by konstant for member register task on 20-06-2022
    include('connectionM_login.php');

	if(!empty($_POST['language'])){
		$lang = $_POST['language'];
	}else{
		$lang = ""; 
	} 

    if(isset($_POST['club_code']) && isset($_POST['member_id'])){

	    if($_POST['club_code'] == '' || $_POST['member_id'] == '')
		{
			if($lang=='es')
			{	
				$response = array('flag'=>'0', 'message' => "Todos los campos son obligatorios.");
			}else{
				$response = array('flag'=>'0', 'message' => "All fields are mandatory.");
			}
			//$response = array('flag'=>'0', 'message' => "Fields can not be null.");
			echo json_encode($response); 
			die();
		}else{
			// check memebr id

			$checkMember = "SELECT id FROM members WHERE id=".$_POST['member_id'];
			try{
				$mem_results = $pdo->prepare("$checkMember");
				$mem_results->execute();
			}catch (PDOException $e){
					$response = array('flag'=>'0', 'message' => $e->getMessage());
					echo json_encode($response); 
					die();
			}

			$memberCount = $mem_results->rowCount();
			if($memberCount == 0){
				if($lang=='es')
				{	
					$response = array('flag'=>'0', 'message' => "El socio no ha sido aprobado.");
				}else{
					$response = array('flag'=>'0', 'message' => "Member is not approved.");
				}
				//$response = array('flag'=>'0', 'message' => "Member is not valid!");
				echo json_encode($response); 
				die();
			}

			// check club against the club code

			$checkClub = "SELECT domain, db_pwd FROM db_access WHERE club_code = '".$_POST['club_code']."'";

				try{
					$club_results = $pdo->prepare("$checkClub");
					$club_results->execute();
				}catch (PDOException $e){
					$response = array('flag'=>'0', 'message' => $e->getMessage());
					echo json_encode($response); 
					die();
				}
				$clubCount = $club_results->rowCount();
				if($clubCount == 0){
					if($lang=='es')
					{	
						$response = array('flag'=>'0', 'message' => "Este código no coincide con ningún club. Por favor, inténtalo de nuevo.");
					}else{
						$response = array('flag'=>'0', 'message' => "This code doesn't match any club. Please try again.");
					}
					//$response = array('flag'=>'0', 'message' => "Code doesn't match to any club, please try again!");
					echo json_encode($response); 
					die();
				}else{
					// delete club request in db
					$club_row = $club_results->fetch();
					$club_name = $club_row['domain'];
					$member_id = $_POST['member_id'];
					$club_code = $_POST['club_code'];

					// check if request already sent
					$checkClubRequest = "SELECT id, allow_request FROM app_requests WHERE member_id = '".$member_id."' AND club_code = '".$club_code."'";
					try{
						$req_results = $pdo->prepare("$checkClubRequest");
						$req_results->execute();
					}catch (PDOException $e){
						$response = array('flag'=>'0', 'message' => $e->getMessage());
						echo json_encode($response); 
						die();
					}
					$reqCount = $req_results->rowCount();
					//$req_row = $req_results->fetch();
					if($reqCount == 0){
							$response = array('flag'=>'0', 'message' => "something is missing or invalid, please try again!");
							echo json_encode($response); 
							die();
					}

						$db_name = 'ccs_'.$club_row['domain'];
						$db_user = $db_name . "u";
						$db_pwd = $club_row['db_pwd'];

						try	{
							//echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
					 		$pdo_club = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
					 		$pdo_club->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					 		$pdo_club->exec('SET NAMES "utf8"');
						}
						catch (PDOException $e)	{
							
					  		$response = array('flag'=>'0', 'message' => $e->getMessage());
							echo json_encode($response);
							die();
					 		
						}

						// remove app member from users when deleted

						$updateUser = "UPDATE users SET app_member = NULL WHERE app_member = ".$_POST['member_id'];
						 	try
							{
								$pdo_club->prepare("$updateUser")->execute();
							}
							catch (PDOException $e)
							{
									$response = array('flag'=>'0', 'message' => $e->getMessage());
							}

					    $deleteAppRequest = sprintf("DELETE FROM app_requests WHERE member_id = '%d' AND club_code='%s'",
						    	$member_id,
						    	$club_code
					    	);

					    	try
							{
								$pdo->prepare("$deleteAppRequest")->execute();
							}
							catch (PDOException $e)
							{
									$response = array('flag'=>'0', 'message' => $e->getMessage());
							}
							if($lang=='es')
							{	
								$response = array('flag' => '1','message' => '¡Solicitud de club eliminada con éxito!');
							}else{
								$response = array('flag' => '1','message' => 'club request deleted successfully!');
							}
							//$response = array('flag' => '1','message' => 'club request deleted successfully!');
							echo json_encode($response);
				}


		}

    }else{
		if($lang=='es')
		{	
			$response = array('flag'=>'0', 'message' => "Todos los campos son obligatorios.");
		}else{
			$response = array('flag'=>'0', 'message' => "All fields are mendatory.");
		}
    	//$response = array('flag'=>'0', 'message' => "All fields are mendatory");
		echo json_encode($response); 
    }
