<?php 
    include('connectionM.php');
    include('language/common.php');
    try{

        if(!empty($_POST['language'])){
            $lang = $_POST['language'];
        }else{
            $lang = ""; 
        }

        if(!empty($lang == 'es') || !empty($lang == 'en')){

            if(!empty($_POST['user_id'])){
                $user_id = $_POST['user_id'];
            }else{
                $user_id = ""; 
            }

            if(!empty($_POST['macAddress'])){
                $macAddress = $_POST['macAddress'];
            }else{
                $macAddress = ""; 
            }

            if(!empty($_POST['newpassport'])){
                $newpassport = $_POST['newpassport'];
            }else{
                $newpassport = ""; 
            }

            $userDetails = "SELECT * FROM users WHERE user_id = '{$_REQUEST['user_id']}'";
            $result = $pdo->prepare($userDetails);
            $result->execute();
            $userCount = $result->rowCount();
            $row = $result->fetch();
            $admintype = $row['userGroup'];
            $login_code = base64_encode($row['login_code']);

            /*Check system setting find domain*/
            $domainName = $_REQUEST['club_name'];
            $checkSystemsetting = "SELECT * FROM systemsettings WHERE domain = '$domainName'";
            $result = $pdo->prepare("$checkSystemsetting");
            $result->execute();
            $clubSystem = $result->fetch();
            $domain = $clubSystem['domain'];

            /*check system wise multiple data check*/
            $checkDomainMulitpleData = "SELECT * FROM moblie_macaddress WHERE domain_name = '$domain'";
            $result = $pdo->prepare("$checkDomainMulitpleData");
            $result->execute();
            $macarr = array();

            if($result->rowCount() > 0){
                while($macaddress = $result->fetch()){
                    $macarr[] = $macaddress['mac_address'];
                }
            }
            if($admintype != 1){

                if(in_array($macAddress,$macarr)){
                    $topupcredit = 1;
                    $preorder    = 1;
                    $showprice   = 1;
                }else{
                    $topupcredit = $clubSystem['topcredit_option'];
                    $preorder    = $clubSystem['preorder_option'];
                    $showprice   = $clubSystem['showprice_option'];
                }
            }

            if($admintype == 1){
                
                $topupcredit = 1;
                $preorder    = 1;
                $showprice   = 1;
            }

            /*update password detail*/
            if($userCount > 0){

                $update_query = "UPDATE `users` SET `login_code`='".base64_encode($newpassport)."' WHERE `user_id`=".$user_id;
                $update_data = $pdo->prepare($update_query);
				$res = $update_data->execute();
                if($lang == 'es'){
                    $response = array('flag' => '1', 'message' => '¡Club encontrado con éxito!');
                }else{
                    $response = array('flag' => '1', 'message' => 'Password updated successfully!');
                }
                //$response = array('flag' => '1', 'message' => 'Password change successfully');
                echo json_encode($response);

            }else{
                if($lang == 'es'){
                    $response = array('flag' => '0', 'message' => 'Error al localizar el perfil del socio');
                }else{
                    $response = array('flag' => '0', 'message' => 'Error locating member profile');
                }
                //$response = array('flag' => '0', 'message' => 'User does not exist');
                echo json_encode($response);
            }
        }else{
            if($lang == 'es'){
                $response = array('flag' => '0', 'message' => 'Please add parameter in languageid.');
            }else{
                $response = array('flag' => '0', 'message' => 'Please add parameter in languageid.');
            }
           //$response = array('flag' => '0', 'message' => 'Please add parameter in languageid.');
           echo json_encode($response);
        }

    }catch(PDOException $e){
        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response);
    }
