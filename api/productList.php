<?php 
    include('connectionM.php');
   // ini_set("display_errors", "on");
   if(!empty($_POST['language'])){
        $lang = $_POST['language'];
    }else{
        $lang = ""; 
    }

    try{
         
        if(!empty($_POST['categoryid'])){
            $categoryid = $_POST['categoryid'];
        }else{
            $categoryid = "";
        }

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

        $userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit,maxCredit, photoExt FROM users WHERE user_id = '{$_REQUEST['user_id']}'";
        $result = $pdo->prepare($userDetails);
        $result->execute();
        $row = $result->fetch();
        $admintype = $row['userGroup'];


        if($lang == 'en' || $lang == 'es'){

            /*Flower category wise product*/
            if($categoryid  == 1){
            
                $selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt,p.medDiscount,g.description,g.medicaldescription FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
                $resultFlower = $pdo->prepare("$selectFlower");
                $resultFlower->execute();

                    if($resultFlower->rowCount() > 0){

                        /*count for user product*/
                        $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
                        $result = $pdo->prepare("$cartCountData");
                        $result->execute();
                        $userCount = $result->rowCount();
                        $userproarr = array();
                        while($userproduct = $result->fetch()){
                            $userproarr[] = $userproduct['product_id'];
                        }

                         /*total of product count in store cart*/
                        $cartpriceCountData = "SELECT SUM(extra_price) AS total_price  FROM cartmobile WHERE user_id = '$user_id'";
                        $resultpricecount = $pdo->prepare("$cartpriceCountData");
                        $resultpricecount->execute();
                        $usertotalpriceData = $resultpricecount->fetch();
                        $userproducttotal = $usertotalpriceData['total_price'];

                        $response['data'] = array();
                        $flowerarr = array();

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

                    if(is_null($topupcredit)){
                         $topupcredit = 0;
                    }        
                    if(is_null($preorder)){
                         $preorder = 0;
                    }        
                    if(is_null($showprice)){
                         $showprice = 0;
                    }

                        /* get notification count */
                        $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                        $resultcntdata = $pdo->prepare("$notificntdata");
                        $resultcntdata->execute();
                        $countnotfication = $resultcntdata->rowCount();

                        if(!empty($_POST['user_id'])){
                            if($lang=='es')
                            {	
                                $response = array('flag' => '1','message' => '¡Producto encontrado con éxito!','cart_count'=> $userCount,'total_price' => number_format($userproducttotal,'2').' '.'€','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                            }else{
                                $response = array('flag' => '1','message' => 'Product found successfully!','cart_count'=> $userCount,'total_price' => number_format($userproducttotal,'2').' '.'€','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                            }
                            //$response = array('flag' => '1','message' => 'Product Found Successfull','cart_count'=> $userCount,'total_price' => number_format($userproducttotal,'2').' '.'€','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                        }else{
                            if($lang=='es')
                            {	
                                $response = array('flag' => '1','message' => '¡Producto encontrado con éxito!','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice);
                            }else{
                                $response = array('flag' => '1','message' => 'Product found successfully!','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice);
                            }
                            //$response = array('flag' => '1','message' => 'Product Found Successfull','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice);
                        }


                        while($flower = $resultFlower->fetch()){

                            /*image path*/
                            if(!empty($flower['purchaseid']) && $flower['photoExt'] != ''){
                                $imagepath = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/purchases/' . $flower['purchaseid'] . '.' .  $flower['photoExt'];
                            }else{
                                $imagepath = "";
                            }


                            /*flower detail*/
                            if ($flower['breed2'] != '') {
                                $name = $flower['name'] . " x " . $flower['breed2'];
                            } else {
                                $name = $flower['name'];
                            }
                            
                            if ($flower['flowertype'] == 'Hybrid' && $flower['sativaPercentage'] > 0 && $flower['sativaPercentage'] != NULL) {
                                $percentageDisplay = number_format($flower['sativaPercentage'],0) . '% s.';
                            } else {
                                $percentageDisplay = '';
                            }

                            /*Grow type*/
                            $growtype = $flower['growType'];
                            $growtypeDetail = $pdo->query("SELECT growtype FROM growtypes WHERE growtypeid = '$growtype'");
                            $growtypeData = $growtypeDetail->fetch();

                            /*product detail*/
                            $productid = $flower['productid'];
                            $productDetail = $pdo->query("SELECT description,medicaldescription FROM products WHERE productid = '$productid'");
                            $productData = $productDetail->fetch();

                            if(!empty($flower['description'])){
                                $product_descritpion = $flower['description'];
                            }else{
                                $product_descritpion = '';
                            }

                            if(!empty($flower['medicaldescription'])){
                                $product_medicaldescritpion = $flower['medicaldescription'];
                            }else{
                                $product_medicaldescritpion = '';
                            }

                        

                            $productfinal_price = number_format($flower['salesPrice'],2);
                            $purchaseid =$flower['purchaseid'];

                            // All Discount category 2
                            $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE user_id = '$user_id' AND categoryid = 1";
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
                            $selectPurchaseDiscount = "SELECT * FROM inddiscounts WHERE user_id = '$user_id' AND purchaseid = '".$flower['purchaseid']."'";
                           // echo $selectPurchaseDiscount; 
                            $result = $pdo->prepare($selectPurchaseDiscount);
                            $result->execute();
                            $rowD = $result->fetch();
                            $prodDiscount = $rowD['discount'];
                            $prodFijo = $rowD['fijo'];
                            //echo "<pre>"; print_r($prodFijo); 


                            $userDetails = "SELECT usageType,discount FROM users WHERE user_id = '$user_id'";
                            $resultuserDetails = $pdo->prepare("$userDetails");
                            $resultuserDetails->execute();
                            $userData = $resultuserDetails->fetch();
                            $userDiscount = $userData['discount'];
                            
                            if ($prodFijo != 0 || $prodFijo != '') {
                             
                              $prodDiscount = $prodFijo .' '.'€';
                              $pricefijo = abs(number_format($prodFijo,2)) - abs($productfinal_price);
                              $price = abs($pricefijo);
                              $is_directprice = 'true';
                              $final_pro_price =  $productfinal_price - $prodDiscount .' '.'€';
                              /*if($productfinal_price < $pricefijo){
                                $pricefijoupdate = abs(number_format($prodFijo,2)) - abs($productfinal_price);
                              }else{
                                $pricefijoupdate = 0 ;
                              }
                              
                              $updatediscountDetail = "UPDATE inddiscounts SET fijo = '$pricefijoupdate' WHERE user_id = $user_id AND purchaseid = $purchaseid";
                              $updatefijoDiscount = $pdo->prepare($updatediscountDetail);
                              $updatefijoDiscount->execute();*/
                              
                            } else if ($prodDiscount != 0 || $prodDiscount != '') {
                                $prodDiscount = $prodDiscount .' '.'%' ;
                                $price = $productfinal_price;
                                $is_directprice = 'false';
                                $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                $final_pro_price = $price - $new_price_flower .' '.'€';
                              
                            } else if ($catDiscount != 0 || $catDiscount != '') {
                            
                                $prodDiscount = $catDiscount .' '.'%' ;
                                $price = $productfinal_price;
                                $is_directprice = 'false';
                                $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                $final_pro_price = $price - $new_price_flower .' '.'€';

                            } else if ($userDiscount  != 0) {
                              $prodDiscount = $userDiscount .' '.'%';
                              $price = $productfinal_price; 
                              $is_directprice = 'false';
                              $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                              $final_pro_price = $price - $new_price_flower .' '.'€';
                            
                            } else if ($userData['usageType'] == '1') {
                             
                              if ($flower['medDiscount'] > 0) {
                                $prodDiscount = $flower['medDiscount'] .' '.'%';
                                $price = $productfinal_price;
                                $is_directprice = 'false';
                                $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                $final_pro_price = $price - $new_price_flower .' '.'€';
                              } else if ($medicalDiscountPercentage == 1) {
                                $prodDiscount = $productfinal_price;
                                $price = $productfinal_price * number_format($medicalDiscountCalc,2);
                                $is_directprice = 'false';
                                $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                $final_pro_price = $price - $new_price_flower .' '.'€';
                              } else {
                                $prodDiscount = $productfinal_price;
                                $price = $productfinal_price - number_format($medicalDiscount,2);
                                $is_directprice = 'false';
                                $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                $final_pro_price = $price - $new_price_flower .' '.'€';
                              }
                            } else {
                              $prodDiscount = $productfinal_price;
                              $price = $productfinal_price;
                              $is_directprice = 'false';
                              $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                              $final_pro_price = $price - $new_price_flower .' '.'€';
                            }
                            if($prodDiscount && $price){

                                $flowerarr['is_directprice'] = $is_directprice;
                                $flowerarr['is_discount'] = "true";     
                                $flowerarr['discount_price'] = "true";
                                $flowerarr['user_discount'] =  $prodDiscount;
                                //$flowerarr['user_discount_price'] = abs($price - $prodDiscount) .' '.'€';
                                //$new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                         
                                $flowerarr['user_discount_price'] = $final_pro_price;   
                            }else{

                                $flowerarr['is_directprice'] = $is_directprice;
                                $flowerarr['is_discount'] = "false"; 
                                $flowerarr['discount_price'] = "false"; 
                                $flowerarr['user_discount'] = "0"; 
                                $flowerarr['user_discount_price'] = "0"; 
                            }

                            $flowerarr['category_id']       = 1;
                            
                            if(in_array($flower['productid'],$userproarr)){
                                    $userproductprice = $flower['productid'];
                                    $cartuserproduct = "SELECT * FROM cartmobile WHERE product_id = '$userproductprice' AND user_id = '$user_id'";
                                    $proresult = $pdo->prepare("$cartuserproduct");
                                    $proresult->execute();
                                    $userproductname = $proresult->fetch();
                                    $flowerarr['add_cart'] =  '1';
                                    if($showprice == 1){
                                        $flowerarr['sales_price'] = $userproductname['product_price'] .' '.'€';
                                    }else{
                                        $flowerarr['sales_price'] = "";
                                    }
                                    $final_extra_multiple_price = $final_pro_price * $userproductname['extra_priceval'];
                                  //  $flowerarr['extra_price'] = $userproductname['extra_price'] .' '.'€';  
                                    $flowerarr['extra_price'] = $final_extra_multiple_price .' '.'€';  
                                    $flowerarr['extra_price_count']   =  $userproductname['extra_priceval'];
                                    
                            }else{
                                    $flowerarr['extra_price']      ="0"; 
                                    $flowerarr['extra_price_count'] = "0";
                                    
                            }

                            if(!in_array($flower['productid'],$userproarr)){
                                    $userproductprice = $flower['productid'];
                                    $flowerarr['add_cart'] =  '0';
                                    if($showprice == 1){
                                        $flowerarr['sales_price'] =  $flower['salesPrice'] .' '.'€';
                                    }else{
                                        $flowerarr['sales_price'] =  "";
                                    }
                            }else{
                                    /*$flowerarr['add_cart'] = "";
                                    $flowerarr['sales_price'] =  "";*/
                            }
                            $flowerarr['category_name']     = 'Flowers';
                            $flowerarr['category_type']     = '2';
                            $flowerarr['product_id']        = $flower['productid'];
                            $flowerarr['purchaseid']        = $flower['purchaseid'];
                            $flowerarr['product_name']      = $name;
                            $flowerarr['breed2']            = $flower['breed2'];
                            $flowerarr['flower_type']       = $flower['flowertype'];
                            $flowerarr['grow_type']         = $growtypeData['growtype'];
                            $flowerarr['realquantity']      = $flower['realQuantity'];
                            $flowerarr['product_image']     = $imagepath;
                            $flowerarr['product_description'] = $product_descritpion;
                            $flowerarr['product_medicaldescription'] = $product_medicaldescritpion;
                            $flowerarr['percentageDisplay'] = $percentageDisplay;
                            $flowerarr['purchase_id'] = $flower['purchaseid'];
                            $response['data'][] = $flowerarr;
                        }
                        echo json_encode($response);
                    }else{
                        if($lang=='es')
                        {	
                            $response = array('flag' => '0', 'message' => 'Producto no encontrado.');
                        }else{
                            $response = array('flag' => '0', 'message' => 'Product Not Found.');
                        }
                        //$response = array('flag' => '0', 'message' => 'Product Not Found');
                        echo json_encode($response);
                    }
            }

            if($categoryid == 2){

                $selectExtract = "SELECT h.extractid, h.name, h.extract, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt,p.medDiscount,h.description,h.medicaldescription, h.extracttype, h.sativaPercentage, p.growType, h.extract FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
                $resultsExtract = $pdo->prepare("$selectExtract");
                $resultsExtract->execute();

                    if($resultsExtract->rowCount() > 0){
                        /*count for user product*/
                        $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
                        $result = $pdo->prepare("$cartCountData");
                        $result->execute();
                        $userCount = $result->rowCount();
                        $userproarr = array();
                        while($userproduct = $result->fetch()){
                            $userproarr[] = $userproduct['product_id'];
                        }

                         /*total of product count in store cart*/
                        $cartpriceCountData = "SELECT SUM(extra_price) AS total_price  FROM cartmobile WHERE user_id = '$user_id'";
                        $resultpricecount = $pdo->prepare("$cartpriceCountData");
                        $resultpricecount->execute();
                        $usertotalpriceData = $resultpricecount->fetch();
                        $userproducttotal = $usertotalpriceData['total_price'];


                        $response['data'] = array();
                        $extractarr = array();

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
                        if(is_null($topupcredit)){
                             $topupcredit = 0;
                        }        
                        if(is_null($preorder)){
                             $preorder = 0;
                        }        
                        if(is_null($showprice)){
                             $showprice = 0;
                        }
                        /* get notification count */
                        $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                        $resultcntdata = $pdo->prepare("$notificntdata");
                        $resultcntdata->execute();
                        $countnotfication = $resultcntdata->rowCount();

                        if(!empty($_POST['user_id'])){
                            if($lang=='es')
                            {	
                                $response = array('flag' => '1','message' => '¡Producto encontrado con éxito!','cart_count'=> $userCount,'total_price' => number_format($userproducttotal,'2').' '.'€','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                            }else{
                                $response = array('flag' => '1','message' => 'Product found successfully!','cart_count'=> $userCount,'total_price' => number_format($userproducttotal,'2').' '.'€','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                            }
                            //$response = array('flag' => '1','message' => 'Product Found Successfull','cart_count'=> $userCount,'total_price' => number_format($userproducttotal,'2').' '.'€','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                        }else{
                            if($lang=='es')
                            {	
                                $response = array('flag' => '1','message' => '¡Producto encontrado con éxito!','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice);
                            }else{
                                $response = array('flag' => '1','message' => 'Product found successfully!','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice);
                            }
                            //$response = array('flag' => '1','message' => 'Product Found Successfull','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice);
                        }

                            while($extract = $resultsExtract->fetch()){


                                /*Grow type*/
                                $growtype = $extract['growType'];
                                $growtypeDetail = $pdo->query("SELECT growtype FROM growtypes WHERE growtypeid = '$growtype'");
                                $growtypeData = $growtypeDetail->fetch();

                                if ($extract['extracttype'] == 'Hybrid' && $extract['sativaPercentage'] > 0 && $extract['sativaPercentage'] != NULL) {
                                    $percentageDisplay =  number_format($extract['sativaPercentage'],0) . '% s.';
                                } else {
                                    $percentageDisplay = '';
                                }
                            
                                /*image path*/
                                if(!empty($extract['purchaseid']) && $extract['photoExt'] != ''){
                                    $imagepath = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/purchases/' . $extract['purchaseid'] . '.' .  $extract['photoExt'];
                                }else{
                                    $imagepath = "";
                                }

                                /*product detail*/
                                $productid = $extract['productid'];
                                $productDetail = $pdo->query("SELECT description,medicaldescription FROM products WHERE productid = '$productid'");
                                $productData = $productDetail->fetch();

                                if(!empty($extract['description'])){
                                    $product_descritpion = $extract['description'];
                                }else{
                                    $product_descritpion = '';
                                }

                                if(!empty($extract['medicaldescription'])){
                                    $product_medicaldescritpion = $extract['medicaldescription'];
                                }else{
                                    $product_medicaldescritpion = '';
                                }

                                $productfinal_price = number_format($extract['salesPrice'],2);
                                $purchaseid =$extract['purchaseid'];

                                // All Discount category 2
                                $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE user_id = '$user_id' AND categoryid = 2";
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
                                $selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE user_id = '$user_id' AND purchaseid = '$purchaseid'";
                                $result = $pdo->prepare("$selectPurchaseDiscount");
                                $result->execute();

                                $rowD = $result->fetch();
                              //  echo "<pre>"; print_r($rowD);exit;
                                $prodDiscount = $rowD['discount'];
                                $prodFijo = $rowD['fijo'];


                                $userDetails = "SELECT usageType,discount FROM users WHERE user_id = '$user_id'";
                                $resultuserDetails = $pdo->prepare("$userDetails");
                                $resultuserDetails->execute();
                                $userData = $resultuserDetails->fetch();
                                $userDiscount = $userData['discount'];
                                
                                if ($prodFijo != 0 || $prodFijo != '') {
                                 
                                  $prodDiscount = $pricefijo .' '.'€';
                                  $pricefijo = abs(number_format($prodFijo,2)) - abs($productfinal_price);
                                  $price = abs($pricefijo);
                                  $is_directprice = 'true';
                                  $final_pro_price =  $productfinal_price - $prodDiscount .' '.'€';

                                  /*if($productfinal_price < $pricefijo){
                                    $pricefijoupdate = abs(number_format($prodFijo,2)) - abs($productfinal_price);
                                  }else{
                                    $pricefijoupdate = 0 ;
                                  }
                                  
                                  $updatediscountDetail = "UPDATE inddiscounts SET fijo = '$pricefijoupdate' WHERE user_id = $user_id AND purchaseid = $purchaseid";
                                  $updatefijoDiscount = $pdo->prepare($updatediscountDetail);
                                  $updatefijoDiscount->execute();*/
                                  
                                } else if ($prodDiscount != 0 || $prodDiscount != '') {
                                  $prodDiscount = $prodDiscount .' '.'%';
                                  $price = $productfinal_price;
                                  $is_directprice = 'false';
                                  $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                  $final_pro_price = $price - $new_price_flower .' '.'€';
                                  
                                } else if ($catDiscount != 0 || $catDiscount != '') {
                                  
                                  $prodDiscount =$catDiscount .' '.'%';
                                  $price = $productfinal_price;
                                  $is_directprice = 'false';
                                  $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                  $final_pro_price = $price - $new_price_flower .' '.'€';

                                } else if ($userDiscount  != 0) {
                                  $prodDiscount = $userDiscount .' '.'%';
                                  $price = $productfinal_price;
                                  $is_directprice = 'false';
                                  $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                  $final_pro_price = $price - $new_price_flower .' '.'€';
                                
                                } else if ($userData['usageType'] == '1') {
                                                                  //   echo "hiiii"; exit;

                                  if ($extract['medDiscount'] > 0) {
                                    $prodDiscount = $extract['medDiscount'] .' '.'%';
                                    $price = $productfinal_price;
                                    $is_directprice = 'false';
                                    $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                    $final_pro_price = $price - $new_price_flower .' '.'€';

                                  } else if ($medicalDiscountPercentage == 1) {
                                    $prodDiscount = $productfinal_price;
                                    $price = $productfinal_price * number_format($medicalDiscountCalc,2);
                                    $is_directprice = 'false';
                                    $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                    $final_pro_price = $price - $new_price_flower .' '.'€';
                                  } else {
                                    $prodDiscount = $productfinal_price;
                                    $price = $productfinal_price - number_format($medicalDiscount,2);
                                    $is_directprice = 'false';
                                    $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                    $final_pro_price = $price - $new_price_flower .' '.'€';
                                  }
                                } else {
                                  $prodDiscount = $productfinal_price;
                                  $price = $productfinal_price;
                                  $is_directprice = 'false';
                                  $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                  $final_pro_price = $price - $new_price_flower .' '.'€';
                                }

                                if($prodDiscount && $price){

                                    $extractarr['is_directprice'] = $is_directprice;     
                                    $extractarr['is_discount']    = "true";     
                                    $extractarr['discount_price'] = "true";
                                    $extractarr['user_discount']  =  $prodDiscount ;
                                    //$extractarr['user_discount_price'] =abs($price - $prodDiscount) .' '.'€';  
                                    $extractarr['user_discount_price'] = $final_pro_price;

                                }else{
                                    $extractarr['is_directprice'] = $is_directprice;
                                    $extractarr['is_discount'] = "false"; 
                                    $extractarr['discount_price'] = "false"; 
                                    $extractarr['user_discount'] = "0"; 
                                    $extractarr['user_discount_price'] = "0"; 
                                }

                                $extractarr['category_id']   = 2;
                                if(in_array($extract['productid'],$userproarr)){

                                    $userproductprice = $extract['productid'];
                                    $cartuserproduct = "SELECT * FROM cartmobile WHERE product_id = '$userproductprice' AND user_id = '$user_id'";
                                    $proresult = $pdo->prepare("$cartuserproduct");
                                    $proresult->execute();
                                    $userproductname = $proresult->fetch();
                                    $extractarr['add_cart'] =  '1';
                                    if($showprice == 1){
                                        $extractarr['sales_price'] = $userproductname['product_price'] .' '.'€';
                                    }else{
                                        $extractarr['sales_price'] = '';
                                    }
                                   
                                    $final_extra_multiple_price = $final_pro_price * $userproductname['extra_priceval'];
                                  //  $extractarr['extra_price'] = $userproductname['extra_price'] .' '.'€';  
                                    $extractarr['extra_price'] = $final_extra_multiple_price .' '.'€';  
                                    $extractarr['extra_price_count']   =  $userproductname['extra_priceval'];

                                }else{

                                    $extractarr['extra_price']      ="0"; 
                                    $extractarr['extra_price_count'] = "0"; 
                                }

                                if(!in_array($extract['productid'],$userproarr)){
                                        $userproductprice = $extract['productid'];
                                        $extractarr['add_cart'] =  '0';
                                        if($showprice == 1){
                                            $extractarr['sales_price'] =  $extract['salesPrice'] .' '.'€';
                                        }else{
                                            $extractarr['sales_price'] =  '';
                                        }
                                }else{
                                        /*$flowerarr['add_cart'] = "";
                                        $flowerarr['sales_price'] =  "";*/
                                }
                                $extractarr['category_name']     = 'Extract';
                                $extractarr['category_type']     = '2';
                                $extractarr['product_id']        = $extract['productid'];
                                $extractarr['product_name']      = $extract['name'];
                                if($showprice == 1){
                                    $extractarr['sales_price']       = $extract['salesPrice'] .' '.'€';
                                }else{
                                    $extractarr['sales_price']       = '';
                                }
                                $extractarr['realquantity']      = $extract['realQuantity'];
                                $extractarr['product_image']     = $imagepath;
                                $extractarr['product_description'] = $product_descritpion;
                                $extractarr['product_medicaldescription'] = $product_medicaldescritpion;
                                $extractarr['grow_type']         = $growtypeData['growtype'];
                                $extractarr['purchase_id'] = $extract['purchaseid'];
                                $extractarr['extract_type'] = $extract['extract'];
                                $extractarr['percentageDisplay'] = $percentageDisplay;
                                $response['data'][] = $extractarr;
                            } 
                        echo json_encode($response);  
                    }else{
                        if($lang=='es')
                        {	
                            $response = array('flag' => '0', 'message' => 'Producto no encontrado.');
                        }else{
                            $response = array('flag' => '0', 'message' => 'Product Not Found.');
                        }
                        //$response = array('flag' => '0', 'message' => 'Product Not Found');
                        echo json_encode($response);
                    }
            } 
            
            if($categoryid != 1 && $categoryid != 2){

                $selectProduct = "SELECT pr.productid, pr.name, pr.description,pr.medicaldescription,p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt ,p.category,p.medDiscount, pr.flowertype, pr.breed2, pr.sativaPercentage, p.growType  FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC";
                $resultsproduct = $pdo->prepare("$selectProduct");
                $resultsproduct->execute();

                    if($resultsproduct->rowCount() > 0){

                        /*count for user product*/
                        $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
                        $result = $pdo->prepare("$cartCountData");
                        $result->execute();
                        $userCount = $result->rowCount();
                        $userproarr = array();
                        while($userproduct = $result->fetch()){
                            $userproarr[] = $userproduct['product_id'];
                        }

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

                        if(is_null($topupcredit)){
                             $topupcredit = 0;
                        }        
                        if(is_null($preorder)){
                             $preorder = 0;
                        }        
                        if(is_null($showprice)){
                             $showprice = 0;
                        }

                        /* get notification count */
                        $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                        $resultcntdata = $pdo->prepare("$notificntdata");
                        $resultcntdata->execute();
                        $countnotfication = $resultcntdata->rowCount();

                        if(!empty($_POST['user_id'])){
                            if($lang=='es')
                            {	
                                $response = array('flag' => '1','message' => '¡Producto encontrado con éxito!','cart_count'=> $userCount,'total_price' => number_format($userproducttotal,'2').' '.'€','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                            }else{
                                $response = array('flag' => '1','message' => 'Product Found Successfully!','cart_count'=> $userCount,'total_price' => number_format($userproducttotal,'2').' '.'€','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                            }
                            //$response = array('flag' => '1','message' => 'Product Found Successfull','cart_count'=> $userCount,'total_price' => number_format($userproducttotal,'2').' '.'€','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                        }else{
                            if($lang=='es')
                            {	
                                $response = array('flag' => '1','message' => '¡Producto encontrado con éxito!','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice);
                            }else{
                                $response = array('flag' => '1','message' => 'Product Found Successfully!','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice);
                            }
                            //$response = array('flag' => '1','message' => 'Product Found Successfull','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice);
                        }
                            while($product = $resultsproduct->fetch()){

                                if ($product['breed2'] != '') {
                                    $name = $product['name'] . " x " . $product['breed2'];
                                } else {
                                    $name = $product['name'];
                                }

                                /*Grow type*/
                                $growtype = $product['growType'];
                                $growtypeDetail = $pdo->query("SELECT growtype FROM growtypes WHERE growtypeid = '$growtype'");
                                $growtypeData = $growtypeDetail->fetch();

                                if ($product['flowertype'] == 'Hybrid' && $product['sativaPercentage'] > 0 && $product['sativaPercentage'] != NULL) {
                                    $percentageDisplay = number_format($product['sativaPercentage'],0) . '% s.';
                                } else {
                                    $percentageDisplay = '';
                                }

                                /*image path*/
                                if(!empty($product['purchaseid']) && $product['photoExt'] != ''){
                                    $imagepath = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/purchases/' . $product['purchaseid'] . '.' .  $product['photoExt'];
                                }else{
                                    $imagepath = "";
                                }


                                /*category detail*/
                                $categoryid = $product['category'];
                                $categoryDetail = $pdo->query("SELECT * from categories WHERE id = '$categoryid'");
                                $category_name = $categoryDetail->fetch();

                                if(!empty($product['description'])){
                                    $product_descritpion = $product['description'];
                                }else{
                                    $product_descritpion = '';
                                }

                                if(!empty($product['medicaldescription'])){
                                    $product_medicaldescritpion = $product['medicaldescription'];
                                }else{
                                    $product_medicaldescritpion = '';
                                }


                                $productfinal_price = number_format($product['salesPrice'],2);
                                $purchaseid =$product['purchaseid'];

                                // All Discount category 2
                                $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE user_id = '$user_id' AND categoryid = '$categoryid'";
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
                                $selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE user_id = '$user_id' AND purchaseid = '$purchaseid'";
                                $result = $pdo->prepare("$selectPurchaseDiscount");
                                $result->execute();

                                $rowD = $result->fetch();
                                $prodDiscount = $rowD['discount'];
                                $prodFijo = $rowD['fijo'];


                                $userDetails = "SELECT usageType,discount FROM users WHERE user_id = '$user_id'";
                                $resultuserDetails = $pdo->prepare("$userDetails");
                                $resultuserDetails->execute();
                                $userData = $resultuserDetails->fetch();
                                $userDiscount = $userData['discount'];
                                
                                if ($prodFijo != 0 || $prodFijo != '') {
                                 
                                  $prodDiscount = $prodFijo .' '.'€';
                                  $pricefijo = abs(number_format($prodFijo,2)) - abs($productfinal_price);
                                  $price = abs($pricefijo);
                                  $is_directprice = 'true';
                                  $final_pro_price =  $productfinal_price - $prodDiscount .' '.'€';


                                  /*if($productfinal_price < $pricefijo){
                                    $pricefijoupdate = abs(number_format($prodFijo,2)) - abs($productfinal_price);
                                  }else{
                                    $pricefijoupdate = 0 ;
                                  }
                                  
                                  $updatediscountDetail = "UPDATE inddiscounts SET fijo = '$pricefijoupdate' WHERE user_id = $user_id AND purchaseid = $purchaseid";
                                  $updatefijoDiscount = $pdo->prepare($updatediscountDetail);
                                  $updatefijoDiscount->execute();*/
                                  
                                } else if ($prodDiscount != 0 || $prodDiscount != '') {
                                  $prodDiscount = $prodDiscount .' '.'%';
                                  $price = $productfinal_price;
                                  $is_directprice = 'false';
                                  $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                  $final_pro_price = $price - $new_price_flower .' '.'€';
                                  
                                } else if ($catDiscount != 0 || $catDiscount != '') {
                                
                                  $prodDiscount = $catDiscount .' '.'%';
                                  $price = $productfinal_price;
                                  $is_directprice = 'false';
                                  $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                  $final_pro_price = $price - $new_price_flower .' '.'€';

                                } else if ($userDiscount  != 0) {
                                  $prodDiscount = $userDiscount .' '.'%';
                                  $price = $productfinal_price;
                                  $is_directprice = 'false';
                                  $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                  $final_pro_price = $price - $new_price_flower .' '.'€';
                                
                                } else if ($userData['usageType'] == '1') {
                                 
                                  if ($product['medDiscount'] > 0) {
                                    $prodDiscount = $product['medDiscount'] .' '.'%';
                                    $price = $productfinal_price;
                                    $is_directprice = 'false';
                                    $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                    $final_pro_price = $price - $new_price_flower .' '.'€';

                                  } else if ($medicalDiscountPercentage == 1) {

                                    $prodDiscount = $productfinal_price;
                                    $price = $productfinal_price * number_format($medicalDiscountCalc,2);
                                    $is_directprice = 'false';
                                    $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                    $final_pro_price = $price - $new_price_flower .' '.'€';

                                  } else {
                                    $prodDiscount = $productfinal_price;
                                    $price = $productfinal_price - number_format($medicalDiscount,2);
                                    $is_directprice = 'false';
                                    $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                    $final_pro_price = $price - $new_price_flower .' '.'€';

                                  }
                                } else {
                                  $prodDiscount = $productfinal_price;
                                  $price = $productfinal_price;
                                  $is_directprice = 'false';
                                  $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                  $final_pro_price = $price - $new_price_flower .' '.'€';
                                }
                                if($prodDiscount && $price){

                                    $new_arr['is_directprice'] = $is_directprice;     
                                    $new_arr['is_discount']    = "true";     
                                    $new_arr['discount_price'] = "true";
                                    $new_arr['user_discount']  =  $prodDiscount;
                                   // $new_arr['user_discount_price'] = abs($price - $prodDiscount) .' '.'€';   
                                         
                                    $new_arr['user_discount_price'] = $final_pro_price;   
                                }else{

                                    $new_arr['is_directprice'] = $is_directprice;
                                    $new_arr['is_discount'] = "false"; 
                                    $new_arr['discount_price'] = "false"; 
                                    $new_arr['user_discount'] = "0"; 
                                    $new_arr['user_discount_price'] = "0"; 
                                }

                                if(!empty($category['type']) == 0){
                                    $categorytype = 1; /*unit*/
                                }else{
                                    $categorytype = 0; /*gram*/
                                }
                              
                                $new_arr['category_id']   = $product['category'];
                                $new_arr['category_name'] = $category_name['name'];
                                $new_arr['category_type'] = $categorytype;
                                $new_arr['product_id']    = $product['productid'];
                                $new_arr['product_name']  = mb_convert_encoding($name, 'UTF-8', 'HTML-ENTITIES');

                                if(in_array($product['productid'],$userproarr)){
                                    $userproductprice = $product['productid'];
                                    $cartuserproduct = "SELECT * FROM cartmobile WHERE product_id = '$userproductprice' AND user_id = '$user_id'";
                                    $proresult = $pdo->prepare("$cartuserproduct");
                                    $proresult->execute();
                                    $userproductname = $proresult->fetch();
                                    $new_arr['add_cart'] =  '1';
                                    if($showprice == 1){
                                        $new_arr['sales_price'] = $userproductname['product_price'] .' '.'€';
                                    }else{
                                        $new_arr['sales_price'] = '';
                                    }
                                    
                                    $final_extra_multiple_price = $final_pro_price * $userproductname['extra_priceval'];
                                  //  $new_arr['extra_price'] = $userproductname['extra_price'] .' '.'€';  
                                    $new_arr['extra_price'] = $final_extra_multiple_price .' '.'€';  
                                    $new_arr['extra_price_count']   =  $userproductname['extra_priceval']; 
                                   
                                }else{
                                    $new_arr['extra_price']      ="0"; 
                                    $new_arr['extra_price_count'] = "0"; 
                                    
                                }

                                if(!in_array($product['productid'],$userproarr)){
                                        $userproductprice = $product['productid'];
                                        $new_arr['add_cart'] =  '0';
                                        if($showprice == 1){
                                            $new_arr['sales_price'] =  $product['salesPrice'] .' '.'€';
                                        }else{
                                             $new_arr['sales_price'] =  '';
                                        }
                                }else{
                                        /*$flowerarr['add_cart'] = "";
                                        $flowerarr['sales_price'] =  "";*/
                                }
                                $new_arr['realquantity']  = $product['realQuantity'];
                                $new_arr['product_image'] = $imagepath;
                                $new_arr['product_description']        = $product_descritpion;
                                $new_arr['product_medicaldescription'] = $product['medicaldescription'];
                                $new_arr['grow_type']         = $growtypeData['growtype'];
                                $new_arr['purchase_id']   = $product['purchaseid'];
                                $new_arr['breed2']   = $product['breed2'];
                                $new_arr['flower_type']   = $product['flowertype'];
                                $new_arr['percentageDisplay']   = $percentageDisplay;

                                $response['data'][] = $new_arr;
                            }
                        echo json_encode($response);
                    }else{
                        if($lang=='es')
                        {	
                            $response = array('flag' => '0', 'message' => 'Producto no encontrado.');
                        }else{
                            $response = array('flag' => '0', 'message' => 'Product Not Found.');
                        }
                        //$response = array('flag' => '0', 'message' => 'Product Not Found');
                        echo json_encode($response);
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
           echo json_encode($response);
        }
    }catch(PDOException $e){
            if($lang=='es')
            {	
                $response = array('flag' => '0', 'message' => 'Please add parameter in languageid.');
            }else{
                $response = array('flag' => '0', 'message' => 'Please add parameter in languageid.');
            }
            //$response = array('flag'=>'0', 'message' => 'Please add parameter in languageid.');
            echo json_encode($response);    
    }
