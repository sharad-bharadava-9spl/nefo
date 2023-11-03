<?php 
	// created by konstant for member register task on 12-01-2022
    include('connectionM_login.php');
    //ini_set("display_errors", "on");
	
	$lang = '';
	if(isset($_POST['language'])){
		$lang = $_POST['language'];
	}

    if(isset($_POST['club_code']) && isset($_POST['member_id'])){

	    if($_POST['club_code'] == '' || $_POST['member_id'] == '')
		{
			if($lang == 'es'){
				$response = array('flag'=>'0', 'message' => "Todos los campos son obligatorios.");
			}else{
				$response = array('flag'=>'0', 'message' => "All fields are mandatory.");
			}
			//$response = array('flag'=>'0', 'message' => "Fields can not be null.");
			echo json_encode($response); 
			die();
		}else{
			$lang = '';
			if(isset($_POST['language'])){
				$lang = $_POST['language'];
			}	
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
				if($lang == 'es'){
					$response = array('flag'=>'0', 'message' => "El socio no ha sido aprobado.");
				}else{
					$response = array('flag'=>'0', 'message' => "Member is not approved.");
				}
				//$response = array('flag'=>'0', 'message' => "Member is not valid!");
				echo json_encode($response); 
				die();
			}

			// check club against the club code

			$checkClub = "SELECT domain, db_pwd, customer FROM db_access WHERE club_code = '".$_POST['club_code']."'";

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
					if($lang == '' || $lang == 'en'){
						$response = array('flag'=>'0', 'message' => "This code doesn't match any club. Please try again.");
					}
					if($lang == 'es'){
						$response = array('flag'=>'0', 'message' => "Este código no coincide con ningún club. Por favor, inténtalo de nuevo.");
					}
					echo json_encode($response); 
					die();
				}else{
					// insert club request in db
					$club_row = $club_results->fetch();
					$club_name = $club_row['domain'];
					$customer = $club_row['customer'];
					$member_id = $_POST['member_id'];
					$club_code = $_POST['club_code'];
					$created_at = date("Y-m-d H:i:s");

					$nefos_db_name = 'admin_nefos';
					$nefos_db_user = 'admin_nefosu';
					$nefos_db_pass = '5T8mHFvfQVIlCrg3';

					//for local
					// $nefos_db_name = 'admin_nefos';
					// $nefos_db_user = 'root';
					// $nefos_db_pass = '';

					try	{
						//echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
				 		$pdo2 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$nefos_db_name, $nefos_db_user, $nefos_db_pass);
				 		$pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				 		$pdo2->exec('SET NAMES "utf8"');
					}
					catch (PDOException $e)	{
						
				  		$response = array('flag'=>'0', 'message' => $e->getMessage());
						echo json_encode($response);
						die();
				 		
					}
					
					$selectCLub ="SELECT longName, shortName FROM customers WHERE number =".$customer;
					$result_club = $pdo2->prepare("$selectCLub");
            		$result_club->execute();
            		$cust_row = $result_club->fetch();
					
					
					
            		$club_shorName = $cust_row['shortName']; 

				    $club_db_name = 'ccs_'.$club_row['domain'];
					$club_db_user = $club_db_name . "u";
					$club_db_pwd = $club_row['db_pwd'];

					//for local
					// $club_db_user = "root";
					// $club_db_pwd = "";

/*								$db_user = "root";
					$db_pwd = "";*/
				    try	{
						//echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
				 		$pdo_club = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$club_db_name, $club_db_user, $club_db_pwd);
				 		$pdo_club->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				 		$pdo_club->exec('SET NAMES "utf8"');
					}
					catch (PDOException $e)	{
						
				  		$response = array('flag'=>'0', 'message' => $e->getMessage());
						echo json_encode($response);
						die();
				 		
					}

					$selectAppMember = "SELECT app_member FROM users WHERE app_member =".$member_id;
					try{
						$mclub_results = $pdo_club->prepare("$selectAppMember");
						$mclub_results->execute();

					}			
					catch (PDOException $e)
					{
						$error_message = $e->getMessage();
						if($lang == 'es'){
							$response = array('flag'=>'0', 'message' => "Club DB has not updated yet.");
						}else{
							$response = array('flag'=>'0', 'message' => "Club DB has not updated yet.");
						}
						//$response = array('flag'=>'0', 'message' =>"Club DB has not updated yet.");
					   	echo json_encode($response); 
					   	die();
					}

					


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
					
					$req_row = $req_results->fetch();
					if($reqCount > 0){
						if($req_row['allow_request'] == 1){
							if($lang == '' || $lang == 'en'){
								$response = array('flag'=>'0', 'message' => " You've already added this club! Please speak to a staff member to activate your app access for the club.");
							}
							if($lang == 'es'){
								$response = array('flag'=>'0', 'message' => "Ya has añadido este club. Por favor, habla con un miembro del personal para activar tu acceso a la app para el club.");
							}
							echo json_encode($response); 
							die();

						}else{
							if($lang == '' || $lang == 'en'){
								$error_txt1 = "You need the club's approval. Please speak to a staff member to activate your app access for the club.";
							}
							if($lang == 'es'){
								$error_txt1 = "Necesitas la aprobación del club. Por favor, habla con un miembro del personal para activar tu acceso a la app para el club.";
							}
							$response = array('flag'=>'0', 'message' => $error_txt1);
							echo json_encode($response); 
							die();
						}
					}

					
					    $insertAppRequest = sprintf("INSERT INTO app_requests (member_id, club_code, club_name, created_at) VALUES ('%d', '%s', '%s', '%s');",
						    	$member_id,
						    	$club_code,
						    	$club_name,
						    	$created_at
					    	);
							

					    	try
							{
								$pdo->prepare("$insertAppRequest")->execute();
							}
							catch (PDOException $e)
							{
									$response = array('flag'=>'0', 'message' => $e->getMessage());
							}
							$selectMember = "SELECT email, username, language FROM members WHERE id = ".$member_id;
							try
							{
								$member_result = $pdo->prepare("$selectMember");
								$member_result->execute();
							}
							catch (PDOException $e)
							{
									$response = array('flag'=>'0', 'message' => $e->getMessage());
							}

							$member_row = $member_result->fetch();
							$member_email = $member_row['email'];
							$member_username = $member_row['username'];
							$member_language = $member_row['language'];

							// send email verification link to memmber
							require_once('class.phpmailer.php');
							$mail = new PHPMailer(true);
							$mail->SMTPDebug = 0;
							$mail->Debugoutput = 'html';
							$mail->isSMTP();
							$mail->Host = "mail.cannabisclub.systems";
							$mail->SMTPAuth = true;
							$mail->Username = "info@cannabisclub.systems";
							$mail->Password = "Insjormafon9191";
							$mail->SMTPSecure = 'ssl'; 
							$mail->Port = 465;
							$mail->setFrom('info@cannabisclub.systems', 'CCSNube');
							$mail->addAddress("$member_email", "$member_username");
							$mail->Subject = "Member club request";
							if($member_language == '' || $member_language == 'en'){
								$email_template = "Hello $member_username!<br><p>Thank you for applying for app access to <strong>$club_shorName</strong>,<br> In order for you to view our club through the CCS Members App, please speak to a staff member to activate your access.<br>Thanks,<br><br>The CCS Members App team</p>";
							}else if($member_language == 'es'){
								$email_template = "Hola $member_username!<br><p>Gracias por solicitar el acceso a la aplicación <strong>$club_shorName</strong>, <br>Para que puedas ver nuestro club a través de la aplicación de Socios de CCS, por favor habla con un miembro del personal para activar tu acceso.<br>Gracias,<br><br>El equipo para socios de la App de CCS</p>";
							}
							
							$mail->isHTML(true);
							//$mail->Body = 'Hello '.$username.',<br>Click On This Link to Verify Email '.$link.'';
							$mail->Body = $email_template;

							if($mail->Send())
							{
								if($lang == '' || $lang == 'en'){
									$success_txt = "We have received your request! Please speak to a staff member to activate your app access for the club.";
								}else if($lang == 'es'){
									$success_txt = "Hemos recibido tu solicitud. Por favor, habla con un miembro del personal para activar tu acceso a la app para el club.";
								}
								$response = array('flag' => '1','message' => $success_txt);
							}
							else
							{
								$response = array('flag' => '0','error' => "Mail Error - >".$mail->ErrorInfo);
							}

							echo json_encode($response);

				}


		}

    }else{
    	$response = array('flag'=>'0', 'message' => "All fields are mendatory");
		echo json_encode($response); 
    }
