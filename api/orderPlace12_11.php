<?php 
    include('connectionM.php'); 
    include('stripepayment/init.php');
    try{

        if(!empty($_POST['language'])){
            $lang = $_POST['language'];
        }else{
            $lang = ""; 
        }

        if(!empty($_POST['order_request'])){
            $order_request = json_decode($_POST['order_request']);
        }else{
            $order_request = "";
        }


        if(!empty($_POST['macAddress'])){
            $macAddress = $_POST['macAddress'];
        }else{
            $macAddress = ""; 
        }

        if(!empty($lang == 'es') || !empty($lang == 'en') ){
            $user_id          = $order_request->user_id;
            $total_price      = $order_request->total_price;
            $user_credit      = $order_request->user_credit;
            $user_grand_total = $order_request->user_grand_total;
           /* $payment_mode     = $order_request->payment_mode;*/
            $usercredit_cal   = $order_request->userupdatecredit;
            $user_discount    = $order_request->user_discount;

            $userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit,maxCredit,photoExt,email FROM users WHERE user_id = '$user_id'";
            $result = $pdo->prepare($userDetails);
            $result->execute();
            $row = $result->fetch();
            $admintype = $row['userGroup'];

            if(!empty($row['email'])){
                $user_email = $row['email'];
            }else{
                $user_email = "";
            }

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


            if($order_request->user_grand_total == 0){
                $payment_mode = "credit";
                $transaction_id = "";
            }else{
                $payment_mode = "";
                $transaction_id  = $order_request->stripe_token;
            }

            /*get last order_id*/
            $lastorderDetail = "SELECT * FROM mobile_order ORDER BY order_id DESC LIMIT 1";
            $resultOrderData = $pdo->query($lastorderDetail);
            $reOrderData = $resultOrderData->fetch();

                if(!empty($reOrderData)){
                    $order_id =$reOrderData['order_id'];
                }else{
                    $order_id = '';
                }

                if(!empty($reOrderData['order_id'])){
                    $orderinc = $order_id + 1;
                }else{
                    $orderinc = 000001;
                }

                
            $site_logo = SITE_ROOT."/images/logo.png";
          
            /*send email in user order*/
            $message = '<html><body>';
            $message .= '<div class="col-md-12">';
            $message .= '<img src="'.$site_logo.'" height="100px" width="150px"><br />';
            $message .= '<p>Your Order has been Placed.</p>';
            $message .= '<div class="col-md-12"><p><storng>Dear</storng> , '. ucwords(@$first_name .' '. @$last_name).'</p></div>';
            $message .= '<p style="margin:0px 70px">Your order is on its way! Delivery 2 or 3 day Give Us in Our Company in cannabisclub.</p>';
            $message .= '<br />';
            $message .= '<label for="commentText" style="font-size:15px;"><b>Order Detail:</b></label><br />';
            $message .= '<table border="1" height="300" width="500">';
            $message .= '<thead style="background-color: #ddd;"> <tr><th> Order Number</th>';
            $message .= '<th>Category Name</th>';
            $message .= '<th>Product Name</th>';
            $message .= '<th>Product Image</th>';
            $message .= '<th>Product Price</th>';
            $message .= '<th>Product Extra Price</th>';
            $message .= '<th>Payment</th>';
            $message .= '</tr></thead>';


            foreach ($order_request->data as $data) {

                if(!empty($data->product_id)){
                    $product_id = $data->product_id;
                }else{
                    $product_id = "";
                }

                if(!empty($data->category_id)){
                    $categoryid  = $data->category_id;
                }else{
                    $categoryid = "";
                }

                if(!empty($data->category_name)){
                    $category_name  = $data->category_name;
                }else{
                    $category_name = "";
                }

                if(!empty($data->category_type)){
                    $category_type  = $data->category_type;
                }else{
                    $category_type = "";
                }

                if(!empty($data->product_name)){
                    $product_name  = $data->product_name;
                }else{
                    $product_name = "";
                }

                if(!empty($data->product_image)){
                    $product_image  = $data->product_image;
                }else{
                    $product_image = "";
                }

                if(!empty($data->product_description)){
                    $product_description  = $data->product_description;
                }else{
                    $product_description = "";
                }

                if(!empty($data->extra_price)){
                    $extra_price  = $data->extra_price;
                }else{
                    $extra_price = "";
                }

                if(!empty($data->extra_price_count)){
                    $extra_price_count  = $data->extra_price_count;
                }else{
                    $extra_price_count  = "";
                }

                if(!empty($data->user_discount)){
                    $user_discountOrignal = $data->user_discount;
                }else{
                    $user_discountOrignal = "";
                }

                if(!empty($data->user_discount_price)){
                    $user_discount_price  = $data->user_discount_price;
                }else{
                    $user_discount_price  = "";
                }
                
                /*Flower category wise product*/
                if($categoryid == 1){

                    $selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt FROM flower g, purchases p WHERE p.category = '$categoryid' AND p.productid = '$product_id' AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
                    $resultFlower = $pdo->prepare($selectFlower);
                    $resultFlower->execute();
                    $flower = $resultFlower->fetch();

                    /*image path*/ 
                    if(!empty($flower['purchaseid'] && $flower['photoExt'])){
                        $imagepath =$flower['purchaseid']. "." .$flower['photoExt']."";
                    }else{
                        $imagepath ='';
                    }

                    $imagepath1 = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/purchases/' . $flower['purchaseid'] . '.' .  $flower['photoExt'];
                    
                    /*product detail*/
                    $productid = $flower['productid'];
                    $productDetail = $pdo->query("SELECT description,medicaldescription FROM products WHERE productid = '$productid'");
                    $productData = $productDetail->fetch();

                    /*product medicaldescription*/
                    if(!empty($product['medicaldescription'])){
                        $product_medicaldescritpion = $productData['medicaldescription'];
                    }else{
                        $product_medicaldescritpion = '';
                    }

                    /*Grow type*/
                    $growtype = $flower['growType'];
                    $growtypeDetail = $pdo->query("SELECT growtype FROM growtypes WHERE growtypeid = '$growtype'");
                    $growtypeData = $growtypeDetail->fetch();

                    $product_price = $flower['salesPrice'];

                    if(!empty($flower['flowertype'])){
                        $flowertype = $flower['flowertype'];
                    }else{
                        $flowertype = "";
                    }

                    if(!empty($growtypeData['growtype'])){
                        $growtypeData  = $growtypeData['growtype'];
                    }else{
                        $growtypeData  = "";
                    }

                    if(!empty($flower['breed2'])){
                        $breed2  = $flower['breed2'];
                    }else{
                        $breed2  = "";
                    }
                }
                
                /*Extract category wise product*/
                if($categoryid == 2){
                    $selectExtract = "SELECT h.extractid, h.name, h.extract, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM extract h, purchases p WHERE p.category = $categoryid AND p.productid = $product_id AND p.productid = h.extractid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
                    $resultsExtract = $pdo->prepare("$selectExtract");
                    $resultsExtract->execute();
                    $extract = $resultsExtract->fetch();

                    /*image path*/ 
                    if(!empty($extract['purchaseid'] && $extract['photoExt'])){
                      $imagepath = $extract['purchaseid']. "." .$extract['photoExt']."";
                    }else{
                      $imagepath = "";
                    }

                    $imagepath1 = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/purchases/' . $extract['purchaseid'] . '.' .  $extract['photoExt'];

                    /*product detail*/
                    $productid = $extract['productid'];
                    $productDetail = $pdo->query("SELECT description,medicaldescription FROM products WHERE productid = '$productid'");
                    $productData = $productDetail->fetch();

                    /*product medicaldescription*/
                    if(!empty($product['medicaldescription'])){
                        $product_medicaldescritpion = $productData['medicaldescription'];
                    }else{
                        $product_medicaldescritpion = '';
                    }
                    $product_price = $extract['salesPrice'];

                    $growtypeData = "";
                    $flowertype = "";
                    $breed2 = "";
                }

                /*other category in get table data*/
                if($categoryid != 1 && $categoryid != 2){
                    $selectProduct = "SELECT pr.productid, pr.name, pr.description,pr.medicaldescription,p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt ,p.category FROM products pr, purchases p WHERE p.category = '$categoryid' AND p.productid = $product_id AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC";
                    $resultsproduct = $pdo->prepare($selectProduct);
                    $resultsproduct->execute();
                    $product = $resultsproduct->fetch();

                    /*image path*/
                    if(!empty($product['purchaseid'] && $product['photoExt'])){
                        $imagepath =$product['purchaseid']. "." .$product['photoExt']."";
                    }else{
                        $imagepath ="";
                    }

                    $imagepath1 = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/purchases/' . $product['purchaseid'] . '.' .  $product['photoExt'];

                    /*product medicaldescription*/
                    if(!empty($product['medicaldescription'])){
                        $product_medicaldescritpion = $product['medicaldescription'];
                    }else{
                        $product_medicaldescritpion = '';
                    }

                    $product_price = $product['salesPrice'];
                    $growtypeData = "";
                    $flowertype = "";
                    $breed2 = "";
                }

                /*print_r($imagepath1);*/

                /*insert data in oreder place*/
                $currentcreateddate = date('Y-m-d H:i:s');
                $currentupdateddate = date('Y-m-d H:i:s');

                try {
                     
                    if($order_request->user_grand_total != 0){

                        \Stripe\Stripe::setApiKey('sk_test_i3mD2pjn7h3RLL012LqCZqwA00izbuHoaN');

                        $customer = \Stripe\customer::create(array(
                            'email' => $user_email,
                            'source' => $transaction_id // tok_visa
                        ));

                        $charge = \Stripe\Charge::create(array(
                            'customer' => $customer->id,
                            'amount'   => (float)$user_grand_total * 100,
                            'currency' => "EUR",
                        )); 

                    $usertransctionid = $charge['id'];
                    $response = array('flag'=>'1', 'message' => 'payment success.','transaction_id' => $usertransctionid);
                    }

                    if(!empty($charge['id'])){
                        $usertransctionid = $charge['id'];
                    }else{
                        $usertransctionid = "";
                    }

                    $orderplaceInsert = "INSERT INTO mobile_order(user_id,order_id,payment_mode,total_price,user_credit,user_grand_total,product_id,category_id,category_name,category_type,product_name,product_description,product_medicaldescription,product_image,product_price,product_qty,flower_type,extra_priceval,extra_price,grow_type,breed2,user_discount,user_discountorignal,user_discount_price,payment_transction_id,created_at,updated_at) VALUES('$user_id','$orderinc','$payment_mode','$total_price','$user_credit','$user_grand_total','$product_id','$categoryid','$category_name','$category_type','$product_name','$product_description','$product_medicaldescritpion','$imagepath','$product_price','1','$flowertype','$extra_price','$extra_price_count','$growtypeData','$breed2','$user_discount','$user_discountOrignal','$user_discount_price','$usertransctionid','$currentcreateddate','$currentupdateddate')";
                    $resultInsertOrder = $pdo->prepare($orderplaceInsert);
                    $insertOrder = $resultInsertOrder->execute();
                  
                    if($insertOrder){
                    /*Delete addtocart user record*/
                    $deleteUserCartData = "DELETE FROM cartmobile WHERE user_id = '$user_id'";
                    $userCartData = $pdo->prepare($deleteUserCartData);
                    $deleteUsercart = $userCartData->execute();
                    }

                     /*select user credit update value in users table*/
                    $userCreditDetail = "SELECT * FROM users WHERE user_id = '$user_id'";
                    $selectUserData = $pdo->prepare($userCreditDetail);
                    $selectUserData->execute();
                    $usersCredit = $selectUserData->fetch();

                        if(!empty($usersCredit['credit'])){
                            $usercreditdata = $usersCredit['credit'] - $usercredit_cal;
                        }else{
                            $usercreditdata = "";
                        }
                        
                        if(!empty($usersCredit['first_name'])){
                            $first_name = $usersCredit['first_name'];
                        }else{
                            $first_name = "";
                        }


                        if(!empty($usersCredit['last_name'])){
                            $last_name = $usersCredit['last_name'];
                        }else{
                            $last_name = "";
                        }

                        if(!empty($usersCredit['email'])){
                            $user_email = $usersCredit['email'];
                        }else{
                            $user_email = ""; 
                        }

                    /*Add donation feild add in donation table*/    
                    $oldCredit = $usersCredit['credit'];
                    $newCredit = abs($usercreditdata);
                    $donationTime = date('Y-m-d H:i:s');
                    $user_name = $first_name .' '. $last_name;

                    if($order_request->user_grand_total == 0){
                        $donatedTo = 4;
                    }else{
                        $donatedTo = 2;
                    }


                    $DonationInsertOrder = "INSERT INTO donations(transaction_id,userid,donationTime,type,amount,creditBefore,creditAfter,donatedTo,operator) VALUES('$transaction_id','$user_id','$donationTime','2','$user_grand_total','$oldCredit','$newCredit','$donatedTo','$user_id')";
                    $resultDonationDetail = $pdo->prepare($DonationInsertOrder);
                    $resultDonationDetail->execute();

                    /*Add payment detail in table payment_table*/
                    $paymentInsertOrder = "INSERT INTO payment_mobile(transaction_id,user_id,user_name,user_lname,user_email,payment_mode,amount,order_date,payment_transction_id) VALUES('$transaction_id','$user_id','$first_name','$last_name','$user_email','$payment_mode','$user_grand_total','$donationTime','$usertransctionid')";
                    $resultPaymentDetail = $pdo->prepare($paymentInsertOrder);
                    $resultPaymentDetail->execute();

                    /*check club wise demain detail*/
                    $club_name = $_POST['club_name'];
                    $clubcreditDetails = "SELECT * FROM systemsettings WHERE domain = '$club_name'";
                    $resultclubcreditDetails = $pdo->prepare($clubcreditDetails);
                    $resultclubcreditDetails->execute();
                    $clubDetail = $resultclubcreditDetails->fetch();
                    $clubcredit = $clubDetail['creditOrDirect'];

                    if($clubcredit == 1){
                        /*update user credit update value in users table*/
                        $updateUserCredit = "UPDATE users SET credit = '$usercreditdata' WHERE user_id ='$user_id'";
                        $userCreditData = $pdo->prepare($updateUserCredit);
                        $updateUserCredit = $userCreditData->execute();
                    }

                    if($insertOrder){
                        /*get user order id*/
                        $userOrderDetail = $pdo->query("SELECT order_id FROM mobile_order WHERE user_id = '$user_id' ORDER BY order_id DESC LIMIT 1");
                        $userOrderData = $userOrderDetail->fetch();
                            if(!empty($userOrderData['order_id'])){
                                $userorder = $userOrderData['order_id'];
                            }else{
                                $userorder = "";
                            }
                        //print_r($imagepath1 ); exit;
                       $pro_image='<img src = "'.$imagepath1.'" height="50px" width="50px">';
                       
                        /*set email content*/
                        $message.='<tbody> <tr> <td>'.$userorder.'</td> <td>'.$category_name.'</td><td>'.$product_name.'</td><td>'.$pro_image.'</td><td>'.$product_price.'</td><td>'.$extra_price.'</td><td>'.$payment_mode.'</td>';
                        $message .= '</tr></tbody>';

                        if($order_request->user_grand_total == 0){
                        $response = array('flag' => '1', 'message' => 'Your order placed successfully.','Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice);
                        }else{
                            $response = array('flag' => '1', 'message' => 'Your order placed successfully.','transaction_id' => $usertransctionid,'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice);
                        }

                    }else{
                        $response = array('flag' => '0', 'message' => 'Order not placed,please try again');
                    }
             
                } catch(\Stripe\Error\Card $e) {

                    $response = array('flag'=>'0', 'message' => $e->getMessage());

                } catch (\Stripe\Error\RateLimit $e) {

                    $response = array('flag'=>'0', 'message' => $e->getMessage());

                } catch (\Stripe\Error\InvalidRequest $e) {

                    $response = array('flag'=>'0', 'message' => $e->getMessage());

                } catch (\Stripe\Error\Authentication $e) {

                    $response = array('flag'=>'0', 'message' => $e->getMessage());

                } catch (\Stripe\Error\ApiConnection $e) {

                    $response = array('flag'=>'0', 'message' => $e->getMessage());

                } catch (\Stripe\Error\Base $e) {

                    $response = array('flag'=>'0', 'message' => $e->getMessage());

                } catch (Exception $e) {
                    
                    $response = array('flag'=>'0', 'message' => $e->getMessage());
                }
            }

            $message .= '</table>';
            $message.='<p><storng>Total Price:</storng>'.$total_price . '<br>';
            $message.='<p><storng>Total Discount:</storng>'.$user_discount . '<br>';
            $message.='<storng>User Credit:</storng>'.$user_credit . '€<br>';
            $message.='<storng>User Grand Total:</storng>'.$user_grand_total .'€</p>';
            $message.='<p><storng>Regards,</storng>'.'<br>'.'Cannabisclub'.'</p>';
            $message .= '</div></body></html>';

            /*check multiple address sent user*/
            $userDetails = "SELECT * FROM users WHERE userGroup = 1";
            $resultusers = $pdo->prepare("$userDetails");
            $resultusers->execute();
            $useraddemail = array();
            while ($userDetail = $resultusers->fetch()) {
                $useraddemail[] = $userDetail['email'];
            }
                require_once('class.phpmailer.php');
                $mail = new PHPMailer();
                $mail->MailerDebug = 1;
                $mail->IsHTML(true);
                $mail->CharSet = 'UTF-8';
                    
                $mail->IsSMTP();
                //Enable SMTP debugging
                // 0 = off (for production use)
                // 1 = client messages
                // 2 = client and server messages
               // $mail->SMTPDebug  = 1;
                $mail->Debugoutput = 'html';
                //Set the hostname of the mail server
                $mail->Host       = 'mail.websiteserverhost.com';
                //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
                $mail->Port       = 587;
                //Set the encryption system to use - ssl (deprecated) or tls
                $mail->SMTPSecure = 'tls';
                //Whether to use SMTP authentication
                $mail->SMTPAuth   = true;
                //Username to use for SMTP authentication - use full email 
                //Username to use for SMTP authentication - use full email address for gmail
                $mail->Username   = "testingdevsj@websiteserverhost.com";
                //Password to use for SMTP authentication
                $mail->Password   = "UK-iM[(DP}AZ";
                $mail->SetFrom("testingdevsj@websiteserverhost.com");
                $mail->Subject    = "Order Place";
                $mail->Body       = $message;
               /* $mail->AddAddress($useremail);*/
                @$addresses = explode(',',$useraddemail);
                foreach ($useraddemail as $address) {
                    $mail->AddAddress($address);
                }
                $mail->AddAddress($user_email);
                $mail->send();
                  /*if(!$mail->send()) 
                { 
                echo "Mailer Error: " . $mail->ErrorInfo;
                } 
                else 
                { 
                echo "Booking To date mail has been sent successfully"; 
                }*/
            
            echo json_encode($response); 
        }

    }catch(PDOException $e){

            $response = array('flag'=>'0', 'message' => $e->getMessage());
            echo json_encode($response);    
    }


