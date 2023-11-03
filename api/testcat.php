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

        if(!empty($lang == 'es')){
            $selectCats = "SELECT * from categories ORDER by id ASC";
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
                    $response['data'] = array();
                    $new_arr = array();
                    $flowerarr = array();
                    $flowermultiple = array();

                        if(!empty($_POST['user_id'])){
                            if($lang=='es')
                            {	
                                $response = array('flag' => '1','message' => 'Category found successfully','cart_count'=> $userCount);
                            }else{
                                $response = array('flag' => '1','message' => 'Category found successfully','cart_count'=> $userCount);
                            }
                            //$response = array('flag' => '1','message' => 'Category Found Successfull','cart_count'=> $userCount);
                        }else{
                            if($lang=='es')
                            {	
                                $response = array('flag' => '1','message' => 'Category found successfully');
                            }else{
                                $response = array('flag' => '1','message' => 'Category found successfully');
                            }
                            //$response = array('flag' => '1','message' => 'Category Found Successfull');
                        }

                        $selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
                        $resultFlower = $pdo->prepare("$selectFlower");
                        $resultFlower->execute();

                        if($resultFlower->rowCount() > 0){
                            while($flower = $resultFlower->fetch()){

                                 /*image path*/
                                if(!empty($flower['purchaseid'] && $flower['photoExt'])){
                                    $imagepath = 'http://'.$_SERVER['SERVER_NAME'] .'/' . explode('/', $_SERVER['REQUEST_URI'])[1] ."/images/_demo/purchases/".$flower['purchaseid']. "." .$flower['photoExt']."";
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
                                $flowerarr['category_type']     = '2';
                                $flowerarr['product_id']        = $flower['productid'];
                                $flowerarr['product_name']      = $name;
                                $flowerarr['breed2']            = $flower['breed2'];
                                $flowerarr['flower_type']       = $flower['flowertype'];
                                $flowerarr['grow_type']         = $growtypeData['growtype'];


                             
                                if(in_array($flower['productid'],$userproarr)){
                                    $userproductprice = $flower['productid'];
                                    $cartuserproduct = "SELECT * FROM cartmobile WHERE product_id = '$userproductprice'";
                                    $proresult = $pdo->prepare("$cartuserproduct");
                                    $proresult->execute();
                                    $userproductname = $proresult->fetch();
                                    $flowerarr['add_cart'] =  '1';
                                    $flowerarr['sales_price'] = $userproductname['product_price'] .' '.'€';
                                    $flowerarr['extra_price'] = $userproductname['extra_price'] .' '.'€';  
                                    $flowerarr['extra_price_count']   =  $userproductname['extra_priceval'] .' '.'€';   
                                }else{
                                    $flowerarr['extra_price']      =""; 
                                    $flowerarr['extra_price_count'] = ""; 
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

                        /*static array with flower and extract*/
                        $staticarrayflower =array(
                            'id' => '1',
                            'categoryname' => 'Flowers',
                            'categorytype' => '2',
                            'icon' => 'http://'. $_SERVER['SERVER_NAME'] .'/' . explode('/', $_SERVER['REQUEST_URI'])[1] ."/api/image/flower.png",
                            'categoryproduct' => $flowermultiple,

                            );
                        $staticarrayextract =array(
                            'id' => '2',
                            'categoryname' => 'Extract',
                            'categorytype' => '2',
                            'icon' => 'http://'. $_SERVER['SERVER_NAME'] .'/' . explode('/', $_SERVER['REQUEST_URI'])[1] ."/api/image/flower.png",
                            );

                       $response['data'][] = $staticarrayflower;
                       $response['data'][] = $staticarrayextract;

                        while($category = $resultscat->fetch()){
                            
                            if(!empty($category['type']) == 0){
                                $categorytype = 'Unit';
                            }else{
                                $categorytype = 'Grams';
                            }

                             /*image path*/
                            if(!empty($category['Icon'])){
                                $catimagepath ='http://'.$_SERVER['SERVER_NAME'] .'/' . explode('/', $_SERVER['REQUEST_URI'])[1] .'/api/image/'.$category['Icon'];
                            }else{
                                $catimagepath ='http://'. $_SERVER['SERVER_NAME'] .'/' . explode('/', $_SERVER['REQUEST_URI'])[1] ."/api/image/noimage.png";
                            }

                            $new_arr['id'] = $category['id'];
                            $new_arr['categoryname'] = $category['name'];
                            $new_arr['categorytype'] = $category['type'];
                            $new_arr['icon']         = $catimagepath;
                            $response['data'][] = $new_arr;
                        }

                }else{
                    if($lang=='es')
                    {	
                        $response = array('flag' => '0', 'message' => 'Category Not Found');
                    }else{
                        $response = array('flag' => '0', 'message' => 'Category Not Found');
                    }
                    //$response = array('flag' => '0', 'message' => 'Category Not Found');
                }
            echo json_encode($response);
        
        }elseif(!empty($lang == 'en')){
            $selectCats = "SELECT * from categories ORDER by id ASC";
            $resultscat = $pdo->prepare("$selectCats");
            $resultscat->execute();

                if($resultscat->rowCount() > 0){

                    /*count for user product*/
                    $cartCountData = "SELECT * FROM cartmobile WHERE user_id = '$user_id'";
                    $result = $pdo->prepare("$cartCountData");
                    $result->execute();
                    $userCount = $result->rowCount();
                    $userDataFetch= $result->fetch();
                    
                    $response['data'] = array();
                    $new_arr = array();
                    $flowerarr = array();
                    $flowermultiple = array();
                    
                    if(!empty($_POST['user_id'])){
                        if($lang=='es')
                        {	
                            $response = array('flag' => '1','message' => 'Category found successfully','cart_count'=> $userCount);
                        }else{
                            $response = array('flag' => '1','message' => 'Category found successfully','cart_count'=> $userCount);
                        }
                        //$response = array('flag' => '1','message' => 'Category Found Successfull','cart_count'=> $userCount);
                    }else{
                        if($lang=='es')
                        {	
                            $response = array('flag' => '1','message' => 'Category Found Successfully.');
                        }else{
                            $response = array('flag' => '1','message' => 'Category Found Successfully.');
                        }
                        //$response = array('flag' => '1','message' => 'Category Found Successfull');
                    }

                        $selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
                        $resultFlower = $pdo->prepare("$selectFlower");
                        $resultFlower->execute();

                        if($resultFlower->rowCount() > 0){
                            while($flower = $resultFlower->fetch()){

                                 /*image path*/
                                if(!empty($flower['purchaseid'] && $flower['photoExt'])){
                                    $imagepath ='http://'.$_SERVER['SERVER_NAME'] .'/' . explode('/', $_SERVER['REQUEST_URI'])[1] ."/images/_demo/purchases/".$flower['purchaseid']. "." .$flower['photoExt']."";
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
                                $flowerarr['category_type']     = '2';
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
                                //$response['data'][] = $flowerarr;
                                $flowermultiple[] = $flowerarr;

                            }
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

                        /*static array with flower and extract*/
                        $staticarrayflower =array(
                            'id' => '1',
                            'categoryname' => 'Flowers',
                            'categorytype' => '',
                            'icon' => 'http://'. $_SERVER['SERVER_NAME'] .'/' . explode('/', $_SERVER['REQUEST_URI'])[1] ."/api/image/flower.png",
                            'categoryproduct' => $flowermultiple,

                            );
                        $staticarrayextract =array(
                            'id' => '2',
                            'categoryname' => 'Extract',
                            'categorytype' => '',
                            'icon' => 'http://'. $_SERVER['SERVER_NAME'] .'/' . explode('/', $_SERVER['REQUEST_URI'])[1] ."/api/image/flower.png",
                            );

                       $response['data'][] = $staticarrayflower;
                       $response['data'][] = $staticarrayextract;

                        while($category = $resultscat->fetch()){
                            
                            if(!empty($category['type']) == 0){
                                $categorytype = 'Unit';
                            }else{
                                $categorytype = 'Grams';
                            }

                            /*image path*/
                            if(!empty($category['Icon'])){
                                $catimagepath ='http://'.$_SERVER['SERVER_NAME'] .'/' . explode('/', $_SERVER['REQUEST_URI'])[1] .'/api/image/'.$category['Icon'];
                            }else{
                                $catimagepath ='http://'. $_SERVER['SERVER_NAME'] .'/' . explode('/', $_SERVER['REQUEST_URI'])[1] ."/api/image/noimage.png";
                            }

                            $new_arr['id'] = $category['id'];
                            $new_arr['categoryname'] = $category['name'];
                            $new_arr['categorytype'] = $category['type'];
                            $new_arr['icon']         = $catimagepath;
                            $response['data'][] = $new_arr;
                        }

                }else{
                    if($lang=='es')
                    {	
                        $response = array('flag' => '0', 'message' => 'Category Not Found');
                    }else{
                        $response = array('flag' => '0', 'message' => 'Category Not Found');
                    }
                    //$response = array('flag' => '0', 'message' => 'Category Not Found');
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




        

            