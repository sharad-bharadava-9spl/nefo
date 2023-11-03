<?php
    include('connectionM.php'); 
//header('Content-type: text/plain; charset=utf-8'); 

    try{

        if(!empty($_POST['language'])){
            $language = $_POST['language'];
        }else{
            $language = "";
        } 

        if(!empty($_POST['club_name'])){
            $domain = $_POST['club_name'];
        }else{
            $domain = "";
        }


        if(!empty($_POST['user_id'])){
            $user_id = $_POST['user_id'];
        }else{
            $user_id = ""; 
        }
        
        $checkSystemsetting = "SELECT * FROM systemsettings WHERE domain = '$domain'";

        $resultSystemsetting = $pdo->prepare("$checkSystemsetting");
        $resultSystemsetting->execute();

        if($resultSystemsetting->rowCount() > 0){
            $row = $resultSystemsetting->fetch();

            /* get notification count */
            $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
            $resultcntdata = $pdo->prepare("$notificntdata");
            $resultcntdata->execute();
            $countnotfication = $resultcntdata->rowCount();



            $response['data'] = array();
            $new_arr = array();
            if($lang=='es')
            {	
                $response = array('flag' => '1','message' => 'Stripe key found successfully','notification_count' => $countnotfication);
            }else{
                $response = array('flag' => '1','message' => 'Stripe key found successfully','notification_count' => $countnotfication);
            }
            //$response = array('flag' => '1','message' => 'Stripe key found successfully','notification_count' => $countnotfication);

            if($row['stripe_payment_Testkey'] !=''){
                $selectTestkey = $row['stripe_payment_Testkey'];
            }else{
                $selectTestkey = "";
            }

            if($row['stripe_payment_Livekey'] !=''){
                $selectLivekey = $row['stripe_payment_Livekey'];
            }else{
                $selectLivekey = "";
            }

            if($row['stripekey_status'] == 0){
                $selectoption = $selectTestkey;
            }elseif($row['stripekey_status'] == 1){
                $selectoption = $selectLivekey;
            }else{
                $selectoption = '';
            }
                 
                $new_arr['stripe_key'] = base64_encode($selectoption);
                $response['data'] = $new_arr;

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
