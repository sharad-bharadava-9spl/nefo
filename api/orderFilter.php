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

        if(!empty($_POST['last_order'])){
            $last_order = $_POST['last_order'];
        }else{
            $last_order = ""; 
        }

        if(!empty($_POST['year'])){
            $year = $_POST['year'];
        }else{
            $year = ""; 
        }

        if(!empty($_POST['month'])){
            $month = $_POST['month'];
        }else{
            $month = ""; 
        }

        if($lang == 'es' || $lang == 'en'){ 

            /*last 30 order display in order table*/ 
            if($last_order){

                $selectOrders = "SELECT * FROM sales WHERE userid='$user_id' ORDER BY saleid DESC LIMIT 30";
                $resultorder = $pdo->prepare("$selectOrders");
                $resultorder->execute();
            }

            if($year){

                $selectOrders = "SELECT * FROM `sales` WHERE Year(created_at) = '$year'  AND userid='$user_id'ORDER BY saleid DESC";
                $resultorder = $pdo->prepare("$selectOrders");
                $resultorder->execute();
            }

            if($resultorder->rowCount() > 0){

                $response['data'] = array();
                $new_arr = array();

                /* get notification count */
                $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                $resultcntdata = $pdo->prepare("$notificntdata");
                $resultcntdata->execute();
                $countnotfication = $resultcntdata->rowCount();
                if($lang=='es')
                {	
                    $response = array('flag' => '1','order_count' => $resultorder->rowCount(),'message' => '¡Pedido encontrado con éxito!','notification_count' => $countnotfication);
                }else{
                    $response = array('flag' => '1','order_count' => $resultorder->rowCount(),'message' => 'Order Found Successfully!','notification_count' => $countnotfication);
                }
                //$response = array('flag' => '1','order_count' => $resultorder->rowCount(),'message' => 'Order Found Successfull','notification_count' => $countnotfication);

                while ($order = $resultorder->fetch()) {
                    $order_id = $order['order_id'];
                    $sales_id = $order['saleid'];

                     /*count orderid wise product*/
                    $selectOrderCount = "SELECT count(saleid) as ordercnt FROM `salesdetails` WHERE `saleid` = '$sales_id'";
                    $resultCount = $pdo->prepare("$selectOrderCount");
                    $resultCount->execute();
                    $orderDataCount = $resultCount->fetch();
                    $itemcount = $orderDataCount['ordercnt'];


                    /*check unique record number wise order count and image display each first row*/ 
                    $selectOrder = "SELECT * FROM `salesdetails` WHERE `saleid` = '$sales_id'";
                    $result = $pdo->prepare("$selectOrder");
                    $result->execute();
                  //  $itemcount = $result->rowCount();
                    $total_price   = $order['amount'];
                    $order_details = [];
                    /*get image data in product table*/
                    $i = 0;
                    while($orderData = $result->fetch()){
                        $categoryid  = $orderData['category'];
                        // check the type of category gram or unit

                        $checkCatType = "SELECT type from categories WHERE id=".$categoryid;
                        $resultCatType = $pdo->prepare("$checkCatType");
                        $resultCatType->execute();
                        $catTypeRow = $resultCatType->fetch();
                        $catType = $catTypeRow['type'];

                        if($categoryid == 1 || $categoryid == 2){
                            $quant_unit = "Gr.";
                        }
                        else if ($categoryid < 3 || $catType == 1) {
                            $quant_unit = "Gr.";
                        }else{
                            $quant_unit = "u.";
                        }
                        
                        $product_id   = $orderData['productid'];
                        $order_details[$i]['quantity'] = $orderData['quantity']." ".$quant_unit;
                        $order_details[$i]['amount'] = $orderData['amount'];

                        if($categoryid == 1){

                            $selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt FROM flower g, purchases p WHERE p.category = '$categoryid' AND p.productid = '$product_id' AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
                            $resultFlower = $pdo->prepare("$selectFlower");
                            $resultFlower->execute();
                            $flower = $resultFlower->fetch();
                            $order_details[$i]['product_name'] = mb_convert_encoding($flower['name'], 'UTF-8', 'HTML-ENTITIES');
                            /*image path*/
                            if(!empty($flower['purchaseid'] && $flower['photoExt'])){
                                $imagepath = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/purchases/' . $flower['purchaseid'] . '.' .  $flower['photoExt'];
                            }else{
                                $imagepath = "";
                            }
                        }

                        if($categoryid == 2){

                            $selectExtract = "SELECT h.extractid, h.name, h.extract, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM extract h, purchases p WHERE p.category = '$categoryid' AND p.productid = '$product_id' AND p.productid = h.extractid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
                            $resultsExtract = $pdo->prepare("$selectExtract");
                            $resultsExtract->execute();
                            $extract = $resultsExtract->fetch();
                            $order_details[$i]['product_name'] = mb_convert_encoding($extract['name'], 'UTF-8', 'HTML-ENTITIES');
                            /*image path*/
                            if(!empty($extract['purchaseid'] && $extract['photoExt'])){
                                $imagepath = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/purchases/' . $extract['purchaseid'] . '.' .  $extract['photoExt'];
                            }else{
                                $imagepath = "";
                            }
                        }

                        if($categoryid != 1 && $categoryid != 2){

                            $selectproductimage = "SELECT pr.productid, pr.name, pr.description,pr.medicaldescription,p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt ,p.category FROM products pr, purchases p WHERE p.category = '$categoryid' AND p.productid = '$product_id' AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC";
                            $resultproductimage = $pdo->prepare("$selectproductimage");
                            $resultproductimage->execute();
                            $product = $resultproductimage->fetch();
                            $order_details[$i]['product_name'] = mb_convert_encoding($product['name'], 'UTF-8', 'HTML-ENTITIES');
                            /*image path*/
                            if(!empty($product['purchaseid'] && $product['photoExt'])){
                                $imagepath = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/purchases/' . $product['purchaseid'] . '.' .  $product['photoExt'];
                            }else{
                                $imagepath = "";
                            }
                        }
                        $i++;
                    }

                    if(!empty($order['order_status'] == '1')){
                        $order_status = 'Ordered';
                    }else if(!empty($order['order_status'] == '2')){
                        $order_status = 'Prepared';
                    }else if(!empty($order['order_status'] == '3')){
                        $order_status = 'Picked';
                    }else if(!empty($order['order_status'] == '4')){
                        $order_status = "Cancel";
                    }else{ 
                        $order_status = "";
                    }
                     
                    $new_arr['order_id']         = $order['order_id'];
                    $new_arr['product_image']    = $imagepath;
                    $new_arr['itemcount']        = $itemcount;
                    $new_arr['total_price']      = $total_price;
                    $new_arr['order_status']     = $order_status;
                    $new_arr['product_total_amount']  = number_format($order['amount'],2);
                    $new_arr['product_discount']      = number_format(abs($order['user_discount']),2);
                    $new_arr['product_total']    = number_format($order['amount'] - abs($order['user_discount']),2);
                    $new_arr['create_order_date']= date('d M Y',strtotime($order['created_at']));
                    $new_arr['create_order_time']= date('H:i A',strtotime($order['created_at']));
                    $new_arr['order_details'] = $order_details;
                    $response['data'][]  = $new_arr;
                                        
                }
                echo json_encode($response);
            }else{

                $notificntdata1= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                $resultcntdata1 = $pdo->prepare("$notificntdata1");
                $resultcntdata1->execute();
                $countnotfication1 = $resultcntdata1->rowCount();
                if($lang=='es')
                {	
                    $response = array('flag' => '1', 'message' => 'Pedido no encontrado.','data' => [],'notification_count' => $countnotfication1);
                }else{
                    $response = array('flag' => '1', 'message' => 'Order Not Found.','data' => [],'notification_count' => $countnotfication1);
                }
                //$response = array('flag' => '1', 'message' => 'Order Not Found.','data' => [],'notification_count' => $countnotfication1);
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