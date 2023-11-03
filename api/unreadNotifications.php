<?php
    include('connectionM.php');

    try{

        if(!empty($_POST['language'])){
            $language = $_POST['language'];
        }else{
            $language = "";
        }

        if(!empty($_POST['user_id'])){
            $user_id = $_POST['user_id'];
        }else{
            $user_id = ""; 
        }

        $unreadNotifications = "SELECT * from pushnotification WHERE (user_id = '".$user_id."' OR user_id is NULL) AND (NOT FIND_IN_SET($user_id,read_by) OR read_by is NULL)";
        $unreadNotificationsResult = $pdo->prepare("$unreadNotifications");
        $unreadNotificationsResult->execute();
        $unreadCount = $unreadNotificationsResult->rowCount();
        if($lang=='es')
        {	
            $response = array('flag' => '1','message' => 'unread count' , 'unread_count'=> $unreadCount);
        }else{
            $response = array('flag' => '1','message' => 'unread count' , 'unread_count'=> $unreadCount);
        }
        //$response = array('flag' => '1','message' => 'unread count!' , 'unread_count'=> $unreadCount);
        echo json_encode($response);

    }catch(PDOException $e){

        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response);
    }