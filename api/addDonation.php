<?php
    include('connectionM.php');
    include('stripepayment/init.php');

    try{

        if(!empty($_POST['language'])){
            $lang = $_POST['language'];
        }else{
            $lang = ""; 
        }
        
        if(!empty($_POST['donation_request'])){
            $donation_request = json_decode($_POST['donation_request']);
        }else{
            $donation_request = "";
        }

        if($donation_request->stripe_token){
            $transaction_id = $donation_request->stripe_token;
        }else{
            $transaction_id = "";
        }

        if($donation_request->donation_amount){
            $amount = $donation_request->donation_amount;
        }else{
            $amount = "";
        }

        if($donation_request->comment){
            $comment = $donation_request->comment;
        }else{
            $comment = "";
        }

        if($donation_request->user_id){
            $user_id = $donation_request->user_id;
        }else{
            $user_id = "";
        }
       
        $donationTime = date('Y-m-d H:i:s');

        if($lang == 'es' || $lang == 'en'){ 
            
            /*select check user credit*/
            $checkUserCredit = "SELECT * FROM users WHERE user_id = '$user_id'";
            $resultCheckCredit = $pdo->prepare($checkUserCredit);
            $resultCheckCredit->execute();
            $userCreditData = $resultCheckCredit->fetch();

            $oldCredit = $userCreditData['credit'];
            $newCredit = $amount + $oldCredit;

            if(!empty($userCreditData['email'])){
                $user_email = $userCreditData['email'];
            }else{
                $user_email = ""; 
            }

            if(!empty($_POST['macAddress'])){
                $macAddress = $_POST['macAddress'];
            }else{
                $macAddress = ""; 
            }

            $userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit,maxCredit, photoExt FROM users WHERE user_id = '$user_id'";
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

            try {


                \Stripe\Stripe::setApiKey('sk_test_i3mD2pjn7h3RLL012LqCZqwA00izbuHoaN');

                $customer = \Stripe\customer::create(array(
                    'email' => $user_email,
                    'source' => $transaction_id // tok_visa
                ));

                $charge = \Stripe\Charge::create(array(
                    'customer' => $customer->id,
                    'amount'   => $amount * 100,
                    'currency' => "EUR",
                )); 

               // print_r($customer);
                $usertransctionid = $charge['id'];
                if($lang == 'es'){
                    $response = array('flag'=>'1', 'message' => '¡Pago recibido correctamente!','transaction_id' => $usertransctionid);
                }else{
                    $response = array('flag'=>'1', 'message' => 'Payment received successfully!','transaction_id' => $usertransctionid);
                }
                //$response = array('flag'=>'1', 'message' => 'payment success.','transaction_id' => $usertransctionid);
                /*insert donation table in credit 1 credit or 2 debit*/
                $insertUserCredit = "INSERT INTO donations(transaction_id,userid,donationTime,type,amount,comment,creditBefore,creditAfter,donatedTo,operator,donation_status) VALUES('$transaction_id','$user_id','$donationTime','1','$amount','$comment','$oldCredit','$newCredit','2','$user_id','in_app')";


                $resultUserCredit =$pdo->prepare($insertUserCredit);
                $insertDonation   = $resultUserCredit->execute();

                /*Update user credit in user table*/
                $updateUser = "UPDATE users SET credit = '$newCredit' WHERE user_id = '$user_id'";
                $resultUserUpdateCredit = $pdo->prepare($updateUser);
                $UpdateDonation  = $resultUserUpdateCredit->execute();

                /* log insert in donation detail log table */
                $logTime = date('Y-m-d H:i:s');
                $insertUserCreditLog = "INSERT INTO log(logtype,logtime,user_id,operator,amount,oldCredit,newCredit) VALUES('6','$logTime','$user_id','$user_id','$amount','$oldCredit','$newCredit')";
                $resultUserCreditLog = $pdo->prepare($insertUserCreditLog);
                $insertDonationLog   = $resultUserCreditLog->execute();
                
                /* get notification count */
                $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                $resultcntdata = $pdo->prepare("$notificntdata");
                $resultcntdata->execute();
                $countnotfication = $resultcntdata->rowCount();

                if($insertDonation){
                    if($lang == 'es'){
                        $response = array('flag'=>'1', 'message' =>'¡Donación añadida con éxito!','user_id' => $user_id,'amount' => $amount , 'oldcredit' => $oldCredit , 'newcredit' => $newCredit,'Donation_time' => $donationTime,'transaction_id' => $usertransctionid,'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                    }else{
                        $response = array('flag'=>'1', 'message' =>'Donation added successfully!','user_id' => $user_id,'amount' => $amount , 'oldcredit' => $oldCredit , 'newcredit' => $newCredit,'Donation_time' => $donationTime,'transaction_id' => $usertransctionid,'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                    }
                    //$response = array('flag'=>'1', 'message' =>'Donation added successfully.','user_id' => $user_id,'amount' => $amount , 'oldcredit' => $oldCredit , 'newcredit' => $newCredit,'Donation_time' => $donationTime,'transaction_id' => $usertransctionid,'Top_up_credit' => $topupcredit, 'Pre_order' => $preorder, 'Show_price' => $showprice,'notification_count' => $countnotfication);
                    echo json_encode($response);

                }else{
                    if($lang == 'es'){
                        $response = array('flag'=>'0', 'message' =>'Algo ha ido mal, por favor inténtelo de nuevo.');
                    }else{
                        $response = array('flag'=>'0', 'message' =>'Something went wrong, please try again.');
                    }
                    //$response = array('flag'=>'0', 'message' =>'Donation not added,please try again.');
                    echo json_encode($response);
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

        }else{
            if($lang == 'es'){
                $response = array('flag'=>'0', 'message' =>'Todos los campos son obligatorios.');
            }else{
                $response = array('flag'=>'0', 'message' =>'All fields are mandatory.');
            }
            //$response = array('flag'=>'0', 'message' =>'Please add all parameter.');
            echo json_encode($response);
        }

    }catch(PDOException $e){
        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response);
    }