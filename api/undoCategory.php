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
       
        if(!empty($_POST['macAddress'])){
            $macAddress = $_POST['macAddress'];
        }else{
            $macAddress = ""; 
        }
        
        /*Get user cart data in last cart undo  id*/
        $getUserCartData ="SELECT * FROM cartmobile WHERE user_id = '$user_id' ORDER BY cat_undo_num DESC";
        $resultcntdata = $pdo->prepare("$getUserCartData");
        $resultcntdata->execute();
        $UserCartData = $resultcntdata->fetch();
       
        /*Delete user cartundo last id detail*/
        $lastDescCartUndoId = $UserCartData['cat_undo_num'];
        $userCartId = $UserCartData['user_id'];
        $deleteUserCartData = "DELETE FROM cartmobile WHERE user_id = '$userCartId' AND cat_undo_num = '$lastDescCartUndoId' ";
        $delCartdata = $pdo->prepare("$deleteUserCartData");
        $deleteData = $delCartdata->execute();

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
                $response = array('flag' => '1', 'message' => 'Producto eliminado del carrito con éxito.','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice, 'cartcount' => $userCount,'notification_count' => $countnotfication);
            }else{
                $response = array('flag' => '1', 'message' => 'Product removed from cart successfully.','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice, 'cartcount' => $userCount,'notification_count' => $countnotfication);
            }
            //$response = array('flag' => '1', 'message' => 'Cart Product Delete Successfully.','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice, 'cartcount' => $userCount,'notification_count' => $countnotfication);
        }else{
            if($lang=='es')
            {	
                $response = array('flag' => '0', 'message' => 'Error al borrar el producto del carrito, por favor inténtelo de nuevo.');
            }else{
                $response = array('flag' => '0', 'message' => 'Error deleting cart product, please try again.');
            }
            //$response = array('flag' => '0', 'message' => 'Cart Product Can not delete,Please Try again.');
        }
        echo json_encode($response);


    }catch(PDOException $e){

        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response);
    }