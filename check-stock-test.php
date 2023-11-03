<?php
require_once 'cOnfig/connection.php';	
	
    //  $num = 20-1027;
     // echo number_format($num, 0, '.','');  die;

		$selectProduct = "SELECT purchaseid, realQuantity, category FROM purchases WHERE closedAt IS NULL AND inMenu = 1";

		try
		{
			$resultProduct = $pdo3->prepare("$selectProduct");
			$resultProduct->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}



				
		while ($product = $resultProduct->fetch()) {

				$categoryid = $product['category'];
				$purchaseid = $product['purchaseid'];

				// get category type
				$selectCategory = "SELECT type from categories where id=".$categoryid;
					try
					{
						$resultCategory = $pdo3->prepare("$selectCategory");
						$resultCategory->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$catRow = $resultCategory->fetch();
						$type = $catRow['type'];
				// fetch warning limits from system settings
				$selectSettings = "SELECT dispensegLimit, dispenseuLimit from systemsettings";
				try
				{
					$result = $pdo3->prepare("$selectSettings");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$setRow = $result->fetch();
					$dispensegLimit = $setRow['dispensegLimit'];
					$dispenseuLimit = $setRow['dispenseuLimit'];
				
				// code for notifications

					
					$selectSales = "SELECT SUM(quantity) FROM salesdetails WHERE purchaseid = $purchaseid";
				
					try
					{
						$result = $pdo3->prepare("$selectSales");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$sales = $row['SUM(quantity)'];
			
					$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
					try
					{
						$result = $pdo3->prepare("$selectPermAdditions");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$permAdditions = $row['SUM(quantity)'];
					
					$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
					
					try
					{
						$result = $pdo3->prepare("$selectPermRemovals");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$permRemovals = $row['SUM(quantity)'];
							
					// Calculate what's in Internal stash
					$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
					try
					{
						$result = $pdo3->prepare("$selectStashedInt");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$stashedInt = $row['SUM(quantity)'];
						
					$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
					try
					{
						$result = $pdo3->prepare("$selectUnStashedInt");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$unStashedInt = $row['SUM(quantity)'];
				
							
						$inStashInt = $stashedInt - $unStashedInt;
						$inStashInt = $inStashInt;
				
				
					// Calculate what's in External stash
					$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
					try
					{
						$result = $pdo3->prepare("$selectStashedExt");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$stashedExt = $row['SUM(quantity)'];
						
					$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
					try
					{
						$result = $pdo3->prepare("$selectUnStashedExt");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$unStashedExt = $row['SUM(quantity)'];
			
						
					$inStashExt = $stashedExt - $unStashedExt;
					$inStashExt = $inStashExt;
					
					$inStash = $inStashInt + $inStashExt;
					$estStock = $product['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
					
					$estStock = number_format($estStock,1,'.','');
					
						if($type == 0){
							$note_type = 0;
							$dispenseLimit = $dispenseuLimit;
						}else if($type == 1){
							$note_type = 1;
							$dispenseLimit = $dispensegLimit;
						}


					// add notifications for stock
					if($estStock <= $dispenseLimit){
						echo $estStock."<br>";

						// check exist notification
						$checkNoti = "SELECT COUNT(*) FROM stock_notifications WHERE purchase_id = '$purchaseid'";
							try
							{
								$result = $pdo3->prepare("$checkNoti");
								$result->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching count: ' . $e->getMessage();
									echo $error;
									exit();
							}
							$selectRow = $result->fetch();
							$stock_num = $selectRow['COUNT'];
							$notetime = date('Y-m-d H:i:s');
							if($stock_num > 0){
								// update Notification

								$updateNoti = "UPDATE stock_notifications SET stock = '$estStock', internal_stash = '$inStashInt', external_stash = '$inStashExt', created_at = '$notetime' WHERE purchase_id = '$purchaseid' AND note_type = '$note_type'";
								try
								{
									$result = $pdo3->prepare("$updateNoti");
									$result->execute();
								}
								catch (PDOException $e)
								{
										$error = 'Error updating user: ' . $e->getMessage();
										echo $error;
										exit();
								}
							}else{
								// insert notification
								$insertNoti = sprintf("INSERT INTO stock_notifications (purchase_id, category_id, stock, internal_stash, external_stash, note_type, created_at) VALUES ('%d', '%d', '%f', '%f', '%f', '%d', '%s');",
				  						$purchaseid, $categoryid, $estStock, $inStashInt, $inStashExt, $note_type, $notetime);
								try
								{
									$result = $pdo3->prepare("$insertNoti");
									$result->execute();
								}
								catch (PDOException $e)
								{
										$error = 'Error inserting user: ' . $e->getMessage();
										echo $error;
										exit();
								}
							}
					}else{
						// Delete notification
						$deleteNoti = "DELETE FROM stock_notifications WHERE purchase_id = '$purchaseid' AND note_type IN (0,1)";
							try
							{
								$delete_result = $pdo3->prepare("$deleteNoti");
								$delete_result->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error deleting user: ' . $e->getMessage();
									echo $error;
									exit();
							}
					}
			}
			$response['result'] =  'Dispense stock checked!';
			echo json_encode($response);
			die; 
		
/*	else if($_POST['stock_type'] == 'bar'){

		  	$selectServices = "SELECT purchaseid, purchaseQuantity, category FROM b_purchases WHERE closedAt IS NULL AND inMenu = 1";
					
			try
			{
				$resultServices = $pdo3->prepare("$selectServices");
				$resultServices->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}

			while ($service = $resultServices->fetch()) {
				$name = $service['name'];
				$categoryID = $service['category'];
				$purchaseid = $service['purchaseid'];

			// fetch warning limits from system settings
			$selectSettings = "SELECT barLimit from systemsettings";
			try
			{
				$result = $pdo3->prepare("$selectSettings");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$setRow = $result->fetch();
				$barLimit = $setRow['barLimit'];
				
			
			// code for notifications

			$selectSales = "SELECT SUM(quantity) FROM b_salesdetails WHERE purchaseid = $purchaseid";
		
			try
			{
				$result = $pdo3->prepare("$selectSales");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$sales = $row['SUM(quantity)'];
	
			$selectPermAdditions = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
			try
			{
				$result = $pdo3->prepare("$selectPermAdditions");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
					$permAdditions = $row['SUM(quantity)'];
			
			$selectPermRemovals = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
			
			try
			{
				$result = $pdo3->prepare("$selectPermRemovals");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
					$permRemovals = $row['SUM(quantity)'];
					
			// Calculate what's in Internal stash
			$selectStashedInt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
			try
			{
				$result = $pdo3->prepare("$selectStashedInt");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
					$stashedInt = $row['SUM(quantity)'];
				
			$selectUnStashedInt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
			try
			{
				$result = $pdo3->prepare("$selectUnStashedInt");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
					$unStashedInt = $row['SUM(quantity)'];
		
					
				$inStashInt = $stashedInt - $unStashedInt;
				$inStashInt = $inStashInt;
		
		
			// Calculate what's in External stash
			$selectStashedExt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
			try
			{
				$result = $pdo3->prepare("$selectStashedExt");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
					$stashedExt = $row['SUM(quantity)'];
				
			$selectUnStashedExt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
			try
			{
				$result = $pdo3->prepare("$selectUnStashedExt");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
					$unStashedExt = $row['SUM(quantity)'];
	
				
			$inStashExt = $stashedExt - $unStashedExt;
			$inStashExt = $inStashExt;
			
			$inStash = $inStashInt + $inStashExt;
			$estStock = $service['purchaseQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
			
			 $estStock = number_format($estStock, 0);

				// add notifications for stock
				if($estStock <= $barLimit){


					// check exist notification
					 $checkNoti = "SELECT COUNT(*) FROM stock_notifications WHERE purchase_id = '$purchaseid'";
						try
						{
							$result = $pdo3->prepare("$checkNoti");
							$result->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
						$selectRow = $result->fetch();
						$stock_num = $selectRow['COUNT(*)'];
						$notetime = date('Y-m-d H:i:s');
						if($stock_num > 0){
							// update Notification
							
							 $updateNoti = "UPDATE stock_notifications SET stock = '$estStock', internal_stash = '$inStashInt', external_stash = '$inStashExt', created_at = '$notetime' WHERE purchase_id = '$purchaseid' AND note_type =2";
							try
							{
								$result = $pdo3->prepare("$updateNoti");
								$result->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}
						}else{
							// insert notification
							$insertNoti = sprintf("INSERT INTO stock_notifications (purchase_id, category_id, stock, internal_stash, external_stash, note_type, created_at) VALUES ('%d', '%d', '%f', '%f', '%f', '%d', '%s');",
			  						$purchaseid, $categoryID, $estStock, $inStashInt, $inStashExt, 2, $notetime);
							try
							{
								$result = $pdo3->prepare("$insertNoti");
								$result->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}
						}
					}else{
						// Delete notification
						 $deleteNoti = "DELETE FROM stock_notifications WHERE purchase_id = '$purchaseid' AND note_type = 2";
							try
							{
								$delete_result = $pdo3->prepare("$deleteNoti");
								$delete_result->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}
					}
				}
		$response['result'] =  'Bar stock checked!';
		echo json_encode($response);
		die;
	}*/

