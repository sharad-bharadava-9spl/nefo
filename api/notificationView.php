<?php
    include('connectionM.php'); 
   // ini_set("display_errors", "on");
    try{

        if(!empty($_POST['language'])){
            $language = $_POST['language'];
        }else{
            $language = "";
        }

        if(!empty($_POST['limit'])){
            $limit = $_POST['limit'];
        }else{
            $limit = "";
        }

        if(!empty($_POST['offset'])){
            $offset = $_POST['offset'] - 1;
        }else{
            $offset = "";
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

        $updateNotificationStatus = "SELECT * FROM pushnotification  WHERE user_id = '".$user_id."' OR user_id is NULL";
        $UpdateNotificationresult = $pdo->prepare("$updateNotificationStatus");
        $UpdateNotificationresult->execute();
        $unreadNotifications = "SELECT * from pushnotification WHERE notification_status = 'unread' OR notification_status is null AND  (user_id = '".$user_id."' OR user_id is NULL)";
        $unreadNotificationsResult = $pdo->prepare("$unreadNotifications");
        $unreadNotificationsResult->execute();
        $unreadCount = $unreadNotificationsResult->rowCount();
        if($UpdateNotificationresult->rowCount() > 0){
            while($notification_Data = $UpdateNotificationresult->fetch()){
                  
                $notification_id = $notification_Data['id'];
                $selectReadBy = "SELECT read_by FROM pushnotification WHERE id = '$notification_id'";
                $readByDetail = $pdo->prepare($selectReadBy);
                $readByDetail->execute();
                $readBy_data = $readByDetail->fetch();
                $readBy_users = $readBy_data['read_by'];
                $read_user_arr = explode(",", $readBy_users);

                 if($readBy_users == ''){
                    $readBy_users = $user_id;
                }
                else if(in_array($user_id, $read_user_arr)){
                    $readBy_users = $readBy_users;
                }else{
                     $readBy_users = $readBy_users.",".$user_id;
                }
                $updatNotificationDetail = "UPDATE pushnotification SET  read_by = '$readBy_users' WHERE id = '$notification_id'";
                $notificationdetail = $pdo->prepare($updatNotificationDetail);
                $updatenotedata = $notificationdetail->execute();
            }
        }


        if(!empty($_POST['limit'])){
            $notificationdata = "SELECT DISTINCT unique_num,title,description,image,note_type,create_at FROM pushnotification ORDER BY create_at DESC LIMIT $limit OFFSET $offset";
        }else{
            $notificationdata = "SELECT DISTINCT unique_num,title,description,image,note_type,create_at FROM pushnotification ORDER BY create_at DESC";
        }

        if(!empty($_POST['user_id'])){ 
            if(!empty($_POST['limit'])){
                $notificationdata = "SELECT DISTINCT unique_num,title,description,image,note_type,create_at,user_id FROM pushnotification WHERE user_id = '".$user_id."' OR user_id is NULL ORDER BY create_at DESC LIMIT $limit OFFSET $offset ";
            }else{
                $notificationdata = "SELECT DISTINCT unique_num,title,description,image,note_type,create_at,user_id FROM pushnotification  WHERE user_id = '".$user_id."' OR user_id is NULL  ORDER BY create_at DESC";
            }
        }
        $resultnotification = $pdo->prepare("$notificationdata");
        $resultnotification->execute();
        $countnotfication = $resultnotification->rowCount();
        if($resultnotification->rowCount() > 0){
            $response['data'] = array();
            $new_arr = array();
             
            $notificntdata= "SELECT DISTINCT unique_num ,title,description,image FROM pushnotification WHERE user_id = '".$user_id."'";
            $resultcntdata = $pdo->prepare("$notificntdata");
            $resultcntdata->execute();
           // $countnotfication = $resultcntdata->rowCount();

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

            if($lang=='es')
            {	
                $response = array('flag' => '1','message' => 'Notification data loaded successfully' , 'rowcount' => $countnotfication, 'unread_count'=> $unreadCount,'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice);
            }else{
                $response = array('flag' => '1','message' => 'Notification data loaded successfully' , 'rowcount' => $countnotfication, 'unread_count'=> $unreadCount,'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice);
            }
            //$response = array('flag' => '1','message' => 'Notification data Found Successfull' , 'rowcount' => $countnotfication, 'unread_count'=> $unreadCount,'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice);

                while($userData = $resultnotification->fetch()){
                    /*image path*/
                    if(!empty($userData['image'])){
                        $imagepath = SITE_ROOT.'/images/_' .$_REQUEST['club_name'] . '/notifications/' .$userData['image'];
                    }else{
                        $imagepath = "";
                    }

                    $new_arr['notification_id'] = $userData['unique_num'];
                    $new_arr['title']       = $userData['title'];
                    $new_arr['description'] = $userData['description'];
                    $new_arr['image']       = $imagepath;
                    $new_arr['notification_type']       = $userData['note_type'];
                    $new_arr['create_at']   = date('d-m-Y H:i:s',strtotime($userData['create_at']));
                    $response['data'][] = $new_arr;
                }

                echo json_encode($response);
        }else{
                if($lang=='es')
                {	
                    $response = array('flag'=>'0', 'message' => 'Algo ha ido mal, por favor inténtelo de nuevo.');
                }else{
                    $response = array('flag'=>'0', 'message' => 'Something went wrong, please try again.');
                }
                //$response = array('flag'=>'0', 'message' => 'Data not found.');
                echo json_encode($response);
        }

    }catch(PDOException $e){

        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response);
    }
