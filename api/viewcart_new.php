<?php 
    include('connectionM.php'); 
    include('language/common.php'); 
    
   
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

        if(!empty($_POST['macAddress'])){
            $macAddress = $_POST['macAddress'];
        }else{
            $macAddress = ""; 
        }
        $remain = "";
        $userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit,maxCredit, photoExt FROM users WHERE user_id = '{$_REQUEST['user_id']}'";
        $result = $pdo->prepare($userDetails);
        $result->execute();
        $row = $result->fetch();
        $admintype = $row['userGroup'];

        if(!empty($lang == 'es') || !empty($lang == 'en') ){
            $usercartData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
            $resultcartData = $pdo->prepare("$usercartData");
            $resultcartData->execute();

                if($resultcartData->rowCount() > 0){

                    /*total of product count in store cart*/
                    /*$cartpriceCountData = "SELECT SUM(extra_price) AS total_price  FROM cartmobile WHERE user_id = '$user_id'";*/
                    $cartpriceCountData = "SELECT  SUM(REPLACE(extra_price, ',', '')) AS total_price  FROM cartmobile WHERE user_id = '$user_id'";
                    $resultpricecount = $pdo->prepare("$cartpriceCountData");
                    $resultpricecount->execute();
                    $usertotalpriceData = $resultpricecount->fetch();
                    $userproducttotal = abs($usertotalpriceData['total_price']);

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
                    $userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit, photoExt, maxCredit,creditEligible FROM users WHERE user_id = '$user_id'";
                    $resultuserDetails = $pdo->prepare("$userDetails");
                    $resultuserDetails->execute();
                    $usercreditDetail = $resultuserDetails->fetch();
                   

                    /*check club wise demain detail*/
                    $club_name = $_POST['club_name'];
                    $clubcreditDetails = "SELECT * FROM systemsettings WHERE domain = '$club_name'";
                    $resultclubcreditDetails = $pdo->prepare("$clubcreditDetails");
                    $resultclubcreditDetails->execute();
                    $clubDetail = $resultclubcreditDetails->fetch();
                    $clubcredit = $clubDetail['creditOrDirect'];


                    $userCredit= abs($usercreditDetail['credit']);
                    $usermaxCredit= abs($usercreditDetail['maxCredit']);
                    $usercreditotal = $userCredit + $usermaxCredit;

                    /*check grand total*/
                    $userproduct_grandtotal = $userproducttotal - abs($userCredit) - $userdiscounttotal;
                    $usedcredit = $usercreditDetail['credit'] - $userproducttotal;

               //print_r($usermaxCredit);
                  //print_r($usercreditotal); exit;
                  
                    /*start check user credit with detail*/
                    if($userCredit != "" && $usermaxCredit != ""){

                        if($usercreditotal > $userproducttotal){
                            $user_credit  =  $usercreditotal - $userproducttotal;
                            $user_mainCredit =  $usercreditotal - $userproducttotal - $userdiscounttotal;
                            $usedmaxcredit = abs($usercreditDetail['credit']) - $userproducttotal;
                            $user_pay_title = $language['remainingcredit'];
                            $user_pay_credit = abs($userproduct_grandtotal) .' '.'€';
                            $user_pay_updatecredit_title = $language['updatedcredit'];
                            $user_pay_updatecredit =  $user_credit .' '.'€';
                            $usergrandtotal  = 0;
                            $userActualCredit = abs($user_credit);
                            $user_discountprice =  $userproducttotal - $userdiscounttotal;

                        }else if($usercreditotal < $userproducttotal){

                            $user_credit     = abs($usercreditDetail['credit']);
                            $usedmaxcredit = abs($usercreditDetail['credit']) - $userproducttotal;
                            $user_mainCredit = $userproducttotal - $userdiscounttotal - $user_credit; 
                            $user_pay_title = 'Pay';
                            $user_pay_credit =  number_format($user_mainCredit,2).' '.'€';
                            $user_pay_updatecredit_title = $language['updatedcredit'] ;
                            $user_pay_updatecredit = $user_credit .' '.'€';
                            $usergrandtotal  = number_format($user_mainCredit,2);
                            $userActualCredit = abs($user_credit);
                            $user_discountprice =  $userproducttotal - $userdiscounttotal;
                           
                        }else if($usercreditotal == $userproducttotal){

                            $user_mainCredit = $userproducttotal - $userdiscounttotal;
                            $usedmaxcredit = abs($usercreditDetail['credit']) - $userproducttotal;
                            $user_credit     = $user_mainCredit;
                            $usedupcredit    = $userproducttotal - $user_credit;
                            $user_pay_title = $language['remainingcredit'];
                            $user_pay_credit = abs($usedupcredit) .' '.'€';
                            $user_pay_updatecredit_title = $language['updatedcredit'];
                            $user_pay_updatecredit = abs($user_mainCredit) .' '.'€';
                            $usergrandtotal= 0;
                            $userActualCredit = abs($usedupcredit);
                            $user_discountprice =  $userproducttotal - $userdiscounttotal; 
                        }
                       
                    }else if($userCredit != ""){

                        if($userCredit > $userproducttotal){

                            $user_mainCredit =  $userCredit - $userproducttotal - $userdiscounttotal;
                            $user_pay_title = $language['remainingcredit'];
                            $user_pay_credit = abs($userproduct_grandtotal) .' '.'€';
                            $user_pay_updatecredit_title = $language['updatedcredit'];
                            $user_pay_updatecredit =  $user_credit .' '.'€';
                            $usergrandtotal  = 0;
                            $userActualCredit = abs($user_credit);

                        }else if($userCredit < $userproducttotal){

                            $user_credit     = abs($usercreditDetail['credit']);
                            $user_mainCredit = $userproducttotal - $userdiscounttotal - $user_credit; 
                            $user_pay_title = 'Pay';
                            $user_pay_credit =  number_format($user_mainCredit,2).' '.'€';
                            $user_pay_updatecredit_title = $language['updatedcredit'] ;
                            $user_pay_updatecredit = $user_credit .' '.'€';
                            $usergrandtotal  = number_format($user_mainCredit,2);
                            $userActualCredit = abs($user_credit);
                            $user_discountprice =  $userproducttotal - $userdiscounttotal;
                           
                        }else if($userCredit == $userproducttotal){

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
                        }
                        
                    }else{


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
                        
                    }
                    /*end check user credit with detail*/
             
                    /*total of product count in store cart*/
                    $cartpriceCountData = "SELECT SUM(extra_price) AS total_price  FROM cartmobile WHERE user_id = '$user_id'";
                    $resultpricecount = $pdo->prepare("$cartpriceCountData");
                    $resultpricecount->execute();
                    $usertotalpriceData = $resultpricecount->fetch();
                    $userproducttotal = $usertotalpriceData['total_price'];


                    $response['data'] = array();
                    $new_arr = array();
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
                    /* get notification count */
                    $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                    $resultcntdata = $pdo->prepare("$notificntdata");
                    $resultcntdata->execute();
                    $countnotfication = $resultcntdata->rowCount();
                    
                    if($clubcredit == 1){
                        if($lang=='es')
                        {	
                            $response = array('flag' => '1','message' => '¡Carrito cargado con éxito!','cart_count'=> $resultcartData->rowCount(),'total_price' => $userproducttotal,'user_discountprice' => number_format(abs($user_discountprice),2),'user_discount' => number_format($userdiscounttotal,2),'usercredit_title' => $user_pay_title,'user_credit' =>number_format(abs($user_credit),2),'user_grand_total' => number_format($usergrandtotal,2),'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' =>number_format($userActualCredit,2),'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'usedmaxcredit' => abs($usedmaxcredit));
                        }else{
                            $response = array('flag' => '1','message' => 'Cart loaded successfully!','cart_count'=> $resultcartData->rowCount(),'total_price' => $userproducttotal,'user_discountprice' => number_format(abs($user_discountprice),2),'user_discount' => number_format($userdiscounttotal,2),'usercredit_title' => $user_pay_title,'user_credit' =>number_format(abs($user_credit),2),'user_grand_total' => number_format($usergrandtotal,2),'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' =>number_format($userActualCredit,2),'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'usedmaxcredit' => abs($usedmaxcredit));
                        }
                        //$response = array('flag' => '1','message' => 'Cart Found Successfull','cart_count'=> $resultcartData->rowCount(),'total_price' => $userproducttotal,'user_discountprice' => number_format(abs($user_discountprice),2),'user_discount' => number_format($userdiscounttotal,2),'usercredit_title' => $user_pay_title,'user_credit' =>number_format(abs($user_credit),2),'user_grand_total' => number_format($usergrandtotal,2),'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' =>number_format($userActualCredit,2),'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'usedmaxcredit' => abs($usedmaxcredit));
                    }else{
                        $userproducttotal = 'Pay' .' '. $userproducttotal;
                        if($lang=='es')
                        {	
                            $response = array('flag' => '1','message' => '¡Carrito cargado con éxito!','cart_count'=> $resultcartData->rowCount(),'total_price' => $userproducttotal,'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'usedmaxcredit' => abs($usedmaxcredit));
                        }else{
                            $response = array('flag' => '1','message' => 'Cart loaded successfully!','cart_count'=> $resultcartData->rowCount(),'total_price' => $userproducttotal,'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'usedmaxcredit' => abs($usedmaxcredit));
                        }
                        //$response = array('flag' => '1','message' => 'Cart Found Successfull','cart_count'=> $resultcartData->rowCount(),'total_price' => $userproducttotal,'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication,'usedmaxcredit' => abs($usedmaxcredit));
                    }
                    
                    $response['data'] = array();
                    $cartarr = array();
                    while($cartData = $resultcartData->fetch()){ 

                        if(!empty($cartData['description'])){
                            $product_descritpion = $cartData['description'];
                        }else{
                            $product_descritpion = '';
                        }

                        if(!empty($cartData['medicaldescription'])){
                            $product_medicaldescritpion = $cartData['medicaldescription'];
                        }else{
                            $product_medicaldescritpion = '';
                        } 
                        
                        /*get image data in product table*/
                        $categoryid  = $cartData['category_id'];
                        $product_id   = $cartData['product_id'];

                        if($categoryid == 1){
                            $selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt FROM flower g, purchases p WHERE p.category = $categoryid AND p.productid = $product_id AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
                            $resultFlower = $pdo->prepare("$selectFlower");
                            $resultFlower->execute();
                            $flower = $resultFlower->fetch();
                            

                            /*image path*/
                            if(!empty($flower['purchaseid'] && $flower['photoExt'])){
                                $imagepath = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/purchases/' . $flower['purchaseid'] . '.' .  $flower['photoExt'];
                            }else{
                                $imagepath = SITE_ROOT."/api/image/noimage.png";
                            }
                        }

                        if($categoryid == 2){
                            $selectExtract = "SELECT h.extractid, h.name, h.extract, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM extract h, purchases p WHERE p.category = $categoryid AND p.productid = $product_id AND p.productid = h.extractid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
                            $resultsExtract = $pdo->prepare("$selectExtract");
                            $resultsExtract->execute();
                            $extract = $resultsExtract->fetch();

                            /*image path*/
                            if(!empty($extract['purchaseid'] && $extract['photoExt'])){
                                $imagepath = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/purchases/' . $extract['purchaseid'] . '.' .  $extract['photoExt'];
                            }else{
                                $imagepath = SITE_ROOT."/api/image/noimage.png";
                            }
                        }

                        if($categoryid != 1 && $categoryid != 2){
                            $selectproductimage = "SELECT pr.productid, pr.name, pr.description,pr.medicaldescription,p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt ,p.category FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = $product_id AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC";
                            $resultproductimage = $pdo->prepare("$selectproductimage");
                            $resultproductimage->execute();
                            $product = $resultproductimage->fetch();
                            $purchaseid = $product['purchaseid'];

                            /*image path*/
                            if(!empty($product['purchaseid'] && $product['photoExt'])){
                                $imagepath = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/purchases/' . $product['purchaseid'] . '.' .  $product['photoExt'];
                            }else{
                                $imagepath = SITE_ROOT."/api/image/noimage.png";
                            }
                        }

                        $cartarr['product_id']      = $cartData['product_id'];
                        $cartarr['category_id']     = $cartData['category_id'];
                        $cartarr['category_name']   = $cartData['category_name'];
                        $cartarr['category_type']       = $cartData['category_type'];
                        $cartarr['product_name']        = $cartData['product_name'];
                        $cartarr['product_image']       = $imagepath;
                        $cartarr['product_description'] = $product_descritpion;
                        $cartarr['product_price']       = $cartData['product_price'];
                        $cartarr['extra_price']         = $cartData['extra_priceval'];
                        $cartarr['extra_price_count']   = $cartData['extra_price'];
                        $cartarr['useroriginal_discount']   = $cartData['originaldiscount_value'].' '.'%';

                        if($cartData['cart_discount'] == 0 || $cartData['cart_discount'] == NULL){
                            $cartarr['is_discount'] = "false";   
                            $cartarr['user_discount']  = "0";
                        }else{
                            $cartarr['is_discount'] = "true";     
                            if($cartarr['is_driectprice'] != 'true'){
                                $cartarr['user_discount']  = $cartData['cart_discount'] .' '.'€' ;
                            }else{
                                $cartarr['user_discount']  = $cartData['cart_discount'] .' '.'%' ;
                            }
                        }

                        if($cartData['is_driectprice'] != "" || $cartData['is_driectprice'] != NULL){
                            $cartarr['is_driectprice'] = $cartData['is_driectprice'];   
                        }else{
                            $cartarr['is_driectprice'] = "";  
                        }
                        
                        if($cartData['extra_price'] == 0 || $cartData['extra_price'] == NULL){
                           $cartarr['discount_price'] = "false";
                           $cartarr['user_discount_price']  = "0";
                        }else{
                            $cartarr['user_discount_price']  = number_format($cartData['extra_price'] -$cartData['cart_discount'],2);
                            $cartarr['discount_price'] = "true";
                        }
                      
                        $response['data'][] = $cartarr;
                    }
                }else{
                    if($lang=='es')
                    {	
                        $response = array('flag' => '0', 'message' => 'Algo ha ido mal, por favor inténtelo de nuevo.');
                    }else{
                        $response = array('flag' => '0', 'message' => 'Something went wrong, please try again.');
                    }
                    //$response = array('flag' => '0', 'message' => 'data not found.');
                }
               echo json_encode($response);
        }else{
            if($lang=='es')
            {	
                $response = array('flag' => '0', 'message' => 'Please add parameter in language id.');
            }else{
                $response = array('flag' => '0', 'message' => 'Please add parameter in language id.');
            }
            //$response = array('flag' => '0', 'message' => 'Please add parameter in language id.');
            echo json_encode($response);
        }
    }catch(PDOException $e){

        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response);
    }

?>