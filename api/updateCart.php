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

        if(!empty($_POST['product_id'])){
            $product_id = $_POST['product_id'];
        }else{
            $product_id = ""; 
        }

       
        $extra_price = $_POST['qty'];
        $product_price = $_POST['product_price'];
        $updatedtime = date("Y-m-d H:i:s");
       

        if(!empty($lang == 'es') || !empty($lang == 'en') && $user_id && $product_id || $extra_price || $product_price ){
             
                $usercartData = "SELECT * FROM cartmobile WHERE user_id = '$user_id' AND product_id = '$product_id'";
                $resultcartData = $pdo->prepare("$usercartData");
                $resultcartData->execute();
                $cartData = $resultcartData->fetch();
                $catid = $cartData['category_id'];
               

                /*check condidtion user cart data*/
                if(!empty($cartData['extra_priceval'])){
                    $cartextra_val = $cartData['extra_priceval'];
                }else{
                    $cartextra_val = "";
                }

                /*check condidtion user cart data*/
                if(!empty($cartData['product_price'])){
                    $cartproduct_price = $cartData['product_price'];
                }else{
                    $cartproduct_price = "";
                }

                 /*check condidtion product purchase id*/
                if(!empty($cartData['purchase_id'])){
                    $cartpurchase_id = $cartData['purchase_id'];
                }else{
                    $cartpurchase_id = "";
                }

                /*check condidtion product meddiscount*/
                if(!empty($cartData['medDiscount'])){
                    $medDiscount = $cartData['medDiscount'];
                }else{
                    $medDiscount = "";
                }

                if($product_id == $cartData['product_id'] && $user_id == $cartData['user_id']){

                        /*flag=0 qty/gram flag=1 extrapricetotal*/
                        if(!empty($_POST['flag'] == 0)){

                            $cartextraprice = $extra_price / $cartproduct_price;
                            $userextrapricetotal1 = $extra_price * $cartData['product_price'];

                            // All Discount
                            $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE user_id = $user_id AND categoryid = '$catid'";
                              $result = $pdo->prepare($selectCategoryDiscount);
                              $result->execute();
                            $rowCD = $result->fetch();
                            $catDiscount = $rowCD['discount'];

                            $selectSettings = "SELECT highRollerWeekly, minAge, closingMail, dispensaryGift, barGift, menuType, medicalDiscount, logouttime, logoutredir, dispDonate, dispExpired, dispenseLimit, showAge, showGender, keepNumber, membershipFees, medicalDiscountPercentage, bankPayments, creditOrDirect, visitRegistration, cropOrNot, puestosOrNot, openAndClose, barMenuType, flowerLimit, extractLimit, realWeight, showStock, showOrigPrice, checkoutDiscount, consumptionMin, consumptionMax, showStockBar, showOrigPriceBar, barTouchscreen, iPadReaders, cashdro, creditchange, expirychange, exentoset, menusortdisp, menusortbar, dispsig, barsig, openmenu, keypads, moneycount, customws, negcredit, language, nobar, sigtablet, entrysys, entrysysstay, entrysyssecs, dooropener, cuotaincrement, checkoutDiscountBar, chipcost, fingerprint, pagination, dooropenfor, workertracking FROM systemsettings";
                            $result = $pdo->prepare($selectSettings);
                            $result->execute();
                            $row = $result->fetch();

                            $medicalDiscount = $row['medicalDiscount'];
                            $medicalDiscountPercentage = $row['medicalDiscountPercentage'];
                            
                            if ($medicalDiscountPercentage == 1) {
                              $medicalDiscountCalc = 1 - ($medicalDiscount / 100);
                            }

                            // Individual purchase discount
                            $selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE user_id = $user_id AND purchaseid = '$cartpurchase_id'";
                            $result = $pdo->prepare($selectPurchaseDiscount);
                            $result->execute();
                            $rowD = $result->fetch();
                            $prodDiscount = $rowD['discount'];
                            $prodFijo = $rowD['fijo'];

                            $userDetails = "SELECT usageType,discount FROM users WHERE user_id = '$user_id'";
                            $resultuserDetails = $pdo->prepare($userDetails);
                            $resultuserDetails->execute();
                            $userData = $resultuserDetails->fetch();
                            $userDiscount = $userData['discount'];
                            
                            if ($prodFijo != 0 || $prodFijo != '') {
                              $prodDiscount = $prodFijo;
                              $pricefijo = abs(number_format($prodFijo,2)) - abs($userextrapricetotal1);
                              $price = abs($pricefijo);
                              $is_directprice = 'true';

                              if($userextrapricetotal1 < $pricefijo){

                                $pricefijoupdate = abs(number_format($prodFijo,2)) - abs($userextrapricetotal1);
                              }else{
                                $pricefijoupdate = 0 ;
                              }
                              
                              /*$updatediscountDetail = "UPDATE inddiscounts SET fijo = '$pricefijoupdate' WHERE user_id = $user_id AND purchaseid = '$purchaseid'";
                              $updatefijoDiscount = $pdo->prepare($updatediscountDetail);
                              $updatefijoDiscount->execute();*/
                              
                            } else if ($prodDiscount != 0 || $prodDiscount != '') {
                              $prodDiscount = ($prodDiscount * $userextrapricetotal1) / 100;
                              $price = $userextrapricetotal1;
                              $is_directprice = 'false';
                              
                            } else if ($catDiscount != 0 || $catDiscount != '') {
                              $prodDiscount = ($catDiscount * $userextrapricetotal1) / 100;
                              $price = $userextrapricetotal1;
                              $is_directprice = 'false';

                            } else if ($userDiscount != 0) {
                              $prodDiscount = ($userDiscount * $userextrapricetotal1) / 100;
                              $price = $userextrapricetotal1;
                              $is_directprice = 'false';
                              
                            } else if ($userData['usageType'] == '1') {
                              if ($medDiscount > 0) {
                                $prodDiscount = ($medDiscount * $userextrapricetotal1) / 100;
                                $price = $userextrapricetotal1;
                                $is_directprice = 'false';
                              } else if ($medicalDiscountPercentage == 1) {
                                $prodDiscount = 0;
                                $price = $userextrapricetotal1 * number_format($medicalDiscountCalc,2);
                                $is_directprice = 'false';
                              } else {
                                $prodDiscount = 0;
                                $price = $userextrapricetotal1 - number_format($medicalDiscount,2);
                                $is_directprice = 'false';
                              }
                            } else {
                              $prodDiscount = 0;
                              $price = $userextrapricetotal1;
                              $is_directprice = 'false';
                            }
                            
                            $updateCart = "UPDATE cartmobile SET extra_priceval = '$extra_price', extra_price = '$price' ,updated_at = '$updatedtime', cart_discount = '$prodDiscount',is_driectprice = '$is_directprice' WHERE user_id = '$user_id' AND product_id = '$product_id'";
                           
                            $resultupdateData = $pdo->prepare($updateCart);
                            $updateData =$resultupdateData->execute();

                            if($updateData){

                                /*count for user product*/
                                $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
                                 $result = $pdo->prepare($cartCountData);
                                $result->execute();
                                $userCount = $result->rowCount();

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

                                //print_r($userdiscounttotal); exit;

                                /*user credit*/
                                $userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit, photoExt, maxCredit,available_credit,used_credit FROM users WHERE user_id = '$user_id'";
                                $resultuserDetails = $pdo->prepare("$userDetails");
                                $resultuserDetails->execute();
                                $usercreditDetail = $resultuserDetails->fetch();
                                $userproduct_grandtotal = $userproducttotal - abs($usercreditDetail['credit']);
                                $user_credit  = abs($usercreditDetail['credit']);
                                $usergrandtotal= number_format($userproduct_grandtotal,2);

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
                                $userDiscountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id' AND product_id = '$product_id'";
                                $resultDisData = $pdo->prepare("$userDiscountData");
                                $resultDisData->execute();
                                $cartDisData = $resultDisData->fetch();
                                $cart_discount = $cartDisData['cart_discount'];
                                $is_driectprice1 = $cartDisData['is_driectprice'];
                                $originalDiscount = $cartDisData['originaldiscount_value'].' '.'%';

                                if($cart_discount == 0 || $cart_discount == NULL){
                                    $is_discount= "false";   
                                    $user_discount  = "0";
                                }else{
                                    $is_discount = "true"; 

                                    if($cartDisData['is_driectprice'] != 'true'){    
                                        $user_discount   = $userdiscounttotal .' '.'€' ;
                                    }else{
                                        $user_discount   = $userdiscounttotal .' '.'%' ;
                                    }
                                }
                                
                                if($extra_price == 0 || $extra_price == NULL){
                                    $discount_price = "false";
                                    $user_discount_price  = "0";
                                }else{
                                    $discount_price = "true";
                                    $user_discount_price  = $userextrapricetotal1 - $cart_discount;
                                }

                                if($cartDisData['is_driectprice'] != "" || $cartDisData['is_driectprice'] != NULL){
                                    $is_driectprice1 = $cartDisData['is_driectprice'];   
                                }else{
                                    $is_driectprice1 = "";  
                                } 

                                /* get notification count */
                                $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                                $resultcntdata = $pdo->prepare("$notificntdata");
                                $resultcntdata->execute();
                                $countnotfication = $resultcntdata->rowCount();

                                if($clubcredit == 1){

                                    if (strpos($extra_price,'.') !== false) {
                                        $newqty = number_format($extra_price,2);
                                    }else{
                                        $newqty =$extra_price;
                                    }

                                    if (strpos($user_discount_price,'.') !== false) {
                                        $userdiscount_price = number_format($user_discount_price,2);
                                    }else{
                                        $userdiscount_price =$user_discount_price;
                                    }


                                    if($lang=='es')
                                    {	
                                        $response = array('flag' => '1', 'message' => '¡Carrito cargado con éxito!','qty'=> $newqty,'product_price' => $cartproduct_price,'extraprice_total'=> $userextrapricetotal1,'cart_count'=> $userCount,'total_price' => $userproducttotal,'user_discount' => $userdiscounttotal,'user_credit' =>abs($user_credit),'user_grand_total' => number_format($usergrandtotal,2), 'usercredit_title' => $user_pay_title,'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' => number_format($userActualCredit,2),'useroriginal_discount'=>$originalDiscount,'is_discount' => $is_discount,'user_discount'=> '-'. $user_discount,'discount_price'=>$discount_price,'user_discount_price' => $userdiscount_price,'flag' => 0,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice1);
                                    }else{
                                        $response = array('flag' => '1', 'message' => 'Cart updated successfully!','qty'=> $newqty,'product_price' => $cartproduct_price,'extraprice_total'=> $userextrapricetotal1,'cart_count'=> $userCount,'total_price' => $userproducttotal,'user_discount' => $userdiscounttotal,'user_credit' =>abs($user_credit),'user_grand_total' => number_format($usergrandtotal,2), 'usercredit_title' => $user_pay_title,'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' => number_format($userActualCredit,2),'useroriginal_discount'=>$originalDiscount,'is_discount' => $is_discount,'user_discount'=> '-'. $user_discount,'discount_price'=>$discount_price,'user_discount_price' => $userdiscount_price,'flag' => 0,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice1);
                                    }
                                    //$response = array('flag' => '1', 'message' => 'Update cart in product successfully','qty'=> $newqty,'product_price' => $cartproduct_price,'extraprice_total'=> $userextrapricetotal1,'cart_count'=> $userCount,'total_price' => $userproducttotal,'user_discount' => $userdiscounttotal,'user_credit' =>abs($user_credit),'user_grand_total' => number_format($usergrandtotal,2), 'usercredit_title' => $user_pay_title,'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' => number_format($userActualCredit,2),'useroriginal_discount'=>$originalDiscount,'is_discount' => $is_discount,'user_discount'=> '-'. $user_discount,'discount_price'=>$discount_price,'user_discount_price' => $userdiscount_price,'flag' => 0,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice1);
                                }else{
                                    $userproducttotal = 'Pay' .' '. $userproducttotal .' '.'€';
                                    if($lang=='es')
                                    {	
                                        $response = array('flag' => '1', 'message' => '¡Carrito cargado con éxito!','qty'=> $extra_price,'product_price' => $cartproduct_price,'extraprice_total'=> $userextrapricetotal1,'cart_count'=> $userCount,'total_price' => $userproducttotal,'flag' => 0,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice1);
                                    }else{
                                        $response = array('flag' => '1', 'message' => 'Cart updated successfully!','qty'=> $extra_price,'product_price' => $cartproduct_price,'extraprice_total'=> $userextrapricetotal1,'cart_count'=> $userCount,'total_price' => $userproducttotal,'flag' => 0,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice1);
                                    }
                                    //$response = array('flag' => '1', 'message' => 'Update cart in product successfully','qty'=> $extra_price,'product_price' => $cartproduct_price,'extraprice_total'=> $userextrapricetotal1,'cart_count'=> $userCount,'total_price' => $userproducttotal,'flag' => 0,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice1);

                                }
                                echo json_encode($response);
                              /* $my_file =getcwd().'/authenticate.txt';
                               $handle = fopen($my_file, 'a') or die('Cannot open file:  '.$my_file);
                               $data = var_export($this->input->post(), true);
                               fwrite($handle, $data);
                               fclose($handle);exit;*/
                            }else{
                                if($lang=='es')
                                {	
                                    $response = array('flag' => '0', 'message' => 'Error al borrar el producto del carrito, por favor inténtelo de nuevo.');
                                }else{
                                    $response = array('flag' => '0', 'message' => 'Error updating cart product, please try again.');
                                }
                                //$response = array('flag' => '0', 'message' => 'Product not update to cart,please try again');
                                echo json_encode($response);
                            }

                        }elseif(!empty($_POST['flag'] == 1)){

                            $cartextraprice = $product_price / $cartproduct_price;
                            $userextrapricetotal = $cartextraprice * $cartproduct_price;

                            // All Discount
                            $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE user_id = $user_id AND categoryid = '$catid'";
                            $result = $pdo->prepare($selectCategoryDiscount);
                            $result->execute();
                            $rowCD = $result->fetch();
                            $catDiscount = $rowCD['discount'];

                            $selectSettings = "SELECT highRollerWeekly, minAge, closingMail, dispensaryGift, barGift, menuType, medicalDiscount, logouttime, logoutredir, dispDonate, dispExpired, dispenseLimit, showAge, showGender, keepNumber, membershipFees, medicalDiscountPercentage, bankPayments, creditOrDirect, visitRegistration, cropOrNot, puestosOrNot, openAndClose, barMenuType, flowerLimit, extractLimit, realWeight, showStock, showOrigPrice, checkoutDiscount, consumptionMin, consumptionMax, showStockBar, showOrigPriceBar, barTouchscreen, iPadReaders, cashdro, creditchange, expirychange, exentoset, menusortdisp, menusortbar, dispsig, barsig, openmenu, keypads, moneycount, customws, negcredit, language, nobar, sigtablet, entrysys, entrysysstay, entrysyssecs, dooropener, cuotaincrement, checkoutDiscountBar, chipcost, fingerprint, pagination, dooropenfor, workertracking FROM systemsettings";
                            $result = $pdo->prepare($selectSettings);
                            $result->execute();
                            $row = $result->fetch();

                            $medicalDiscount = $row['medicalDiscount'];
                            $medicalDiscountPercentage = $row['medicalDiscountPercentage'];
                            
                            if ($medicalDiscountPercentage == 1) {
                              $medicalDiscountCalc = 1 - ($medicalDiscount / 100);
                            }

                            // Individual purchase discount
                            $selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE user_id = $user_id AND purchaseid = '$cartpurchase_id'";
                            $result = $pdo->prepare($selectPurchaseDiscount);
                            $result->execute();
                            $rowD = $result->fetch();
                            $prodDiscount = $rowD['discount'];
                            $prodFijo = $rowD['fijo'];

                            $userDetails = "SELECT usageType,discount FROM users WHERE user_id = '$user_id'";
                            $resultuserDetails = $pdo->prepare($userDetails);
                            $resultuserDetails->execute();
                            $userData = $resultuserDetails->fetch();
                            $userDiscount = $userData['discount'];
                            
                            if ($prodFijo != 0 || $prodFijo != '') {
                              $prodDiscount = $prodFijo;
                              $pricefijo = abs(number_format($prodFijo,2)) - abs($userextrapricetotal);
                              $price = abs($pricefijo);
                              $is_directprice = 'true';

                              if($userextrapricetotal < $pricefijo){

                                $pricefijoupdate = abs(number_format($prodFijo,2)) - abs($userextrapricetotal);
                              }else{
                                $pricefijoupdate = 0 ;
                              }
                              
                              /*$updatediscountDetail = "UPDATE inddiscounts SET fijo = '$pricefijoupdate' WHERE user_id = $user_id AND purchaseid = '$purchaseid'";
                              $updatefijoDiscount = $pdo->prepare($updatediscountDetail);
                              $updatefijoDiscount->execute();*/
                              
                            } else if ($prodDiscount != 0 || $prodDiscount != '') {
                              $prodDiscount = ($prodDiscount * $userextrapricetotal) / 100;
                              $price = $userextrapricetotal;
                              $is_directprice = 'false';
                              
                            } else if ($catDiscount != 0 || $catDiscount != '') {
                              $prodDiscount = ($catDiscount * $userextrapricetotal) / 100;
                              $price = $userextrapricetotal;
                              $is_directprice = 'false';

                            } else if ($userDiscount != 0) {
                              $prodDiscount = ($userDiscount * $userextrapricetotal) / 100;
                              $price = $userextrapricetotal;
                              $is_directprice = 'false';
                              
                            } else if ($userData['usageType'] == '1') {
                              if ($medDiscount > 0) {
                                $prodDiscount = ($medDiscount * $userextrapricetotal) / 100;
                                $price = $userextrapricetotal;
                                $is_directprice = 'false';
                              } else if ($medicalDiscountPercentage == 1) {
                                $prodDiscount = 0;
                                $price = $userextrapricetotal * number_format($medicalDiscountCalc,2);
                                $is_directprice = 'false';
                              } else {
                                $prodDiscount = 0;
                                $price = $userextrapricetotal - number_format($medicalDiscount,2);
                                $is_directprice = 'false';
                              }
                            } else {
                              $prodDiscount = 0;
                              $price = $userextrapricetotal;
                              $is_directprice = 'false';
                            }

                            $updateCart = "UPDATE cartmobile SET extra_priceval = '$cartextraprice', extra_price = '$price',cart_discount = '$prodDiscount',updated_at = '$updatedtime',is_driectprice = '$is_directprice' WHERE user_id = '$user_id' AND product_id = '$product_id'";
                            $resultupdateData = $pdo->prepare("$updateCart");
                            $updateData =$resultupdateData->execute();

                            if($updateData){

                                /*count for user product*/
                                $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
                                 $result = $pdo->prepare("$cartCountData");
                                $result->execute();
                                $userCount = $result->rowCount();

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
                                $userproduct_grandtotal = $userproducttotal - abs($usercreditDetail['credit']);
                                $user_credit  = abs($usercreditDetail['credit']);
                                $usergrandtotal= number_format($userproduct_grandtotal,2);

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
                                $userDiscountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id' AND product_id = '$product_id'";
                                $resultDisData = $pdo->prepare("$userDiscountData");
                                $resultDisData->execute();
                                $cartDisData = $resultDisData->fetch();
                                $cart_discount = $cartDisData['cart_discount'];
                                $is_driectprice1 = $cartDisData['is_driectprice'];
                                $originalDiscount = $cartDisData['originaldiscount_value'].' '.'%';


                                if($cart_discount== 0 || $cart_discount == NULL){
                                    $is_discount= "false";   
                                    $user_discount  = "0";
                                }else{
                                    $is_discount = "true";     
                                    if($cartDisData['is_driectprice'] != 'true'){    
                                        $user_discount   = $userdiscounttotal .' '.'€' ;
                                    }else{
                                        $user_discount   = $userdiscounttotal .' '.'%' ;
                                    }
                                }
                                
                                if($extra_price == 0 || $extra_price == NULL){
                                    $discount_price = "false";
                                    $user_discount_price  = "0";
                                }else{
                                    $discount_price = "true";
                                    $user_discount_price  = $userextrapricetotal - $cart_discount;
                                }

                                if($cartDisData['is_driectprice'] != "" || $cartDisData['is_driectprice'] != NULL){
                                    $is_driectprice1 = $cartDisData['is_driectprice'];   
                                }else{
                                    $is_driectprice1 = "";  
                                }

                                /* get notification count */
                                $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                                $resultcntdata = $pdo->prepare("$notificntdata");
                                $resultcntdata->execute();
                                $countnotfication = $resultcntdata->rowCount();


                                
                                if($clubcredit == 1){

                                    if (strpos($cartextraprice,'.') !== false) {
                                        $newqty = number_format($cartextraprice,2);
                                    }else{
                                        $newqty =$cartextraprice;
                                    }

                                    if (strpos($user_discount_price,'.') !== false) {
                                        $userdiscount_price = number_format($user_discount_price,2);
                                    }else{
                                        $userdiscount_price =$user_discount_price;
                                    }

                                    if($lang=='es')
                                    {	
                                        $response = array('flag' => '1', 'message' => '¡Carrito cargado con éxito!','qty'=> $newqty,'product_price' => $cartproduct_price,'extraprice_total'=> $userextrapricetotal,'cart_count'=> $userCount,'total_price' => $userproducttotal,'user_discount' => $userdiscounttotal,'user_credit' => '-'. abs($user_credit),'user_grand_total' => number_format($usergrandtotal,2), 'usercredit_title' => $user_pay_title,'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' => number_format($userActualCredit,2),'useroriginal_discount'=>$originalDiscount,'is_discount' => $is_discount,'user_discount'=>'-'. $user_discount,'discount_price'=>$discount_price,'user_discount_price' => $userdiscount_price,'flag' => 1,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice1);
                                    }else{
                                        $response = array('flag' => '1', 'message' => 'Cart updated successfully!','qty'=> $newqty,'product_price' => $cartproduct_price,'extraprice_total'=> $userextrapricetotal,'cart_count'=> $userCount,'total_price' => $userproducttotal,'user_discount' => $userdiscounttotal,'user_credit' => '-'. abs($user_credit),'user_grand_total' => number_format($usergrandtotal,2), 'usercredit_title' => $user_pay_title,'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' => number_format($userActualCredit,2),'useroriginal_discount'=>$originalDiscount,'is_discount' => $is_discount,'user_discount'=>'-'. $user_discount,'discount_price'=>$discount_price,'user_discount_price' => $userdiscount_price,'flag' => 1,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice1);
                                    }
                                    //$response = array('flag' => '1', 'message' => 'Update cart in product successfully','qty'=> $newqty,'product_price' => $cartproduct_price,'extraprice_total'=> $userextrapricetotal,'cart_count'=> $userCount,'total_price' => $userproducttotal,'user_discount' => $userdiscounttotal,'user_credit' => '-'. abs($user_credit),'user_grand_total' => number_format($usergrandtotal,2), 'usercredit_title' => $user_pay_title,'user_credit_detail' => $user_pay_credit,'user_pay_updatecredit_title' => $user_pay_updatecredit_title,'userupdate_credit' => $user_pay_updatecredit,'userupdatecredit' => number_format($userActualCredit,2),'useroriginal_discount'=>$originalDiscount,'is_discount' => $is_discount,'user_discount'=>'-'. $user_discount,'discount_price'=>$discount_price,'user_discount_price' => $userdiscount_price,'flag' => 1,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice1);

                                }else{
                                    $userproducttotal = 'Pay' .' '. $userproducttotal .' '.'€';
                                    if($lang=='es')
                                    {	
                                        $response = array('flag' => '1', 'message' => '¡Carrito cargado con éxito!','qty'=> $cartextraprice,'product_price' => $cartproduct_price,'extraprice_total'=> $userextrapricetotal,'cart_count'=> $userCount,'total_price' => $userproducttotal,'flag' => 1,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice1);
                                    }else{
                                        $response = array('flag' => '1', 'message' => 'Cart updated successfully!','qty'=> $cartextraprice,'product_price' => $cartproduct_price,'extraprice_total'=> $userextrapricetotal,'cart_count'=> $userCount,'total_price' => $userproducttotal,'flag' => 1,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice1);
                                    }
                                    //$response = array('flag' => '1', 'message' => 'Update cart in product successfully','qty'=> $cartextraprice,'product_price' => $cartproduct_price,'extraprice_total'=> $userextrapricetotal,'cart_count'=> $userCount,'total_price' => $userproducttotal,'flag' => 1,'notification_count' => $countnotfication,'user_discountprice' => number_format(abs($user_discountprice),2),'is_driectprice' => $is_driectprice1);

                                }
                                echo json_encode($response);
                                   /*$my_file =getcwd().'/authenticate.txt';
                                   $handle = fopen($my_file, 'a') or die('Cannot open file:  '.$my_file);
                                   $data = var_export($this->input->post(), true);
                                   fwrite($handle, $data);
                                   fclose($handle);exit;*/
                            }else{
                                if($lang=='es')
                                {	
                                    $response = array('flag' => '0', 'message' => 'Error al borrar el producto del carrito, por favor inténtelo de nuevo.');
                                }else{
                                    $response = array('flag' => '0', 'message' => 'Error updating cart product, please try again.');
                                }
                                //$response = array('flag' => '0', 'message' => 'Product not update to cart,please try again');
                                echo json_encode($response);
                            }

                        }else{
                            if($lang=='es')
                            {	
                                $response = array('flag' => '0', 'message' => 'Please add flag.');
                            }else{
                                $response = array('flag' => '0', 'message' => 'Please add flag.');
                            }
                            //$response = array('flag' => '0', 'message' => 'Please add flag.');
                            echo json_encode($response);
                        }
                        
                }else{
                    if($lang=='es')
                    {	
                        $response = array('flag' => '0', 'message' => 'Producto no encontrado.');
                    }else{
                        $response = array('flag' => '0', 'message' => 'Product not found.');
                    }
                    //$response = array('flag' => '0', 'message' => 'Product not found.');
                    echo json_encode($response);
                }

        }else{
            if($lang=='es')
            {	
                $response = array('flag' => '0', 'message' => 'Please add parameter in language,userid,productid.');
            }else{
                $response = array('flag' => '0', 'message' => 'Please add parameter in language,userid,productid.');
            }
            //$response = array('flag' => '0', 'message' => 'Please add parameter in language,userid,productid.');
            echo json_encode($response);
        }
        
    }catch(PDOException $e){

        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response);
    }

?>
