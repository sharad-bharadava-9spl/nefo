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

        if($lang == 'es' || $lang == 'en'){   

        	/*Main order detail get from sales table */
		    $selectmainOrder = "SELECT * FROM sales where order_id = $order_id AND orderForm = '1'";
            $resultmainorder = $pdo->prepare("$selectmainOrder");
		    $resultmainorder->execute();
		    $mainorderDetail = $resultmainorder->fetch();
		    $sales_id = $mainorderDetail['saleid'];
          
            /*Main sales detail get from sales details table */
		    $selectOrders = "SELECT * FROM `salesdetails` WHERE saleid='$sales_id'";
            $resultorder = $pdo->prepare("$selectOrders");
		    $resultorder->execute();

		    if($resultorder->rowCount() > 0){

		    	$selectUserOrder = "SELECT * from sales WHERE userid = '$user_id' AND order_id='$order_id' AND orderForm = '1' ORDER BY saleid ASC";
	            $result = $pdo->prepare("$selectUserOrder");
	            $result->execute();
	            $userDetail = $result->fetch();
	      
	            if(!empty($userDetail['amount'])){
	            	$total_price = $userDetail['amount'];
	            }else{
	            	$total_price = "";
	            }

	            if(!empty($userDetail['user_credit'])){
	            	$user_credit = $userDetail['user_credit'];
	            }else{
	            	$user_credit = "";
	            }

	            if(!empty($userDetail['user_grand_total'])){
	            	$user_grand_total = $userDetail['user_grand_total'];
	            }else{
	            	$user_grand_total = "";
	            }

	            if(!empty($userDetail['payment_mode'])){
	            	$payment_mode = $userDetail['payment_mode'];
	            }else{
	            	$payment_mode = "";
	            }

	            if(!empty($userDetail['order_id'])){
	            	$order_id = $userDetail['order_id'];
	            }else{
	            	$order_id = "";
	            }

	            if(!empty($userDetail['order_status'] == '1')){
	            	$order_status = 'Ordered';
	            }else if(!empty($userDetail['order_status'] == '2')){
	            	$order_status = 'Prepared';
	            }else if(!empty($userDetail['order_status'] == '3')){
	            	$order_status = 'Picked';
	            }else if(!empty($userDetail['order_status'] == '4')){
	            	$order_status = 'Cancel';
	            }else{ 
                    $order_status = "";
                }

	            /*total discount for user product*/
                $cartdiscountCountData = "SELECT * FROM sales WHERE userid = '$user_id' AND order_id = '$order_id' AND orderForm = '1' ";
                $resultdiscountcount = $pdo->prepare("$cartdiscountCountData");
                $resultdiscountcount->execute();
                $usertotaldiscountData = $resultdiscountcount->fetch();


                if(!empty($usertotaldiscountData['user_discount'])){
                    $userdiscounttotal = abs($usertotaldiscountData['user_discount']);
                }else{
                    $userdiscounttotal = 0;
                }
                 
                 /*count orderid wise product*/
                $selectOrderCount = "SELECT count(saleid) as ordercnt FROM `salesdetails` WHERE `saleid` = '$sales_id'";
                $resultCount = $pdo->prepare("$selectOrderCount");
                $resultCount->execute();
                $orderDataCount = $resultCount->fetch();
                $itemcount = $orderDataCount['ordercnt'];


                 /* get notification count */
                $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                $resultcntdata = $pdo->prepare("$notificntdata");
                $resultcntdata->execute();
                $countnotfication = $resultcntdata->rowCount();



		    	$response['data'] = array();
                $new_arr = array();
                $user_discountprice =  number_format(abs($total_price),2) - number_format(abs($userDetail['user_discount']),2);
				if($lang=='es')
                {	
					$response = array('flag' => '1','userproduct_Count'=>$itemcount,'total_price'=>$total_price,'user_discountprice' => number_format($user_discountprice,2),'user_discount' => number_format(abs($userDetail['user_discount']),2),'user_credit'=> number_format(abs($user_credit),2),'user_grand_total' => $user_grand_total,'payment_mode'=>$payment_mode,'order_status' => $order_status,'notification_count' => $countnotfication,'message' => '¡Pedido encontrado con éxito!');
				}else{
					$response = array('flag' => '1','userproduct_Count'=>$itemcount,'total_price'=>$total_price,'user_discountprice' => number_format($user_discountprice,2),'user_discount' => number_format(abs($userDetail['user_discount']),2),'user_credit'=> number_format(abs($user_credit),2),'user_grand_total' => $user_grand_total,'payment_mode'=>$payment_mode,'order_status' => $order_status,'notification_count' => $countnotfication,'message' => 'Order found successfully!');
				}
                //$response = array('flag' => '1','userproduct_Count'=>$itemcount,'total_price'=>$total_price,'user_discountprice' => number_format($user_discountprice,2),'user_discount' => number_format(abs($userDetail['user_discount']),2),'user_credit'=> number_format(abs($user_credit),2),'user_grand_total' => $user_grand_total,'payment_mode'=>$payment_mode,'order_status' => $order_status,'notification_count' => $countnotfication,'message' => 'Order Detail Found Successfull');

                while ($order = $resultorder->fetch()) {
                	
	        	    /*get image data in product table*/
	            	$categoryid  = $order['category'];
	                $product_id   = $order['productid'];
	                $saleid   = $order['saleid'];
                    
                    /*Get store main order detail in sales table*/
	                $getStoreOrderData = "SELECT * FROM sales WHERE saleid = '$saleid'";
	                $resultStoreOrderData = $pdo->prepare("$getStoreOrderData");
	                $resultStoreOrderData->execute();
	                $usertotalresultStoreOrderData = $resultStoreOrderData->fetch();


		            if($categoryid == 1){

	                    $selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt FROM flower g, purchases p WHERE p.category = '$categoryid' AND p.productid = '$product_id' AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
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

	                    $selectExtract = "SELECT h.extractid, h.name, h.extract, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM extract h, purchases p WHERE p.category = '$categoryid' AND p.productid = '$product_id' AND p.productid = h.extractid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
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

	                    $selectproductimage = "SELECT pr.productid, pr.name, pr.description,pr.medicaldescription,p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt ,p.category FROM products pr, purchases p WHERE p.category = '$categoryid' AND p.productid = '$product_id' AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC";
	                    $resultproductimage = $pdo->prepare("$selectproductimage");
	                    $resultproductimage->execute();
	                    $product = $resultproductimage->fetch();

	                    /*image path*/
	                    if(!empty($product['purchaseid'] && $product['photoExt'])){
	                        $imagepath = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/purchases/' . $product['purchaseid'] . '.' .  $product['photoExt'];
	                    }else{
	                        $imagepath = SITE_ROOT."/api/image/noimage.png";
	                    }
	                }

                    $new_arr['order_id']         = $usertotalresultStoreOrderData['order_id'];
                	$new_arr['product_id']       = $order['productid'];
                    $new_arr['product_name']     = $order['product_name'];
                    $new_arr['payment_mode']     = $usertotalresultStoreOrderData['payment_mode'];
                    $new_arr['product_description']        = $order['product_description'];
                    $new_arr['product_medicaldescription'] = $order['product_medicaldescription'];
                    $new_arr['product_image']    = $imagepath;
                    $new_arr['product_price']    = $order['product_price'];
                    $new_arr['flower_type']      = $order['flower_type'];
                    $new_arr['grow_type']        = $order['grow_type'];
                    $new_arr['breed2']           = $order['breed2'];
                    $new_arr['category_name']    = $order['category_name'];
                    $new_arr['category_type']    = $order['category_type'];
                    $new_arr['category_id']      = $order['category'];
                    $new_arr['extra_price']      = $order['extra_priceval'];
                    $new_arr['extra_price_count']      = $order['extra_price'];
                    $new_arr['create_order_date']= date('d M Y',strtotime($usertotalresultStoreOrderData['created_at']));
                    $new_arr['create_order_time']= date('H:i A',strtotime($usertotalresultStoreOrderData['created_at']));
                    $response['data'][]  = $new_arr;
                }
                echo json_encode($response);

		    }else{
				if($lang=='es')
                {	
					$response = array('flag' => '0', 'message' => 'Pedido no encontrado.');
				}else{
					$response = array('flag' => '0', 'message' => 'Order not found.');
				}
		    	//$response = array('flag' => '0', 'message' => 'Order not found');
                echo json_encode($response);
		    }
               
        }else{
			if($lang=='es')
			{	
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
