<?php 
    include('connectionM.php');

    try{
        if(!empty($_POST['language'])){
            $lang = $_POST['language'];
        }else{
            $lang = ""; 
        }
        
        if(!empty($lang == 'es') || !empty($lang == 'en') || !empty($_POST['user_id']) && !empty($_POST['product_id']) ||!empty($_POST['category_type']) && !empty($_POST['extra_price'])){
             
            $user_id       = $_POST['user_id'];
            $product_id    = $_POST['product_id'];
            $categoryid    = $_POST['category_id'];
            $category_type = $_POST['category_type'];
            $extra_price   = $_POST['extra_price'];

            /*Flower category wise product*/
            if($categoryid  == 1){
             
                  $selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt,p.medDiscount,g.description,g.medicaldescription FROM flower g, purchases p WHERE p.category = $categoryid AND p.productid = $product_id AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
                  $resultFlower = $pdo->prepare("$selectFlower");
                  $resultFlower->execute();
                  $flower = $resultFlower->fetch();
                  $purchaseid = $flower['purchaseid'];
                  $medDiscount = $flower['medDiscount'];
                  $productfinal_price = number_format($_POST['extra_price'] * $flower['salesPrice'],2);

                        /*image path*/ 
                        if(!empty($flower['purchaseid'] && $flower['photoExt'])){
                          $imagepath =$flower['purchaseid']. "." .$flower['photoExt']."";
                        }else{
                          $imagepath ='';
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

                        /*Grow type*/
                        $growtype = $flower['growType'];
                        $growtypeDetail = $pdo->query("SELECT growtype FROM growtypes WHERE growtypeid = '$growtype'");
                        $growtypeData = $growtypeDetail->fetch();

                        /*product detail*/
                        $productid = $flower['productid'];
                        $productDetail = $pdo->query("SELECT description,medicaldescription FROM products WHERE productid = '$productid'");
                        $productData = $productDetail->fetch();

                        if(!empty($productData['description'])){
                          $product_descritpion = $productData['description'];
                        }else{
                          $product_descritpion = '';
                        }

                        if(!empty($productData['medicaldescription'])){
                          $product_medicaldescritpion = $productData['medicaldescription'];
                        }else{
                          $product_medicaldescritpion = '';
                        }

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
                         
                          $prodDiscount = $prodFijo;
                          $pricefijo = abs(number_format($prodFijo,2)) - abs($productfinal_price);
                          $price = abs($pricefijo);
                          $originalDiscount = 0;
                          $is_directprice = 'true';

                          /*if($productfinal_price < $pricefijo){
                            $pricefijoupdate = abs(number_format($prodFijo,2)) - abs($productfinal_price);
                          }else{
                            $pricefijoupdate = 0 ;
                          }
                          
                          $updatediscountDetail = "UPDATE inddiscounts SET fijo = '$pricefijoupdate' WHERE user_id = $user_id AND purchaseid = $purchaseid";
                          $updatefijoDiscount = $pdo->prepare($updatediscountDetail);
                          $updatefijoDiscount->execute();*/
                          
                        } else if ($prodDiscount != 0 || $prodDiscount != '') {
                         
                          $prodDiscount = ($prodDiscount * $productfinal_price) / 100;
                          $price = $productfinal_price;
                          $originalDiscount = $prodDiscount;
                          $is_directprice = 'false';
                          
                        } else if ($catDiscount != 0 || $catDiscount != '') {
                        
                          $prodDiscount = ($catDiscount * $productfinal_price) / 100;
                          $price = $productfinal_price;
                          $originalDiscount = $catDiscount;
                          $is_directprice = 'false';

                        } else if ($userDiscount  != 0) {
                          $prodDiscount = ($userDiscount * $productfinal_price) / 100;
                          $price = $productfinal_price;
                          $originalDiscount = $userDiscount;
                          $is_directprice = 'false';
                        
                        } else if ($userData['usageType'] == '1') {
                         
                          if ($flower['medDiscount'] > 0) {
                            $prodDiscount = ($flower['medDiscount'] * $productfinal_price) / 100;
                            $price = $productfinal_price;
                            $originalDiscount = $flower['medDiscount'];
                            $is_directprice = 'false';

                          } else if ($medicalDiscountPercentage == 1) {
                            $prodDiscount = 0;
                            $price = $productfinal_price * number_format($medicalDiscountCalc,2);
                            $originalDiscount = 0;
                            $is_directprice = 'false';

                          } else {
                            $prodDiscount = 0;
                            $price = $productfinal_price - number_format($medicalDiscount,2);
                            $originalDiscount =0;
                            $is_directprice = 'false';
                          }

                        } else {
                          $prodDiscount = 0;
                          $originalDiscount = 0;
                          $price = $productfinal_price;
                          $is_directprice = 'false';
                        }

                        /*insert time get data for product ,flower, grow type*/
                        $category_name                 = 'Flowers';
                        $product_name                   = $name;
                        $breed2                         = $flower['breed2'];
                        $flowertype                     = $flower['flowertype'];
                        $growtypeData                   = $growtypeData['growtype'];
                        $product_price                  = $flower['salesPrice'];
                        $extrapricecount                = $price;
                        $product_qty                    = 1;
                        $product_image                  = $imagepath;
                        $product_description            = $product_descritpion;
                        $product_medicaldescription     = $product_medicaldescritpion;

                        $numberAsString = number_format($extrapricecount, 2);

                        /*product detail*/
                        $checkProduct = $pdo->query("SELECT * FROM cartmobile WHERE product_id = '$product_id'");
                        $checkProduct = $checkProduct->fetch();

                        if(!empty($checkProduct['product_id'] == $product_id && $checkProduct['user_id'] == $user_id)){
                          if($lang == 'es'){
                            $response = array('flag'=>'0', 'message' => "¡Ya has añadido este producto!");
                          }else{
                            $response = array('flag'=>'0', 'message' => "You've already added this product!");
                          }
                          //$response = array('flag' => '0', 'message' => 'Product already added.');
                            //echo json_encode($response);  
                        }else{
                            /*count for user product*/
                            $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
                            $result = $pdo->prepare("$cartCountData");
                            $result->execute();
                            $userCount = $result->rowCount();

                            /*insert data in query*/     
                            $cartQuery = "INSERT INTO cartmobile(user_id,product_id,purchase_id,medDiscount,product_name,product_description,product_medicaldescription,product_image,product_price,product_qty,flower_type,category_type,category_name,category_id ,extra_priceval,extra_price,grow_type,breed2,procart_cnt,cart_discount,originaldiscount_value,is_driectprice,created_at,updated_at) VALUES('$user_id','$product_id','$purchaseid','$medDiscount','$product_name','$product_description','$product_medicaldescription','$product_image','$product_price','$product_qty','$flowertype','$category_type','$category_name','$categoryid','$extra_price','$extrapricecount','$growtypeData','$breed2',1,'$prodDiscount','$originalDiscount','$is_directprice','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."')";

                            $stmt= $pdo->prepare($cartQuery);
                            $cartinsert = $stmt->execute();
                            $lastcartmobile_id = $pdo->lastInsertId();

                            $usersum = $userCount + 1;

                                
                            /*update undo category id*/
                            $cartUpdate_Catundo = "UPDATE cartmobile SET `cat_undo_num` = '$usersum' WHERE `id` = '$lastcartmobile_id'";
                            $cartUpdateData = $pdo->prepare($cartUpdate_Catundo);
                            $catCat_undoUpdate = $cartUpdateData->execute();


                            /* get notification count */
                            $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                            $resultcntdata = $pdo->prepare("$notificntdata");
                            $resultcntdata->execute();
                            $countnotfication = $resultcntdata->rowCount();


                            if($cartinsert){
                              if($lang == 'es'){
                                $response = array('flag' => '1', 'message' => 'Producto añadido al carrito con éxito.','cart_count'=> $usersum,'notification_count' => $countnotfication);
                              }else{
                                $response = array('flag' => '1', 'message' => 'Product added to cart successfully.','cart_count'=> $usersum,'notification_count' => $countnotfication);
                              }
                              //$response = array('flag' => '1', 'message' => 'Add to cart in product successfully.','cart_count'=> $usersum,'notification_count' => $countnotfication);
                            }else{
                              if($lang == 'es'){
                                $response = array('flag' => '0', 'message' => 'Algo ha ido mal, por favor inténtelo de nuevo.');
                              }else{
                                $response = array('flag' => '0', 'message' => 'Something went wrong, please try again.');
                              }
                              //$response = array('flag' => '0', 'message' => 'Product not add to cart,please try again');
                            }
                        }

                       echo json_encode($response);
            }

            /*Extract category wise product*/
            if($categoryid  == 2){
                $selectExtract = "SELECT h.extractid, h.name, h.extract, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt,p.medDiscount,h.description,h.medicaldescription FROM extract h, purchases p WHERE p.category = $categoryid AND p.productid = $product_id AND p.productid = h.extractid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
                $resultsExtract = $pdo->prepare("$selectExtract");
                $resultsExtract->execute();
                $extract = $resultsExtract->fetch();
                $purchaseid = $extract['purchaseid'];
                $medDiscount = $extract['medDiscount'];
                $productfinal_price = number_format($_POST['extra_price'] * $extract['salesPrice'],2);

                    if(!empty($extract['purchaseid'] && $extract['photoExt'])){
                      $imagepath = $extract['purchaseid']. "." .$extract['photoExt']."";
                    }else{
                      $imagepath = "";
                    }

                    /*product detail*/
                    $productid = $extract['productid'];
                    $productDetail = $pdo->query("SELECT description,medicaldescription FROM products WHERE productid = '$productid'");
                    $productData = $productDetail->fetch();

                    if(!empty($productData['description'])){
                      $product_descritpion = $productData['description'];
                    }else{
                      $product_descritpion = '';
                    }

                    if(!empty($productData['medicaldescription'])){
                      $product_medicaldescritpion = $productData['medicaldescription'];
                    }else{
                      $product_medicaldescritpion = '';
                    }

                    // All Discount
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
                    $prodDiscount = $rowD['discount'];
                    $prodFijo = $rowD['fijo'];

                    $userDetails = "SELECT usageType,discount FROM users WHERE user_id = '$user_id'";
                    $resultuserDetails = $pdo->prepare("$userDetails");
                    $resultuserDetails->execute();
                    $userData = $resultuserDetails->fetch();
                    $userDiscount = $userData['discount'];
                    
                    if ($prodFijo != 0 || $prodFijo != '') {
                      $prodDiscount = $prodFijo;
                      $originalDiscount = 0;
                      $pricefijo = abs(number_format($prodFijo,2)) - abs($productfinal_price);
                      $price = abs($pricefijo);
                      $is_directprice = 'true';

                      if($productfinal_price < $pricefijo){

                        $pricefijoupdate = abs(number_format($prodFijo,2)) - abs($productfinal_price);
                      }else{
                        $pricefijoupdate = 0 ;
                      }
                      
                     /* $updatediscountDetail = "UPDATE inddiscounts SET fijo = '$pricefijoupdate' WHERE user_id = '$user_id' AND purchaseid = '$purchaseid'";
                      $updatefijoDiscount = $pdo->prepare($updatediscountDetail);
                      $updatefijoDiscount->execute();*/
                      
                    } else if ($prodDiscount != 0 || $prodDiscount != '') {
                      $prodDiscount = ($prodDiscount * $productfinal_price) / 100;
                      $price = $productfinal_price;
                      $originalDiscount = $prodDiscount;
                      $is_directprice = 'false';
                      
                    } else if ($catDiscount != 0 || $catDiscount != '') {
                      $prodDiscount = ($catDiscount * $productfinal_price) / 100;
                      $price = $productfinal_price;
                      $originalDiscount = $catDiscount;

                    } else if ($userDiscount != 0) {
                      $prodDiscount = ($userDiscount * $productfinal_price) / 100;
                      $price = $productfinal_price;
                      $originalDiscount = $userDiscount;
                      $is_directprice = 'false';
                      
                    } else if ($userData['usageType'] == '1') {
                      if ($extract['medDiscount'] > 0) {
                        $prodDiscount = ($extract['medDiscount'] * $productfinal_price) / 100;
                        $price = $productfinal_price;
                        $originalDiscount = $extract['medDiscount'];
                        $is_directprice = 'false';
                      } else if ($medicalDiscountPercentage == 1) {
                        $prodDiscount = 0;
                        $originalDiscount = 0;
                        $price = $productfinal_price * number_format($medicalDiscountCalc,2);
                        $is_directprice = 'false';
                      } else {
                        $prodDiscount = 0;
                        $originalDiscount = 0;
                        $price = $productfinal_price - number_format($medicalDiscount,2);
                        $is_directprice = 'false';
                      }
                    } else {
                      $prodDiscount = 0;
                      $originalDiscount = 0;
                      $price = $productfinal_price;
                      $is_directprice = 'false';
                    }

                    $category_name                   = 'Extract';
                    $product_name                    = $extract['name'];
                    $product_price                   = $extract['salesPrice'];
                    $extrapricecount                 = $price;
                    $product_qty                     = 1;
                    $product_image                   = $imagepath;
                    $product_description             = $product_descritpion;
                    $product_medicaldescription      = $product_medicaldescritpion;
                    $numberAsString = number_format($extrapricecount, 2);


                  /*product detail*/
                    $checkProduct = $pdo->query("SELECT * FROM cartmobile WHERE product_id = '$product_id'");
                    $checkProduct = $checkProduct->fetch();

                    if(!empty($checkProduct['product_id'] == $product_id && $checkProduct['user_id'] == $user_id)){

                          if($lang == 'es'){
                            $response = array('flag' => '0', 'message' => '¡Ya has añadido este producto!');
                          }else{
                            $response = array('flag' => '0', 'message' => 'You have already added this product!');
                          }
                          //$response = array('flag' => '0', 'message' => 'Product already added.');
                        //echo json_encode($response);    

                    }else{

                          /*count for user product*/
                          $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
                          $result = $pdo->prepare("$cartCountData");
                          $result->execute();
                          $userCount = $result->rowCount();
                          $usersum = $userCount + 1;

                          /*insert data in query*/     
                          $cartQuery = "INSERT INTO cartmobile(user_id,product_id,purchase_id,medDiscount,product_name,product_description,product_medicaldescription,product_image,product_price,product_qty,category_type,category_name,category_id,extra_priceval,extra_price,procart_cnt,cart_discount,originaldiscount_value,is_driectprice,created_at,updated_at) VALUES('$user_id','$product_id','$purchaseid','$medDiscount','$product_name','$product_description','$product_medicaldescription','$product_image','$product_price','$product_qty','$category_type','$category_name','$categoryid',$extra_price,'$extrapricecount',1,'$prodDiscount','$originalDiscount','$is_directprice','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."')";

                          $stmt= $pdo->prepare($cartQuery);
                          $cartinsert = $stmt->execute();
                          $lastcartmobile_id = $pdo->lastInsertId();


                          /* get notification count */
                          $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                          $resultcntdata = $pdo->prepare("$notificntdata");
                          $resultcntdata->execute();
                          $countnotfication = $resultcntdata->rowCount();


                          /*update undo category id*/        
                          $cartUpdate_Catundo = "UPDATE cartmobile SET `cat_undo_num` = '$usersum' WHERE `id` = '$lastcartmobile_id'";
                          $cartUpdateData = $pdo->prepare($cartUpdate_Catundo);
                          $catCat_undoUpdate = $cartUpdateData->execute();

                          if($cartinsert){
                            if($lang == 'es'){
                              $response = array('flag' => '1', 'message' => 'Producto añadido al carrito con éxito.','cart_count'=> $usersum,'notification_count' => $countnotfication);
                            }else{
                              $response = array('flag' => '1', 'message' => 'Product added to cart successfully.','cart_count'=> $usersum,'notification_count' => $countnotfication);
                            }
                            //$response = array('flag' => '1', 'message' => 'Add to cart in product successfully.','cart_count'=> $usersum,'notification_count' => $countnotfication);
                          }else{
                            if($lang == 'es'){
                              $response = array('flag' => '0', 'message' => 'Algo ha ido mal, por favor inténtelo de nuevo.');
                            }else{
                              $response = array('flag' => '0', 'message' => 'Something went wrong, please try again.');
                            }
                            //$response = array('flag' => '0', 'message' => 'Product not add to cart,please try again');
                          }
                    }
                      echo json_encode($response);
            }
            
            /*other category in get table data*/
            if($categoryid != 1 && $categoryid != 2){

                $selectProduct = "SELECT pr.productid, pr.name, pr.description,pr.medicaldescription,p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt ,p.category,p.medDiscount FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = $product_id AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC";
                $resultsproduct = $pdo->prepare("$selectProduct");
                $resultsproduct->execute();
                $product = $resultsproduct->fetch();
                $purchaseid = $product['purchaseid'];
                $medDiscount = $product['medDiscount'];
                $productfinal_price = number_format($_POST['extra_price'] * $product['salesPrice'],2);

                  /*image path*//*update undo category id*/        
                  $cartUpdate_Catundo = "UPDATE cartmobile SET `cat_undo_num` = '$usersum' WHERE `id` = '$lastcartmobile_id'";
                  $cartUpdateData = $pdo->prepare($cartUpdate_Catundo);
                  $catCat_undoUpdate = $cartUpdateData->execute();
                  if(!empty($product['purchaseid'] && $product['photoExt'])){
                    $imagepath =$product['purchaseid']. "." .$product['photoExt']."";
                  }else{
                    $imagepath ="";
                  }

                  /*category detail*/
                  $category_id = $product['category'];
                  $categoryDetail = $pdo->query("SELECT * from categories WHERE id = '$category_id'");
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

                  // All Discount category 2
                  $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE user_id = '$user_id' AND categoryid = '$categoryid'";
                    $result = $pdo->prepare($selectCategoryDiscount);
                    $result->execute();
                  $rowCD = $result->fetch();
                  $catDiscount = $rowCD['discount'];

                  $selectSettings = "SELECT highRollerWeekly, minAge, closingMail, dispensaryGift, barGift, menuType, medicalDiscount, logouttime, logoutredir, dispDonate, dispExpired, dispenseLimit, showAge, showGender, keepNumber, membershipFees, medicalDiscountPercentage, bankPayments, creditOrDirect, visitRegistration, cropOrNot, puestosOrNot, openAndClose, barMenuType, flowerLimit, extractLimit,   realWeight, showStock, showOrigPrice, checkoutDiscount, consumptionMin, consumptionMax, showStockBar, showOrigPriceBar, barTouchscreen, iPadReaders, cashdro, creditchange, expirychange, exentoset, menusortdisp, menusortbar, dispsig, barsig, openmenu, keypads, moneycount, customws, negcredit, language, nobar, sigtablet, entrysys, entrysysstay, entrysyssecs, dooropener, cuotaincrement, checkoutDiscountBar, chipcost, fingerprint, pagination, dooropenfor, workertracking FROM systemsettings";
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
                    $prodDiscount = $prodFijo;
                    $originalDiscount = 0;
                    $pricefijo = abs(number_format($prodFijo,2)) - abs($productfinal_price);
                    $price = abs($pricefijo);
                    $is_directprice = 'true';

                    if($productfinal_price < $pricefijo){
                      $pricefijoupdate = abs(number_format($prodFijo,2)) - abs($productfinal_price);
                    }else{
                      $pricefijoupdate = 0 ;
                    }
                    
                    /*$updatediscountDetail = "UPDATE inddiscounts SET fijo = '$pricefijoupdate' WHERE user_id = $user_id AND purchaseid = $purchaseid";
                    $updatefijoDiscount = $pdo->prepare($updatediscountDetail);
                    $updatefijoDiscount->execute();*/
                    
                  } else if ($prodDiscount != 0 || $prodDiscount != '') {
                    $prodDiscount = ($prodDiscount * $productfinal_price) / 100;
                    $price = $productfinal_price;
                    $originalDiscount = $prodDiscount;
                    $is_directprice = 'false';
                    
                  } else if ($catDiscount != 0 || $catDiscount != '') {
                    $prodDiscount = ($catDiscount * $productfinal_price) / 100;
                    $price = $productfinal_price;
                    $originalDiscount = $catDiscount;
                    $is_directprice = 'false';

                  } else if ($userDiscount != 0) {
                    $prodDiscount = ($userDiscount * $productfinal_price) / 100;
                    $price = $productfinal_price;
                    $originalDiscount = $userDiscount;
                    $is_directprice = 'false';
                    
                  } else if ($userData['usageType'] == '1') {

                    if ($product['medDiscount'] > 0) {
                      $prodDiscount = ($product['medDiscount'] * $productfinal_price) / 100;
                      $price = $productfinal_price;
                      $originalDiscount = $product['medDiscount'];
                      $is_directprice = 'false';

                    } else if ($medicalDiscountPercentage == 1) {
                      $prodDiscount = 0;
                      $originalDiscount = 0;
                      $price = $productfinal_price * number_format($medicalDiscountCalc,2);
                      $is_directprice = 'false';

                    } else {
                      $prodDiscount = 0;
                      $originalDiscount = 0;
                      $price = $productfinal_price - number_format($medicalDiscount,2);
                      $is_directprice = 'false';
                    }
                  } else {
                    $prodDiscount = 0;
                    $originalDiscount = 0;
                    $price = $productfinal_price;
                    $is_directprice = 'false';
                  }
                  $category_name              = $category_name['name'];
                  $product_name               = $product['name'];
                  $product_price              = $product['salesPrice'];
                  $extrapricecount            = $price;
                  $product_qty                = 1;
                  $product_image              = $imagepath;
                  $product_description        = $product_descritpion;
                  $product_medicaldescription = $product_medicaldescritpion;
                  $numberAsString = number_format($extrapricecount, 2);
                   

                /*product detail*/
                  $checkProduct = $pdo->query("SELECT * FROM cartmobile WHERE product_id = '$product_id'");
                  $checkProduct = $checkProduct->fetch();

                  if(!empty($checkProduct['product_id'] == $product_id && $checkProduct['user_id'] == $user_id)){
                    if($lang == 'es'){
                      $response = array('flag' => '0', 'message' => '¡Ya has añadido este producto!');
                    }else{
                      $response = array('flag' => '0', 'message' => 'You have already added this product!');
                    }
                    //$response = array('flag' => '0', 'message' => 'Product already added.');
                      //echo json_encode($response);    

                  }else{
                        /*count for user product*/
                        $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
                        $result = $pdo->prepare($cartCountData);
                        $result->execute();
                        $userCount = $result->rowCount();
                        $usersum = $userCount + 1;

                        /*insert data in query*/     
                        $cartQuery = "INSERT INTO cartmobile(user_id,product_id,purchase_id,medDiscount,product_name,product_description,product_medicaldescription,product_image,product_price,product_qty,category_type,category_name,category_id,extra_priceval,extra_price,cart_discount,originaldiscount_value,procart_cnt,is_driectprice,created_at,updated_at) VALUES('$user_id','$product_id','$purchaseid','$medDiscount','$product_name','$product_description','$product_medicaldescription','$product_image','$product_price','$product_qty','$category_type','$category_name','$categoryid','$extra_price','$extrapricecount','$prodDiscount','$originalDiscount',1,'$is_directprice','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."')";
                        
                          $stmt= $pdo->prepare($cartQuery);
                          $cartinsert = $stmt->execute();
                          $lastcartmobile_id = $pdo->lastInsertId();
                         
                          /* get notification count */
                          $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                          $resultcntdata = $pdo->prepare("$notificntdata");
                          $resultcntdata->execute();
                          $countnotfication = $resultcntdata->rowCount();
     
                          /*update undo category id*/        
                          $cartUpdate_Catundo = "UPDATE cartmobile SET `cat_undo_num` = '$usersum' WHERE `id` = '$lastcartmobile_id'";
                          $cartUpdateData = $pdo->prepare($cartUpdate_Catundo);
                          $catCat_undoUpdate = $cartUpdateData->execute();

                          if($cartinsert){
                            if($lang == 'es'){
                              $response = array('flag' => '1', 'message' => 'Producto añadido al carrito con éxito.','cart_count'=> $usersum,'notification_count' => $countnotfication);
                            }else{
                              $response = array('flag' => '1', 'message' => 'Product added to cart successfully.','cart_count'=> $usersum,'notification_count' => $countnotfication);
                            }
                            //$response = array('flag' => '1', 'message' => 'Add to cart in product successfully.','cart_count'=> $usersum,'notification_count' => $countnotfication);
                          }else{
                            if($lang == 'es'){
                              $response = array('flag' => '0', 'message' => 'Algo ha ido mal, por favor inténtelo de nuevo.');
                            }else{
                              $response = array('flag' => '0', 'message' => 'Something went wrong, please try again.');
                            }
                            //$response = array('flag' => '0', 'message' => 'Product not add to cart,please try again');
                          }
                  }
                  echo json_encode($response);
            }

        }else{
          if($lang == 'es'){
            $response = array('flag' => '0', 'message' => 'Todos los campos son obligatorios.');
          }else{
            $response = array('flag' => '0', 'message' => 'All fields are mandatory.');
          }
          //$response = array('flag' => '0', 'message' => 'Please add parameter all parameter.');
            echo json_encode($response);
        }

    }catch(PDOException $e){

      $response = array('flag'=>'0', 'message' => $e->getMessage());
      echo json_encode($response);
    }
