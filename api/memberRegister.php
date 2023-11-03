<?php 
	// created by konstant for member register task on 11-01-2022
    include('connectionM_login.php');
    

    if(!empty($_POST['language'])){
		$lang = $_POST['language'];
	}else{
		$lang = ""; 
	}

    if(isset($_REQUEST['username']) && isset($_REQUEST['password']) && isset($_REQUEST['email']) && isset($_REQUEST['device_id']) && isset($_REQUEST['platform']) && isset($_REQUEST['fcm_key']) && isset($_REQUEST['macAddress']))
    {
   
    		if($_REQUEST['email'] == '' || $_REQUEST['password'] == '' || $_REQUEST['username'] == '' || $_REQUEST['language'] == '')
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

			    $selectUser = "SELECT * from members WHERE email = '".$_REQUEST['email']."' OR username = '".$_REQUEST['username']."'";  
				$result = $pdo->prepare("$selectUser");
				$result->execute();
				$userCount = $result->rowCount(); 

			    if($userCount > 0){
					if($lang=='es')
					{	
						$response = array('flag'=>'0', 'message' => "El nombre del usuario o e-mail ya existen, inténtelo de nuevo.");
					}else{
						$response = array('flag'=>'0', 'message' => "Username or e-mail already exists, please try again!");
					}
			    	//$response = array('flag'=>'0', 'message' => "Username or Email already exist, please try again!");
					echo json_encode($response);
			    }else{
			    	$username = $_REQUEST['username'];
			    	$password = $_REQUEST['password'];
			    	$email = $_REQUEST['email'];
			    	$lang = $_REQUEST['language'];
			    	$device_id = $_REQUEST['device_id'];
			    	$platform = $_REQUEST['platform'];
			    	$fcm_key = $_REQUEST['fcm_key'];
			    	$macAddress = $_REQUEST['macAddress'];
			    	$insertTime = date("Y-m-d H:i:s");
			    	$crypt_pass = md5($password.$email);
			    	$token = md5($_REQUEST['email']).rand(10,9999);

			    	$very_param = base64_encode($_REQUEST['email'].",".$token);

			    	$link = "<a href='".SITE_ROOT."/api/verify-email.php?key=".$very_param."'>Click and Verify Email</a>";
			    	$very_link = SITE_ROOT."/api/verify-email.php?key=".$very_param;
			    	$path = SITE_ROOT.'/api/image/logo.png';
			    	$type = pathinfo($path, PATHINFO_EXTENSION);
					$data = file_get_contents($path);
					$base64_image = 'data:image/' . $type . ';base64,' . base64_encode($data);

				    $insertMember = sprintf("INSERT INTO members (username, email, password, device_id, platform, registered_at, token, fcm_key, macAddress, language) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');",
					    	$username,
					    	$email,
					    	$crypt_pass,
					    	$device_id,
					    	$platform,
					    	$insertTime,
					    	$token,
					    	$fcm_key,
					    	$macAddress,
					    	$lang
				    	);

				    	try
						{
							$pdo->prepare("$insertMember")->execute();
						}
						catch (PDOException $e)
						{
								$response = array('flag'=>'0', 'message' => $e->getMessage());
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
						$mail->Subject = "Member Email Verfication";
						if($lang == 'en' || $lang == ''){
							$email_template = file_get_contents("email-confirmation-en.html");
						}else if($lang == 'es'){
							$email_template = file_get_contents("email-confirmation-es.html");
						}
						$search_arr = array('[site_logo]', '[user_name]', '[very_link]');
						$replace_arr = array($base64_image, $username, $very_link);
						$email_template = str_replace($search_arr, $replace_arr, $email_template);
						$mail->isHTML(true);
						//$mail->Body = 'Hello '.$username.',<br>Click On This Link to Verify Email '.$link.'';
						$mail->Body = $email_template;
						
						if($mail->Send())
						{
							if($lang=='es')
							{	
								$response = array('flag' => '1','message' => 'Se has registrado correctamente, compruebe tu correo electrónico para verificarlo.');
							}else{
								$response = array('flag' => '1','message' => 'Registered successfully, please check your email for verfication!');
							}
							//$response = array('flag' => '1','message' => 'User registered Successfully, please check your email for verfication!');
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
			$response = array('flag' => '0', 'message' => 'Todos los campos son obligatorios.');
		}else{
			$response = array('flag' => '0', 'message' => 'All fields are mandatory.');
		}
    	//$response = array('flag'=>'0', 'message' => "All fields are mendatory");
		echo json_encode($response); 
    }
    
    