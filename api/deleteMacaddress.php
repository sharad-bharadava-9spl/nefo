<?php 
    include('connectionM.php');

    try{

    	if(!empty($_POST['language'])){
            $lang = $_POST['language'];
        }else{
            $lang = ""; 
        }

		if(!empty($_POST['macid'])){
            $macid = $_POST['macid'];
        }else{
            $macid = "";
        }

        if(!empty($_POST['user_id'])){
            $user_id = $_POST['user_id'];
        }else{
            $user_id = "";
        }

        if(!empty($lang == 'es') || !empty($lang == 'en')){

        	$checkUserDetail = "SELECT * FROM users WHERE user_id = '$user_id'";
            $result1 = $pdo->prepare("$checkUserDetail");
            $result1->execute();
            $userData = $result1->fetch();

            if($userData['userGroup'] == 1){

            	$checkDomainMulitpleData = "SELECT * FROM moblie_macaddress WHERE id = '$macid' AND status = '2'";
                $result1 = $pdo->prepare("$checkDomainMulitpleData");
                $result1->execute();
                $macDetail = $result1->fetch();

                if($macDetail['id'] == $macid ){
                    if($lang=='es')
                    {	
                        $response = array('flag' => '0', 'message' => 'MacCaddress ya eliminada.');
                    }else{
                        $response = array('flag' => '0', 'message' => 'Mac-address already deleted.');
                    }
                    //$response = array('flag' => '0', 'message' => 'Mac-address already deleted.');

                }else{

		        	$updateMacDetail = "UPDATE moblie_macaddress SET status = '2' WHERE id = '$macid'";
		        	$macdetail = $pdo->prepare($updateMacDetail);
		        	$updateQuery = $macdetail->execute();

                    /* get notification count */
                    $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                    $resultcntdata = $pdo->prepare("$notificntdata");
                    $resultcntdata->execute();
                    $countnotfication = $resultcntdata->rowCount();

		        	if($updateQuery){
                        if($lang=='es')
                        {	
                            $response = array('flag' => '1', 'message' => 'Mac-address eliminado con éxito.','macaddress_id' => $macid,'notification_count' => $countnotfication);
                        }else{
                            $response = array('flag' => '1', 'message' => 'Mac-address deleted successfully.','macaddress_id' => $macid,'notification_count' => $countnotfication);
                        }
		                //$response = array('flag' => '1', 'message' => 'Mac-address deleted successfully.','macaddress_id' => $macid,'notification_count' => $countnotfication);
		        	}else{
                        if($lang=='es')
                        {	
                            $response = array('flag' => '0', 'message' => 'No se pudo eliminar MacCaddress, inténtalo de nuevo.');
                        }else{
                            $response = array('flag' => '0', 'message' => 'Mac-address could not deletd,please try again');
                        }
		                //$response = array('flag' => '0', 'message' => 'Mac-address could not deletd,please try again');
		        	}
		        }

	        }else{
                if($lang=='es')
                {	
                    $response = array('flag' => '0', 'message' => 'No tienes privilegios del administrador.');
                }else{
                    $response = array('flag' => '0', 'message' => 'You do not have admin privileges.');
                }
                //$response = array('flag' => '0', 'message' => 'You have not admin.');
            }

        }else{
            if($lang=='es')
            {	
                $response = array('flag' => '0', 'message' => 'Todos los campos son obligatorios.');
            }else{
                $response = array('flag' => '0', 'message' => 'All fields are mandatory.');
            }
            //$response = array('flag' => '0', 'message' => 'Please add parameter all parameter.');
        }

        echo json_encode($response);

    }catch(PDOException $e){

      $response = array('flag'=>'0', 'message' => $e->getMessage());
      echo json_encode($response);
    }