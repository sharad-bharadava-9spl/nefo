<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';



		// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['name'])) {

			
			$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));
			$flowertype = $_POST['flowertype'];
			$extracttype = $_POST['extracttype'];
	        $extract = $_POST['extract'];
			$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
			$medicaldescription = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['medicaldescription'])));
			$breed2 = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['breed2'])));
			$sativaPercentage = $_POST['sativaPercentage'];
			$THC = $_POST['THC'];
			$CBD = $_POST['CBD'];
			$CBN = $_POST['CBN'];
			$category = $_POST['category_id'];
			$insertTime = date('Y-m-d H:i:s');

			$purchaseDate = date('Y-m-d H:i:s');
			$purchaseQuantity = $_POST['purchaseQuantity'];
			$purchasePrice = $_POST['purchaseppg'];

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
						$purchaseid = $pdo3->lastInsertId();
							// On success: redirect.
						$_SESSION['successMessage'] = $lang['add-purchaseadded'];
						//header("Location: purchase.php?purchaseid=" . $purchaseid);
						header("Location: purchase.php?purchaseid=$purchaseid");
				}else{

					$_SESSION['successMessage'] = $lang['product-added'] . "<br /><br />" . $lang['remember-add-purchase'];
				    header("Location: transform-product.php");
				}	
					
					
					
					exit();
	}
	/***** FORM SUBMIT END *****/