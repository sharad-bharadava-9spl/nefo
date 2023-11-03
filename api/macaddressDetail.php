<?php 
    include('connectionM.php');

    try{

        if(!empty($_POST['language'])){
            $lang = $_POST['language'];
        }else{
            $lang = ""; 
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

                $domain = $_POST['club_name'];
                $checkSystemsetting = "SELECT * FROM systemsettings WHERE domain = '$domain'";
                $result = $pdo->prepare("$checkSystemsetting");
                $result->execute();
                $clubSystem = $result->fetch();
                $domain = $clubSystem['domain'];
                $domainid = $clubSystem['id'];
                $topcredit_option  = $clubSystem['topcredit_option'];
                $preorder_option   = $clubSystem['preorder_option'];
                $showprice_option  = $clubSystem['showprice_option'];
                $showmenu_option   = 1; 
                $showmenu_option  = $clubSystem['showmenu_option'];  
                /*check system wise multiple data check*/
                $checkDomainMulitpleData = "SELECT * FROM moblie_macaddress WHERE domain_name = '$domain' AND status = '0'";
                $result1 = $pdo->prepare("$checkDomainMulitpleData");
                $result1->execute();

                if($result1->rowCount() > 0){
                    $response['data'] = array();
                    $macarr = array();

                    /* get notification count */
                    $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                    $resultcntdata = $pdo->prepare("$notificntdata");
                    $resultcntdata->execute();
                    $countnotfication = $resultcntdata->rowCount();
                    if($lang=='es')
                    {	
                        $response = array('flag' => '1','message' => 'Detalles de Mac-address encontrados con éxito','topcredit_option' => $topcredit_option,'preorder_option' => $preorder_option ,'showprice_option' => $showprice_option,'notification_count' => $countnotfication,'showmenu_option' => $showmenu_option);
                    }else{
                        $response = array('flag' => '1','message' => 'Mac-address detail found successfully','topcredit_option' => $topcredit_option,'preorder_option' => $preorder_option ,'showprice_option' => $showprice_option,'notification_count' => $countnotfication,'showmenu_option' => $showmenu_option);
                    }
                    //$response = array('flag' => '1','message' => 'Mac-address detail found successfully','topcredit_option' => $topcredit_option,'preorder_option' => $preorder_option ,'showprice_option' => $showprice_option,'notification_count' => $countnotfication,'showmenu_option' => $showmenu_option);
                    
                    while($macaddress = $result1->fetch()){
                        $macarr['macid'] = $macaddress['id'];
                        $macarr['macaddress'] = $macaddress['mac_address'];
                        $response['data'][] = $macarr;
                    }
                }else{
                    if($lang=='es')
                    {	
                        $response = array('flag' => '1', 'message' => 'Mac-address no encontrada.','topcredit_option' => $topcredit_option,'preorder_option' => $preorder_option ,'showprice_option' => $showprice_option,'showmenu_option' => $showmenu_option, 'data' => '[]');
                    }else{
                        $response = array('flag' => '1', 'message' => 'Mac-address not found.','topcredit_option' => $topcredit_option,'preorder_option' => $preorder_option ,'showprice_option' => $showprice_option,'showmenu_option' => $showmenu_option, 'data' => '[]');
                    }
                    //$response = array('flag' => '1', 'message' => 'Mac-address not found.','topcredit_option' => $topcredit_option,'preorder_option' => $preorder_option ,'showprice_option' => $showprice_option,'showmenu_option' => $showmenu_option, 'data' => '[]');
                }
            
            }else{
                if($lang=='es')
                {	
                    $response = array('flag' => '0', 'message' => 'No tienes privilegios del administrador.');
                }else{
                    $response = array('flag' => '0', 'message' => 'You don\'t have admin privileges.');
                }
                //$response = array('flag' => '0', 'message' => 'You have not admin.');
            }
            echo json_encode($response);
        }else{
            if($lang=='es')
            {	
                $response = array('flag' => '0', 'message' => 'Todos los campos son obligatorios.');
            }else{
                $response = array('flag' => '0', 'message' => 'All fields are mandatory.');
            }
            //$response = array('flag' => '0', 'message' => 'Please add parameter all parameter.');
            echo json_encode($response);
        } 
        
    }catch(PDOException $e){

      $response = array('flag'=>'0', 'message' => $e->getMessage());
      echo json_encode($response);
    }