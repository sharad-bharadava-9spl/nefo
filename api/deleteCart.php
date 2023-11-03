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

            if(!empty($_POST['product_id'])){
                $product_id = $_POST['product_id'];
            }else{
                $product_id = ""; 
            }


            if(!empty($_POST['macAddress'])){
                $macAddress = $_POST['macAddress'];
            }else{
                $macAddress = ""; 
            }

            $userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit,maxCredit, photoExt FROM users WHERE user_id = '{$_REQUEST['user_id']}'";
            $result = $pdo->prepare($userDetails);
            $result->execute();
            $row = $result->fetch();
            $admintype = $row['userGroup'];

           
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

            /*product detail*/
            $checkProduct = $pdo->query("SELECT product_id FROM cartmobile WHERE product_id = '$product_id' AND user_id = '$user_id'");
            $checkProduct = $checkProduct->fetch();
            if(!empty($checkProduct['product_id'] == $product_id)){

               
                $Query="DELETE FROM cartmobile WHERE user_id = '$user_id' AND product_id = '$product_id'";
                $deleteCart = $pdo->prepare("$Query")->execute();

                if($deleteCart){

                    /*count for user product*/
                    $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
                    $result = $pdo->prepare("$cartCountData");
                    $result->execute();
                    $userCount = $result->rowCount();
                    $usercatfetch = $result->fetch();
                    $cat_id = $usercatfetch['category_id'];

                    /*total of product count in store cart*/
                    $cartpriceCountData = "SELECT SUM(extra_price) AS total_price  FROM cartmobile WHERE user_id = '$user_id'";
                    $resultpricecount = $pdo->prepare("$cartpriceCountData");
                    $resultpricecount->execute();
                    $usertotalpriceData = $resultpricecount->fetch();
                    $userproducttotal = $usertotalpriceData['total_price'];
                    
                    /*total discount for user product*/
                    $cartdiscountCountData = "SELECT SUM(cart_discount) AS discount  FROM cartmobile WHERE user_id = '$user_id'";
                    $resultdiscountcount = $pdo->prepare("$cartdiscountCountData");
                    $resultdiscountcount->execute();
                    $usertotaldiscountData = $resultdiscountcount->fetch();

                    if(!empty($usertotaldiscountData['discount'])){
                        $userdiscounttotal = abs($usertotaldiscountData['discount']);
                    }else{
                        $userdiscounttotal = 0;
                    }

                    /*user credit*/
                    $userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit, photoExt, maxCredit,available_credit,used_credit FROM users WHERE user_id = '$user_id'";
                    $resultuserDetails = $pdo->prepare("$userDetails");
                    $resultuserDetails->execute();
                    $usercreditDetail = $resultuserDetails->fetch();
                    $userproduct_grandtotal = $userproducttotal - $usercreditDetail['credit'];
                    $user_credit  = abs($usercreditDetail['credit']);
                    $usergrandtotal= $userproduct_grandtotal;

                    /*check club wise demain detail*/
                    $club_name = $_POST['club_name'];
                    $clubcreditDetails = "SELECT * FROM systemsettings WHERE domain = '$club_name'";
                    $resultclubcreditDetails = $pdo->prepare("$clubcreditDetails");
                    $resultclubcreditDetails->execute();
                    $clubDetail = $resultclubcreditDetails->fetch();
                    $clubcredit = $clubDetail['creditOrDirect'];
                    
                    $usercreditTotal = abs($usercreditDetail['credit']) + abs($usercreditDetail['maxCredit']);
                    /*check grand total*/
                    $userproduct_grandtotal = $userproducttotal - abs($usercreditTotal) - $userdiscounttotal;
                    $usedcredit = $usercreditDetail['credit'] - $userproducttotal;
                    

                    /*check user credit total*/
                    if($userproducttotal > $usercreditTotal){

                        $user_credit  = $userproducttotal -  $userdiscounttotal;
                        if($user_credit < $usercreditDetail['credit']){
                            $user_mainCredit = $userproducttotal - $usercreditTotal - $userdiscounttotal; 
                           // $remain .= $lang['remainingcredit'];
                            $user_pay_title = $language['remainingcredit'];
                            $user_pay_credit = abs($userproduct_grandtotal) .' '.'€';
                            $user_pay_updatecredit_title = $language['updatedcredit'];
                            $user_pay_updatecredit =  $user_credit .' '.'€';
                            $usergrandtotal  = 0;
                            $userActualCredit = abs($user_credit);
                            $user_discountprice =  $userproducttotal - $userdiscounttotal; 
                        }else{
                            $user_credit     = abs($usercreditDetail['credit']);
                            $user_mainCredit = $userproducttotal - $userdiscounttotal - $user_credit; 
                            $user_pay_title = 'Pay';
                            $user_pay_credit =  number_format($user_mainCredit,2).' '.'€';
                            $user_pay_updatecredit_title = $language['updatedcredit'] ;
                            $user_pay_updatecredit = $user_credit .' '.'€';
                            $usergrandtotal  = number_format($user_mainCredit,2);
                            $userActualCredit = abs($user_credit);
                            $user_discountprice =  $userproducttotal - $userdiscounttotal; 
                        }

                    }else if($userproducttotal < $usercreditDetail['credit']){
                        $user_credit     = $userproducttotal -  $userdiscounttotal ;
                        $user_mainCredit = $usercreditDetail['credit'] - $user_credit;
                        $user_pay_title = $language['remainingcredit'];
                        $user_pay_credit =abs($user_mainCredit) .' '.'€';
                        $usergrandtotal= 0;
                        $userActualCredit =$usercreditDetail['credit'] - $user_mainCredit;
                        $user_pay_updatecredit_title = $language['updatedcredit'];
                        $user_pay_updatecredit = $userActualCredit .' '.'€';
                        $user_discountprice =  $userproducttotal - $userdiscounttotal; 

                        /*update user credit detail*/
                        $userUpcredit = abs($user_mainCredit);
                        if($usercreditDetail['maxCredit'] != 0.00){
                            $updateCreditDetail = "UPDATE users SET used_credit = '$usedcredit' WHERE user_id = '$user_id'";
                            $updateCredit = $pdo->prepare($updateCreditDetail);
                            $update = $updateCredit->execute();
                        }else{
                            $updateCreditDetail = "UPDATE users SET used_credit = '$usedcredit' ,available_credit = '$userUpcredit' WHERE user_id = '$user_id'";
                            $updateCredit = $pdo->prepare($updateCreditDetail);
                            $update = $updateCredit->execute();
                        }
                       

                    }else if($userproducttotal == $usercreditDetail['credit']){

                        $user_mainCredit = $userproducttotal - $userdiscounttotal;
                        $user_credit     = $user_mainCredit;
                        $usedupcredit    = $userproducttotal - $user_credit;
                        $user_pay_title = $language['remainingcredit'];
                        $user_pay_credit = abs($usedupcredit) .' '.'€';
                        $user_pay_updatecredit_title = $language['updatedcredit'];
                        $user_pay_updatecredit = abs($user_mainCredit) .' '.'€';
                        $usergrandtotal= 0;
                        $userActualCredit = abs($usedupcredit);
                        $user_discountprice =  $userproducttotal - $userdiscounttotal; 

                        /*update user credit detail*/
                        $userUpcredit = abs($user_mainCredit);
                        if($usercreditDetail['maxCredit'] != 0.00){
                            $updateCreditDetail = "UPDATE users SET used_credit = '$usedcredit' WHERE user_id = '$user_id'";
                            $updateCredit = $pdo->prepare($updateCreditDetail);
                            $update = $updateCredit->execute();
                        }else{
                            $updateCreditDetail = "UPDATE users SET used_credit = '$usedcredit' ,available_credit = '$userUpcredit' WHERE user_id = '$user_id'";
                            $updateCredit = $pdo->prepare($updateCreditDetail);
                            $update = $updateCredit->execute();
                        }

                    }else{
                        
                        if(!empty($usercreditDetail['maxCredit']) && !empty($usercreditDetail['credit'])){

                            $user_mainCredit = $userproducttotal - $userdiscounttotal;
                            $user_credit     = number_format($usercreditDetail['credit'],2);
                            $remaincredit = number_format($usercreditDetail['credit'],2);
                            $user_pay_title = 'pay';
                            $user_pay_credit = abs($remaincredit) .' '.'€';
                            $user_pay_updatecredit_title = $language['updatedcredit'];
                            $user_pay_updatecredit =  $user_credit .' '.'€';
                            $usergrandtotal1 = abs($userproducttotal) - abs($userdiscounttotal);
                            $usergrandtotal= $usergrandtotal1 - abs($usercreditDetail['credit']);
                            $userActualCredit = abs($user_credit);
                            $user_discountprice =  $userproducttotal - $userdiscounttotal; 

                            /*update user credit detail*/
                            $userUpcredit = abs($user_mainCredit);
                            if($usercreditDetail['maxCredit'] != 0.00){
                            $updateCreditDetail = "UPDATE users SET used_credit = '$usedcredit' WHERE user_id = '$user_id'";
                            $updateCredit = $pdo->prepare($updateCreditDetail);
                            $update = $updateCredit->execute();
                            }else{
                                $updateCreditDetail = "UPDATE users SET used_credit = '$usedcredit' ,available_credit = '$userUpcredit' WHERE user_id = '$user_id'";
                                $updateCredit = $pdo->prepare($updateCreditDetail);
                                $update = $updateCredit->execute();
                            }
                           
                        }else{
                            $user_credit     = number_format($usercreditTotal,2);
                            $user_mainCredit = $userproducttotal - $usercreditTotal - $userdiscounttotal;
                            $user_pay_title = 'Pay';
                            $user_pay_credit = abs($user_mainCredit) .' '.'€';
                            $user_pay_updatecredit_title = $language['updatedcredit'];
                            $user_pay_updatecredit = $user_credit .' '.'€';
                            $usergrandtotal= number_format($userproduct_grandtotal,2);
                            $userActualCredit = abs($user_credit);
                            $user_discountprice =  $userproducttotal - $userdiscounttotal; 
                        }
                       
                    }   

                        /* get notification count */
                        $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                        $resultcntdata = $pdo->prepare("$notificntdata");
                        $resultcntdata->execute();
                        $countnotfication = $resultcntdata->rowCount();

                        if(!empty($usercatfetch['is_driectprice'])){
                           $is_driectprice = $usercatfetch['is_driectprice'];
                        }else{
                            $is_driectprice = "";
                        }


                        if($userCount == 0){
                            if($lang=='es')
                            {	
                                $response = array('flag' => '1', 'message' => 'Producto eliminado del carrito con éxito.','cart_count'=> 0,'total_price' => '','user_credit' => '','user_discount' => 0,'user_grand_total'=> '', 'usercredit_title' => $user_pay_title,'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' => number_format($userActualCredit,2),'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice);
                            }else{
                                $response = array('flag' => '1', 'message' => 'Product removed from cart successfully.','cart_count'=> 0,'total_price' => '','user_credit' => '','user_discount' => 0,'user_grand_total'=> '', 'usercredit_title' => $user_pay_title,'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' => number_format($userActualCredit,2),'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice);
                            }
                            //$response = array('flag' => '1', 'message' => 'Delete product in cart successfully.','cart_count'=> 0,'total_price' => '','user_credit' => '','user_discount' => 0,'user_grand_total'=> '', 'usercredit_title' => $user_pay_title,'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' => number_format($userActualCredit,2),'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice);
                        }else{

                            if($clubcredit == 1){

                                if($lang=='es')
                                {	
                                    $response = array('flag' => '1', 'message' => 'Producto eliminado del carrito con éxito.','cart_count'=> $userCount,'total_price' => $userproducttotal,'user_discount' => '-'. $userdiscounttotal,'user_credit' => '-'. abs($user_credit), 'usercredit_title' => $user_pay_title,'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' => number_format($userActualCredit,2),'user_grand_total' => number_format($usergrandtotal,2),'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice);
                                }else{
                                    $response = array('flag' => '1', 'message' => 'Product removed from cart successfully.','cart_count'=> $userCount,'total_price' => $userproducttotal,'user_discount' => '-'. $userdiscounttotal,'user_credit' => '-'. abs($user_credit), 'usercredit_title' => $user_pay_title,'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' => number_format($userActualCredit,2),'user_grand_total' => number_format($usergrandtotal,2),'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice);
                                }
                                //$response = array('flag' => '1', 'message' => 'Delete product in cart successfully.','cart_count'=> $userCount,'total_price' => $userproducttotal,'user_discount' => '-'. $userdiscounttotal,'user_credit' => '-'. abs($user_credit), 'usercredit_title' => $user_pay_title,'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' => number_format($userActualCredit,2),'user_grand_total' => number_format($usergrandtotal,2),'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice);
                            }else{

                                $userproducttotal = 'Pay' .' '. $userproducttotal .' '.'€';
                                if($lang=='es')
                                {	
                                    $response = array('flag' => '1', 'message' => 'Producto eliminado del carrito con éxito.','cart_count'=> $userCount,'total_price' => $userproducttotal,'user_credit' =>'-'. abs($user_credit),'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice);
                                }else{
                                    $response = array('flag' => '1', 'message' => 'Product removed from cart successfully.','cart_count'=> $userCount,'total_price' => $userproducttotal,'user_credit' =>'-'. abs($user_credit),'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice);
                                }
                                //$response = array('flag' => '1', 'message' => 'Delete product in cart successfully.','cart_count'=> $userCount,'total_price' => $userproducttotal,'user_credit' =>'-'. abs($user_credit),'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice);
                            }
                        }

                }else{
                    if($lang=='es')
                    {	
                        $response = array('flag' => '0', 'message' => 'Please add parameter in languageid.');
                    }else{
                        $response = array('flag' => '0', 'message' => 'Please add parameter in languageid.');
                    }
                    //$response = array('flag' => '0', 'message' => 'Please add parameter in languageid.');
                }
                echo json_encode($response);
            }else{
                if($lang=='es')
                {	
                    $response = array('flag' => '0', 'message' => 'El producto ya se ha eliminado de tu carrito.');
                }else{
                    $response = array('flag' => '0', 'message' => 'Product has already been removed from your cart.');
                }
                //$response = array('flag' => '0', 'message' => 'Product already deleted.');
                echo json_encode($response);
            }
        }else{
            if($lang=='es')
            {	
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
