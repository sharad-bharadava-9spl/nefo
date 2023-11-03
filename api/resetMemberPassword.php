<?php 
	// created by konstant for task on 04-04-2022
    include('connectionM_login.php');
    
	if(!empty($_POST['language'])){
		$language = $_POST['language'];
	}else{
		$language = "";
	}

    
    if(isset($_REQUEST['email']) && isset($_REQUEST['device_id']) && isset($_REQUEST['platform']) && isset($_REQUEST['fcm_key']) && isset($_REQUEST['macAddress']))
    {
   
    		if($_REQUEST['email'] == '' )
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

			    $selectUser = "SELECT * from members WHERE email = '".$_REQUEST['email']."'";  
				$result = $pdo->prepare("$selectUser");
				$result->execute();
				$userCount = $result->rowCount(); 

			    if($userCount == 0){
					if($lang=='es')
					{	
						$response = array('flag'=>'0', 'message' => "E-mail no registrado, por favor inténtelo de nuevo.");
					}else{
						$response = array('flag'=>'0', 'message' => "E-mail not registered, please try again.");
					}
			    	//$response = array('flag'=>'0', 'message' => "Email not registered, please try again!");
					echo json_encode($response);
			    }else{
			    	
			    	$user_row = $result->fetch();
			    	$token = $user_row['token'];
			    	$email = $_REQUEST['email'];
			    	$username = $user_row['username'];
			    	$language = $user_row['language'];

			    	$very_param = base64_encode($_REQUEST['email'].",".$token);
			    	if($language == '' || $language == 'en'){
			    		$link = "<a href='".SITE_ROOT."/reset-member-password.php?key=".$very_param."'>Click here</a>";
			    	}else if($language == 'es'){
			    		$link = "<a href='".SITE_ROOT."/reset-member-password.php?key=".$very_param."'>Haga clic aquí</a>";
			    	}	

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
						$mail->addAddress("$email", "$username");
						$mail->Subject = "Reset Member Password";
						$mail->isHTML(true);
						if($language == '' || $language == 'en'){
							$mail->Body = "Hi ".$username.",<br>We've received your request to reset your password for the CCS Members App.<br> Please click this link to reset your password: ".$link."<br><br>Thanks,<br>The CCS Members App team";
						}else if($language == 'es'){
							$mail->Body = 'Hola '.$username.',<br>Hemos recibido tu solicitud para restablecer tu contraseña para la aplicación de socios de CCS.<br>Por favor, haz clic en este enlace para restablecer tu contraseña: '.$link.'<br><br>Gracias,<br>El equipo para socios de la App de CCS';
						}
						
						if($mail->Send())
						{
							if($lang=='es')
							{	
								$response = array('flag' => '1','message' => 'Compruebe tu e-mail para restablecer la contraseña.');
							}else{
								$response = array('flag' => '1','message' => 'Please check your email to reset password!');
							}
							//$response = array('flag' => '1','message' => 'Please check your email to reset password!');
						}
						else
						{
							$response = array('flag' => '0','error' => "Mail Error - >".$mail->ErrorInfo);
						}

						echo json_encode($response);
			    }

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
    
    