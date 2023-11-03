<?php 
include('connectionM.php'); 

    try{

        if(!empty($_POST['language'])){
            $language = $_POST['language'];
        }else{
            $language = "";
        }

        if(!empty($_POST['notification_id'])){
            $notification_id = $_POST['notification_id'];
        }else{
            $notification_id = "";
        }

        if(!empty($_POST['macAddress'])){
            $macAddress = $_POST['macAddress'];
        }else{
            $macAddress = ""; 
        }

        if(!empty($_POST['user_id'])){
            $user_id = $_POST['user_id'];
        }else{
            $user_id = ""; 
        }

        $getNotificationData ="SELECT * FROM pushnotification WHERE unique_num = '$notification_id'";
        $resultcntdata = $pdo->prepare("$getNotificationData");
        $resultcntdata->execute();
        
        while ($countnotfication = $resultcntdata->fetch()) {

            $OriginalId = $countnotfication['id'];
            $deleteNotification = "DELETE FROM pushnotification WHERE id = '$OriginalId'";
            $delenotificationdata = $pdo->prepare("$deleteNotification");
            $deleteData = $delenotificationdata->execute();

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
               
            /*Get Admin type*/
            $userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit,maxCredit,photoExt,email FROM users WHERE user_id = '$user_id'";
            $resultUser = $pdo->prepare($userDetails);
            $resultUser->execute();
            $row = $resultUser->fetch();
            $admintype = $row['userGroup'];

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

            if($deleteData){

                $notificntdata1= "SELECT DISTINCT unique_num ,title,description,image FROM pushnotification";
                $resultcntdata1 = $pdo->prepare("$notificntdata1");
                $resultcntdata1->execute();
                $countnotfication1 = $resultcntdata1->rowCount();

                /* get notification count */
                $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                $resultcntdata = $pdo->prepare("$notificntdata");
                $resultcntdata->execute();
                $countnotfication = $resultcntdata->rowCount();

                if($language == 'en'){
                    $successmsg = "Notification deleted successfully.";
                }
                if($language == "es"){
                    $successmsg = str_replace("'","\'",str_replace('%', '&#37;', trim("Notificación borrado con éxito.")));;
                }

                $response = array('flag' => '1', 'message' => $successmsg, 'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice, 'rowcount' => $countnotfication1,'notification_count' => $countnotfication);
            }else{
                if($lang=='es')
                {	
                    $response = array('flag' => '0', 'message' => 'Algo ha ido mal, por favor inténtelo de nuevo.');
                }else{
                    $response = array('flag' => '0', 'message' => 'Something went wrong, please try again.');
                }
                //$response = array('flag' => '0', 'message' => 'Notification Can not delete,Please Try again.');
            }
        }
        echo json_encode($response);

    }catch(PDOException $e){

        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response);
    }