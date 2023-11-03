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

        if(!empty($_POST['order_id'])){
            $order_id = $_POST['order_id'];
        }else{
            $order_id = ""; 
        }

        if(!empty($_POST['macAddress'])){
            $macAddress = $_POST['macAddress'];
        }else{
            $macAddress = ""; 
        }



        if($lang == 'es' || $lang == 'en'){ 

            $selectOrders = "SELECT * FROM sales WHERE userid='$user_id' AND order_id = '$order_id'";
            $resultorder = $pdo->prepare("$selectOrders");
            $resultorder->execute();
            $order = $resultorder->fetch();
            $orderStatus = $order['order_status'];
            $paymentMode = $order['payment_mode'];
            $userOrderCredit = $order['amount'] - $order['user_discount'];
            $orderUpdateDate = date('Y-m-d H:i:s');

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

            /* get notification count */
            $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
            $resultcntdata = $pdo->prepare("$notificntdata");
            $resultcntdata->execute();
            $countnotfication = $resultcntdata->rowCount();

            if($orderStatus == 4){
                if($lang == 'es'){
                    $response = array('flag' => '0', 'message' => 'Tu pedido ya ha sido cancelado.','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                }else{
                    $response = array('flag' => '0', 'message' => 'Your order has already been cancelled.','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                }
                //$response = array('flag' => '0', 'message' => 'Your order already cancel.','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                    echo json_encode($response);
            }else{

                if($orderStatus == 1 && $paymentMode == 'credit'){

                        $updateOrderStatus = "UPDATE sales SET order_status = '4', updated_at = '$orderUpdateDate' WHERE order_id = '$order_id' AND userid = '$user_id'";
                           
                        $resultupdateData = $pdo->prepare($updateOrderStatus);
                        $updateData =$resultupdateData->execute();

                        if($updateData){

                            /*Get User Credit*/
                            $userCreditDetail = "SELECT * FROM users WHERE user_id = '$user_id'";
                            $selectUserData = $pdo->prepare($userCreditDetail);
                            $selectUserData->execute();
                            $usersCredit = $selectUserData->fetch();

                                if(!empty($usersCredit['credit'])){
                                    $usercreditdata = $usersCredit['credit'] + $userOrderCredit;
                                }else{
                                    $usercreditdata = "";
                                }

                                $updateuserdetail = "UPDATE users SET credit = '$usercreditdata' WHERE  user_id = '$user_id'";
                           
                                $resultupdateUserData = $pdo->prepare($updateuserdetail);
                                $updateData =$resultupdateUserData->execute();
                            if($lang == 'es'){
                                $response = array('flag' => '1', 'message' => 'Tu pedido se ha cancelado correctamente.','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                            }else{
                                $response = array('flag' => '1', 'message' => 'Your order has been cancelled successfully.','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                            }    
                            //$response = array('flag' => '1', 'message' => 'Your order cancel successfully.','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                            echo json_encode($response);
                        }
                }else{
                    if($lang == 'es'){
                        $response = array('flag' => '0', 'message' => "Tu pedido se está preparando, ¡ya no puede cancelarlo!");
                    }else{
                        $response = array('flag' => '0', 'message' => "Your order is currently being prepared, you can no longer cancel it!");
                    } 
                    //$response = array('flag' => '0', 'message' => "Currently your order is Prepareing so can't possible to cancel order noew.");
                    echo json_encode($response);
                }
            }

        }else{
            $response = array('flag' => '0', 'message' => 'Please add parameter all parameter.');
            echo json_encode($response);
        }


    }catch(PDOException $e){

        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response);
    }