<?php 
    include('connectionM.php'); 
    include('language/common.php'); 

    function getCatIcon($cat_id, $domain){
        global $pdo;
        $selectIcon = "SELECT icon FROM categories WHERE id=".$cat_id;
        $result = $pdo->prepare($selectIcon);
        $result->execute();
        $row = $result->fetch();
        if(trim($row['icon']) != ''){
            //$catimagepath =SITE_ROOT.'/api/image/'.$category['Icon'];
            $cat_png_icon = str_replace(".svg", ".png", $row['icon']);
            $catimagepath =SITE_ROOT."/images/_$domain/category/png/" .$cat_png_icon;
        }else{
            $catimagepath ="";
        } 
        return $catimagepath;
    }

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
        // set default value
        $topupcredit = 0;
        $preorder    = 0;
        $showprice   = 0;

        $userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit,maxCredit, photoExt FROM users WHERE user_id = '{$user_id}'";
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

        if(is_null($topupcredit)){
             $topupcredit = 0;
        }        
        if(is_null($preorder)){
             $preorder = 0;
        }        
        if(is_null($showprice)){
             $showprice = 0;
        }

        // show prices in app

        $showAppPrice = $clubSystem['showAppPrice'];

        if(!empty($lang == 'es') || !empty($lang == 'en')){
            $selectCats = "SELECT * from categories ORDER by sortorder ASC";
            $resultscat = $pdo->prepare("$selectCats");
            $resultscat->execute();

                if($resultscat->rowCount() > 0){

                     /*count for user product*/
                    $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
                    $result = $pdo->prepare("$cartCountData");
                    $result->execute();
                    $userCount = $result->rowCount();
                    $userproarr = array();
                    while($userflower = $result->fetch()){
                        $userproarr[] = $userflower['product_id'];
                    }

                    /*total of product count in store cart*/
                    $cartpriceCountData = "SELECT SUM(extra_price) AS total_price  FROM cartmobile WHERE user_id = '$user_id'";
                    $resultpricecount = $pdo->prepare("$cartpriceCountData");
                    $resultpricecount->execute();
                    $usertotalpriceData = $resultpricecount->fetch();
                    $userproducttotal = $usertotalpriceData['total_price'];
                 
                    $response['data'] = array();
                    $new_arr = array();
                    $flowerarr = array();
                    $flowermultiple = array();
                    
                    /* get notification count */
                    $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                    $resultcntdata = $pdo->prepare("$notificntdata");
                    $resultcntdata->execute();
                    $countnotfication = $resultcntdata->rowCount();

                        if(!empty($_POST['user_id'])){
                            if($lang == 'es'){
                                $response = array('flag' => '1','message' => 'Category Found Successfull','cart_count'=> $userCount,'total_price' => number_format($userproducttotal,'2').' '.'€','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice, 'show_app_price' => $showAppPrice, 'notification_count' => $countnotfication);
                            }else{
                                $response = array('flag' => '1','message' => 'Category Found Successfull','cart_count'=> $userCount,'total_price' => number_format($userproducttotal,'2').' '.'€','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice, 'show_app_price' => $showAppPrice, 'notification_count' => $countnotfication);
                            }   
                            //$response = array('flag' => '1','message' => 'Category Found Successfull','cart_count'=> $userCount,'total_price' => number_format($userproducttotal,'2').' '.'€','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice, 'show_app_price' => $showAppPrice, 'notification_count' => $countnotfication);
                        }else{
                            if($lang == 'es'){
                                $response = array('flag' => '1','message' => 'Category Found Successfull','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice, 'show_app_price' => $showAppPrice);
                            }else{
                                $response = array('flag' => '1','message' => 'Category Found Successfull','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice, 'show_app_price' => $showAppPrice);
                            }  
                            //$response = array('flag' => '1','message' => 'Category Found Successfull','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice, 'show_app_price' => $showAppPrice);
                        }

                        $selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt,p.medDiscount, g.description,g.medicaldescription FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
                        $resultFlower = $pdo->prepare("$selectFlower");
                        $resultFlower->execute();

                        if($resultFlower->rowCount() > 0){
                            while($flower = $resultFlower->fetch()){
                                /*image path*/
                                if(!empty($flower['purchaseid'] && $flower['photoExt'])){
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
                                    $percentageDisplay = '<br />(' . number_format($flower['sativaPercentage'],0) . '% s.)';
                                } else {
                                    $percentageDisplay = '';
                                }

                                $growtype = $flower['growType'];
                                $growtypeDetail = $pdo->query("SELECT growtype FROM growtypes WHERE growtypeid = '$growtype'");
                                $growtypeData = $growtypeDetail->fetch();

                                /*product detail*/
                                $productid = $flower['productid'];
                                $productDetail = $pdo->query("SELECT description,medicaldescription FROM products WHERE productid = '$productid'");
                                $productData = $productDetail->fetch();
                             //   echo "<pre>"; print_r($productData); exit;

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
                                 
                                  $prodDiscount = $prodFijo .' '.'€' ;
                                  $pricefijo = abs(number_format($prodFijo,2)) - abs($productfinal_price);
                                  $price = abs($pricefijo);
                                  $is_directprice = 'true';
                                //  $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
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
                                    $flowerarr['user_discount'] =  $prodDiscount ;
                                    $flowerarr['user_discount_price'] = $final_pro_price;   
                                    //$flowerarr['user_discount_pricew'] = $new_price_flower;   
                                }else{
                                    $flowerarr['is_directprice'] = $is_directprice;    
                                    $flowerarr['is_discount'] = "false"; 
                                    $flowerarr['discount_price'] = "false"; 
                                    $flowerarr['user_discount'] = "0"; 
                                    $flowerarr['user_discount_price'] = "0"; 
                                }

                                if(!empty($growtypeData['growtype'])){
                                    $growtypeData_Type = $growtypeData['growtype'];
                                }else{
                                    $growtypeData_Type = "";
                                }

                                $flowerarr['category_id']       = 1;
                                $flowerarr['category_name']     = 'Flowers';
                                $flowerarr['category_type']     = '2';
                                $flowerarr['product_id']        = $flower['productid'];
                                $flowerarr['product_name']      = $name;
                                $flowerarr['breed2']            = $flower['breed2'];
                                $flowerarr['flower_type']       = $flower['flowertype'];
                                $flowerarr['grow_type']         = $growtypeData_Type;

                                if(in_array($flower['productid'],$userproarr)){
                                    
                                    $userproductprice = $flower['productid'];
                                    $cartuserproduct = "SELECT * FROM cartmobile WHERE product_id = '$userproductprice' AND user_id = '$user_id'";
                                    $proresult = $pdo->prepare("$cartuserproduct");
                                    $proresult->execute();
                                    $userproductname = $proresult->fetch();
                                    $flowerarr['add_cart'] =  '1';
                                    $flowerarr['sales_price'] = $userproductname['product_price'] .' '.'€';

                                   /* $new_price_flower = number_format(($prodDiscount / 100) * $price,2);
                                         
                                    $final_extra_price = $price - $new_price_flower;*/
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
                                    $flowerarr['sales_price'] =  $flower['salesPrice'] .' '.'€';
                                }else{
                                    /*$flowerarr['add_cart'] = "";
                                    $flowerarr['sales_price'] =  "";*/
                                }
                                $flowerarr['realquantity']      = $flower['realQuantity'];
                                $flowerarr['product_image']     = $imagepath;
                                $flowerarr['product_description'] = $product_descritpion;
                                $flowerarr['product_medicaldescription'] = $product_medicaldescritpion;
                                $flowerarr['percentageDisplay'] = $percentageDisplay;
                                $flowerarr['purchase_id'] = $flower['purchaseid'];
                                //$response['data'][] = $flowerarr;
                                $flowermultiple[] = $flowerarr;

                            }
                        }
                        $flower_count = $resultFlower->rowCount();
                        $flower_path = getCatIcon(1, $domain);
                        // get category icons 
                        /*static array with flower and extract*/
                        $staticarrayflower =array(
                            'id' => '1',
                            'categoryname' => 'Flowers',
                            'categorytype' => '1',
                            //'icon' =>  SITE_ROOT."/api/image/flower.png",
                            'icon' =>  $flower_path,
                          //  'iconselected' =>  SITE_ROOT."/api/image/flowerselected.png",
                           /* 'iconselected' =>  SITE_ROOT."/images/_$domain/category_sec/flowerselected.png",*/
                            'product_count' => $resultFlower->rowCount(),
                           /* 'categoryproduct' => $flowermultiple,*/
                            );
                         
                        /*get count data*/
                        $selectExtract = "SELECT h.extractid, h.name, h.extract, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt,p.medDiscount FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
                        $resultsExtract = $pdo->prepare("$selectExtract");
                        $resultsExtract->execute();
                        $extract_path = getCatIcon(2, $domain);
                        $staticarrayextract =array(
                            'id' => '2',
                            'categoryname' => 'Extract',
                            'categorytype' => '1',
                           // 'icon' => SITE_ROOT."/api/image/extracts.png",
                            'icon' =>  $extract_path,
                            /*'iconselected' =>  SITE_ROOT."/images/_$domain/category_sec/extractsselected.png",*/
                            'product_count' => $resultsExtract->rowCount(),
                            );
                        $extract_count = $resultsExtract->rowCount();
                       if($flower_count > 0){ 
                           $response['data'][] = $staticarrayflower;
                        }
                        if($extract_count > 0){
                            $response['data'][] = $staticarrayextract;
                        }

                        while($category = $resultscat->fetch()){
                            
                            if(!empty($category['type']) == 1){
                                $categorytype = 0; /*unit*/
                            }else{
                                $categorytype = 1; /*gram*/
                            }

                             /*image path*/
                            if(trim($category['icon']) != ''){
                                //$catimagepath =SITE_ROOT.'/api/image/'.$category['Icon'];
                                $cat_png_icon = str_replace(".svg", ".png", $category['icon']);
                                $catimagepath =SITE_ROOT."/images/_$domain/category/png/" .$cat_png_icon;
                            }else{
                                $catimagepath ="";
                            } 
                            /*selcted image path*/
/*                            if(!empty($category['cat_selected_icon'])){
                                $selctedcatimagepath =SITE_ROOT."/images/_$domain/category_sec/" .$category['cat_selected_icon'];
                            }else{
                                $selctedcatimagepath ="";
                            }*/

                            $catid = $category['id'];
                            /*get count data in product*/
                            if($catid != 1 && $catid !=2){
                                $selectProduct = "SELECT pr.productid, pr.name, pr.description,pr.medicaldescription,p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt ,p.category,p.medDiscount FROM products pr, purchases p WHERE p.category = $catid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC";
                                $resultsproduct = $pdo->prepare("$selectProduct");
                                $resultsproduct->execute();
                                $product_count =   $resultsproduct->rowCount();
                                if($product_count > 0){
                                    $new_arr['id'] = $category['id'];
                                    $new_arr['categoryname'] = $category['name'];
                                    $new_arr['categorytype'] = $categorytype;
                                   /* $new_arr['categorytype'] =  $category['type'];*/
                                    $new_arr['icon']         = $catimagepath;
                                   /* $new_arr['iconselected'] = $selctedcatimagepath;*/
                                    $new_arr['product_count'] = $resultsproduct->rowCount();
                                    $response['data'][] = $new_arr;
                                }
                            }
                        }

                }else{
                    if($lang == 'es'){
                        $response = array('flag' => '0', 'message' => 'Category Not Found');
                    }else{
                        $response = array('flag' => '0', 'message' => 'Category Not Found');
                    }
                    //$response = array('flag' => '0', 'message' => 'Category Not Found');
                }
            echo json_encode($response);
        }else{
            if($lang == 'es'){
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




        

            