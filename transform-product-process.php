<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';


	if(isset($_POST['oneClick2'])){

		
		$remove_product_selected = explode(",", $_POST['remove_product_selected']);
		$remove_unit_selected =explode("," ,$_POST['remove_unit_selected']);
		
		
		if(!array_filter($remove_product_selected) || !array_filter($remove_unit_selected)){
			$_SESSION['errorMessage'] = "Please select product to create !";
			header("Location: transform-product.php");
			die();
		}
		



		$remove_product_arr =[];

		for($i = 0; $i< count($remove_product_selected); $i++ ){
			$remove_product_arr[$remove_product_selected[$i]] = $remove_unit_selected[$i];
		}

		$create_product = $_POST['create_product'];
		$create_unit = $_POST['create_unit'];
		$new_products = $_POST['new_products'];

		

		for($j=0; $j<count($create_product); $j++){
			$create_product_arr[$create_product[$j]] = $create_unit[$j];
		}
		
		if( !array_filter($create_product_arr) && !array_filter($new_products)){
			$_SESSION['errorMessage'] = "Please select product to create !";
			header("Location: transform-product.php");
			die();
		}

		// remove product unit from dispensary
		foreach($remove_product_arr  as $remove_product_key=> $remove_product_val){
			$movementTypeid = 5;
			$purchaseid = $remove_product_key;

				// Query to look for purchase
				$purchaseDetails = "SELECT category FROM purchases WHERE purchaseid = $purchaseid";
					try
					{
						$result1 = $pdo3->prepare("$purchaseDetails");
						$result1->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row1 = $result1->fetch();
					$category = $row1['category'];
			$quantity = $remove_product_val;
			$comment = "Removed from transform product process !";
			$movementtime = date('Y-m-d H:i:s');
			// Query to add new purchase movement - 6 arguments
			  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, user_id, category, stashMovementType) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', '%d', '%d', '%d');",
			  $movementtime, '2', $purchaseid, $quantity, $movementTypeid, $comment, $_SESSION['user_id'], $category, 1);
			try
			{
				$result = $pdo3->prepare("$query")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		}

		// add product unit in dispensary
		if(!empty($create_product_arr)){
			foreach($create_product_arr as $create_product_key=> $create_product_val){
				$movementTypeid = 12;
				$purchaseid = $create_product_key;
				$category = $_POST['category'];
				$quantity = $create_product_val;
				$realweight = 0;
				$provider = 0;
				$price = 0;
				$paid = 0;
				$comment = "Added from transform product process !";
				$movementtime = date('Y-m-d H:i:s');
				
				// find out internal and external stash movemnets
				
				$stashMovementType = 1;
				

				if ($realweight == '' || $realweight == 0) {
					$realweight = $quantity;
				}
				
				// Query to add new purchase movement - 6 arguments
				  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, provider, price, paid, realquantity, user_id, category, stashMovementType) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', '%d', '%f', '%f', '%f', '%d', '%d', '%d');",
				  $movementtime, '1', $purchaseid, $quantity, $movementTypeid, $comment, $provider, $price, $paid, $realweight, $_SESSION['user_id'], $category, $stashMovementType);
				try
				{
					$result = $pdo3->prepare("$query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
		}

		// create new products
		if(!empty($new_products)){
			foreach($new_products as $n_product){
				$n_product_arr = json_decode($n_product);
							
				$name = $n_product_arr->name;
				$flowertype = $n_product_arr->flowertype;
				$extracttype = $n_product_arr->extracttype;
		        $extract = $n_product_arr->extract;
				$description = $n_product_arr->description;
				$medicaldescription = $n_product_arr->medicaldescription;
				$breed2 = $n_product_arr->breed2;
				$sativaPercentage = $n_product_arr->sativaPercentage;
				$THC = $n_product_arr->THC;
				$CBD = $n_product_arr->CBD;
				$CBN = $n_product_arr->CBN;
				$category = $n_product_arr->category_id;
				$insertTime = date('Y-m-d H:i:s');

				$purchaseDate = date('Y-m-d H:i:s');
				$purchaseQuantity = $n_product_arr->purchaseQuantity;
				$purchasePrice = $n_product_arr->purchaseppg;

				if($category == 1){
					$query = sprintf("INSERT INTO flower (registeredSince, name, flowertype, description, medicaldescription, breed2, sativaPercentage, THC, CBD, CBN) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%f');",
			  			$insertTime, $name, $flowertype, $description, $medicaldescription, $breed2, $sativaPercentage, $THC, $CBD, $CBN);
				}else if($category == 2){
					$query = sprintf("INSERT INTO extract (registeredSince, name, extracttype, extract, description, medicaldescription, THC, CBD, CBN) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f');",
			  			$insertTime, $name, $extracttype, $extract, $description, $medicaldescription, $THC, $CBD, $CBN);
				}else{
			
				// Query to add new goods
				  $query = sprintf("INSERT INTO products (category, registeredSince, name, flowertype, description, medicaldescription, breed2, sativaPercentage, THC, CBD, CBN) VALUES ('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%f');",
				  $category, $insertTime, $name, $flowertype, $description, $medicaldescription, $breed2, $sativaPercentage, $THC, $CBD, $CBN);
				}
				  
			  
		  
						try
						{
							$result = $pdo3->prepare("$query")->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
						
						$productid = $pdo3->lastInsertId();

				if($purchaseQuantity != ''){		


					$purchase_query = sprintf("INSERT INTO purchases (category, productid, purchaseDate, purchasePrice, purchaseQuantity) VALUES ('%d', '%d', '%s', '%f', '%f');",
					  $category, $productid, $purchaseDate, $purchasePrice, $purchaseQuantity);
							
							try
							{
								 $pdo3->prepare("$purchase_query")->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}
							
					}
			}
		}
		
		$_SESSION['successMessage'] = "Product created successfully !";
		header("Location: transform-product.php");
		die();
	}
