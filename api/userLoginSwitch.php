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

        if($user_id){

                /*count for user product*/
                $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
                $result = $pdo->prepare("$cartCountData");
                $result->execute();
                $userCount = $result->rowCount();
                
               /* get notification count */
                $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                $resultcntdata = $pdo->prepare("$notificntdata");
                $resultcntdata->execute();
                $countnotfication = $resultcntdata->rowCount();
                if($lang=='es')
                {	
                    $response = array('flag' => '1','message' => '¡Carrito cargado con éxito!','cart_count'=> $userCount,'count_notfication' => $countnotfication,'notification_count' => $countnotfication);
                }else{
                    $response = array('flag' => '1','message' => 'Cart loaded successfully!','cart_count'=> $userCount,'count_notfication' => $countnotfication,'notification_count' => $countnotfication);
                }
                //$response = array('flag' => '1','message' => 'User cart found Successfull','cart_count'=> $userCount,'count_notfication' => $countnotfication,'notification_count' => $countnotfication);
        }else{
            if($lang=='es')
            {	
                $response = array('flag' => '0','message' => 'No se han encontrado los datos del socio. Por favor, inténtelo de nuevo.');
            }else{
                $response = array('flag' => '0','message' => 'Member details not found, please try again.');
            }
            //$response = array('flag' => '0','message' => 'User detail not found,please try again');
        }
        echo json_encode($response);

    }catch(PDOException $e){

        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response);
    }
