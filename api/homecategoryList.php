<?php 
    include('connectionM.php');

	if(!empty($_POST['language'])){
		$lang = $_POST['language'];
	}else{
		$lang = ""; 
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

    	if(!empty($lang == 'en')){

    	/*Flower category wise product*/
    		$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
    	    $resultFlower = $pdo->prepare("$selectFlower");
	        $resultFlower->execute();

	            if($resultFlower->rowCount() > 0){

	            	/*count for user product*/
                    $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
                    $result = $pdo->prepare("$cartCountData");
                    $result->execute();
                    $userCount = $result->rowCount();

	            	$response['data'] = array();
	                $flowerarr = array();

					if($lang=='es')
					{	
						$response = array('flag' => '1','message' => '¡Producto encontrado con éxito!','cart_count'=> $userCount);
					}else{
						$response = array('flag' => '1','message' => 'Product Found Successfull','cart_count'=> $userCount);
					}
	                //$response = array('flag' => '1','message' => 'Product Found Successfull','cart_count'=> $userCount);

	            	while($flower = $resultFlower->fetch()){

                        /*image path*/
	            		if(!empty($flower['purchaseid'] && $flower['photoExt'])){
	                		$imagepath = $_SERVER['SERVER_NAME'] .'/' . explode('/', $_SERVER['REQUEST_URI'])[1] ."/images/_demo/purchases/".$flower['purchaseid']. "." .$flower['photoExt']."";
	                	}else{
	                		$imagepath ='http://'. $_SERVER['SERVER_NAME'] .'/' . explode('/', $_SERVER['REQUEST_URI'])[1] ."/api/image/noimage.png";
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

	            		$flowerarr['category_id']       = 1;
	            		$flowerarr['category_name']     = 'Flowers';
	            		$flowerarr['product_id']        = $flower['productid'];
            	        $flowerarr['product_name']      = $name;
            	        $flowerarr['breed2']            = $flower['breed2'];
            	        $flowerarr['flower_type']       = $flower['flowertype'];
            	        $flowerarr['grow_type']         = $growtypeData['growtype'];
	                	$flowerarr['sales_price']       = $flower['salesPrice'] .' '.'€';
	                	$flowerarr['realquantity']      = $flower['realQuantity'];
	                	$flowerarr['product_image']     = $imagepath;
	                	$flowerarr['product_description'] = $product_descritpion;
            	        $flowerarr['product_medicaldescription'] = $product_medicaldescritpion;
	                	$flowerarr['percentageDisplay'] = $percentageDisplay;
	                	$flowerarr['purchase_id'] = $flower['purchaseid'];
	            		$response['data'][] = $flowerarr;
	            	}
	            }else{
					if($lang=='es')
					{	
						$response = array('flag' => '0', 'message' => 'Producto no encontrado.');
					}else{
						$response = array('flag' => '0', 'message' => 'Product Not Found');
					}
	            	//$response = array('flag' => '0', 'message' => 'Product Not Found');
	            }
	            echo json_encode($response);
        }else if(!empty($lang == 'es')){

    	/*Flower category wise product*/
    		$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
    	    $resultFlower = $pdo->prepare("$selectFlower");
	        $resultFlower->execute();

	            if($resultFlower->rowCount() > 0){
	            	
	            	/*count for user product*/
                    $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
                    $result = $pdo->prepare("$cartCountData");
                    $result->execute();
                    $userCount = $result->rowCount();
                    
	            	$response['data'] = array();
	                $flowerarr = array();
					if($lang=='es')
					{	
						$response = array('flag' => '1','message' => '¡Producto encontrado con éxito!','cart_count'=> $userCount);
					}else{
						$response = array('flag' => '1','message' => 'Product Found Successfull','cart_count'=> $userCount);
					}
	                //$response = array('flag' => '1','message' => 'Product Found Successfull','cart_count'=> $userCount);

	            	while($flower = $resultFlower->fetch()){

                        /*image path*/
	            		if(!empty($flower['purchaseid'] && $flower['photoExt'])){
	                		$imagepath ='http://'. $_SERVER['SERVER_NAME'] .'/' . explode('/', $_SERVER['REQUEST_URI'])[1] ."/images/_demo/purchases/".$flower['purchaseid']. "." .$flower['photoExt']."";
	                	}else{
	                		$imagepath ='http://'.$_SERVER['SERVER_NAME'] .'/' . explode('/', $_SERVER['REQUEST_URI'])[1] ."/api/image/noimage.png";
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

	            		$flowerarr['category_id']       = 1;
	            		$flowerarr['category_name']     = 'Flowers';
	            		$flowerarr['product_id']        = $flower['productid'];
            	        $flowerarr['product_name']      = $name;
            	        $flowerarr['breed2']            = $flower['breed2'];
            	        $flowerarr['flower_type']       = $flower['flowertype'];
            	        $flowerarr['grow_type']         = $growtypeData['growtype'];
	                	$flowerarr['sales_price']       = $flower['salesPrice'] .' '.'€';
	                	$flowerarr['realquantity']      = $flower['realQuantity'];
	                	$flowerarr['product_image']     = $imagepath;
	                	$flowerarr['product_description'] = $product_descritpion;
            	        $flowerarr['product_medicaldescription'] = $product_medicaldescritpion;
	                	$flowerarr['percentageDisplay'] = $percentageDisplay;
	                	$flowerarr['purchase_id'] = $flower['purchaseid'];
	            		$response['data'][] = $flowerarr;
	            	}
	            }else{
					if($lang=='es')
					{	
						$response = array('flag' => '0', 'message' => 'Producto no encontrado.');
					}else{
						$response = array('flag' => '0', 'message' => 'Product Not Found');
					}
	            	//$response = array('flag' => '0', 'message' => 'Product Not Found');
	            }
	            echo json_encode($response);
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
				$response = array('flag'=>'0', 'message' => 'Please add parameter categoryid and languageid.');
			}else{
				$response = array('flag'=>'0', 'message' => 'Please add parameter categoryid and languageid.');
			}
    	    //$response = array('flag'=>'0', 'message' => 'Please add parameter categoryid and languageid.');
		    echo json_encode($response); 	
    }
