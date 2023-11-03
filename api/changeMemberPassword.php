<?php 
	// created by konstant  on 04-04-2022
    include('connectionM_login.php');
    
	if(!empty($_POST['language'])){
		$lang = $_POST['language'];
	}else{
		$lang = ""; 
	}
    
    if(isset($_REQUEST['email']) && isset($_REQUEST['old_password']) && isset($_REQUEST['new_password']) && isset($_REQUEST['device_id']) && isset($_REQUEST['platform']) && isset($_REQUEST['fcm_key']) && isset($_REQUEST['macAddress']))
    {
   
    		if($_REQUEST['email'] == '' || $_REQUEST['old_password'] == '' || $_REQUEST['new_password'] == '')
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
    			$old_password = md5($_REQUEST['old_password'].$_REQUEST['email']);

			    $selectMember = "SELECT * from members WHERE email = '".$_REQUEST['email']."' AND password = '".$old_password."'";  
				$result = $pdo->prepare("$selectMember");
				$result->execute();
				$userCount = $result->rowCount(); 

			    if($userCount == 0){
					if($lang == 'es'){
						$response = array('flag'=>'0', 'message' => "La contraseña antigua o el e-mail son incorrectos, inténtelo de nuevo.");
					}else{
						$response = array('flag'=>'0', 'message' => "The old password or e-mail is wrong, please try again!");
					}
			    	//$response = array('flag'=>'0', 'message' => "Old password or email is wrong, please try again!");
					echo json_encode($response);
			    }else{
			    	
			    	$new_password = md5($_REQUEST['new_password'].$_REQUEST['email']);

			    	$selectPassword = "SELECT password FROM members WHERE password = '".$new_password."'";
			    	$result1 = $pdo->prepare("$selectPassword");
			    	$result1->execute();
			    	$passCount = $result1->rowCount();
			    	if($passCount > 0){
						if($lang == 'es'){
							$response = array('flag'=>'0', 'message' => "La nueva contraseña es demasiado parecida a la anterior, por favor, inténtelo con una contraseña diferente.");
						}else{
							$response = array('flag'=>'0', 'message' => "The new password is too similar to your old one, please try with a different password!");
						}
				    	//$response = array('flag'=>'0', 'message' => "Old password is similar to new password, please try with different password!");
						echo json_encode($response);
			    	}else{

				    	$updateMember = "UPDATE members SET password = '".$new_password."', device_id = '".$_REQUEST['device_id']."', platform = '".$_REQUEST['platform']."', fcm_key = '".$_REQUEST['fcm_key']."', macAddress = '".$_REQUEST['macAddress']."' WHERE email = '".$_REQUEST['email']."'";

					    	try
							{
								$pdo->prepare("$updateMember")->execute();
							}
							catch (PDOException $e)
							{
									$response = array('flag'=>'0', 'message' => $e->getMessage());
							}
							
							if($lang == 'es'){
								$response = array('flag' => '1','message' => '¡Contraseña actualizada correctamente!');
							}else{
								$response = array('flag' => '1','message' => 'Password updated successfully!');
							}
							//$response = array('flag' => '1','message' => 'Password updated Successfully!');
							echo json_encode($response);
					}
			    }

			}



    }
    else
    {
		if($lang == 'es'){
			$response = array('flag' => '0','message' => 'Todos los campos son obligatorios.');
		}else{
			$response = array('flag' => '0','message' => 'All fields are mendatory');
		}
    	//$response = array('flag'=>'0', 'message' => "All fields are mendatory");
		echo json_encode($response); 
    }
    
    