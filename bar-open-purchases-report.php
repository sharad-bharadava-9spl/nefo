<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	require_once 'vendor/PHPExcel/Classes/PHPExcel.php';

	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();
	// Set document properties
	$objPHPExcel->getProperties()->setCreator("Lokesh Nayak")
	                             ->setLastModifiedBy("Lokesh Nayak")
	                             ->setTitle("Test Document")
	                             ->setSubject("Test Document")
	                             ->setDescription("Test document for PHPExcel")
	                             ->setKeywords("office")
	                             ->setCategory("Test result file");	                               

					$objPHPExcel->setActiveSheetIndex(0);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A1',$lang['pur-date']);
					$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B1',$lang['global-product']);
					$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  		
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C1',$lang['global-quantity']);
					$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D1',$lang['add-realweightshort']);
					$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('E1','Precio');
					$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('F1',$lang['purchase']);
					$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('G1',$lang['pur-quota']);
					$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('H1',$lang['global-dispense']);
					$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('I1',$lang['title-stock']);
					$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('J1',$lang['stash']);
					$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('K1',$lang['pur-inmenu']);
					$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true); 
				// FIND OUT HOW MANY TABLES TO EXPORT

	
	// FIND OUT HOW MANY TABLES TO EXPORT
	$i = 1;
	
		$selectCats = "SELECT id, name, description from b_categories ORDER by id ASC";
		try
		{
			$resultCats = $pdo3->prepare("$selectCats");
			$resultCats->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		while ($category = $resultCats->fetch()) {
			
			$categoryid = $category['id'];
			
			// Query to look up open products, extracts next
			$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, adminComment, inMenu, barCode FROM b_purchases WHERE closedAt IS NULL AND category = $categoryid ORDER by purchaseDate DESC";
			try
			{
				$result = $pdo3->prepare("$selectOpenPurchases");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
			if ($data) {
		
				$exp1 .= "'t$categoryid', ";
		
			}
			
			$i++;
			
		}

				
	$exp1 = substr($exp1, 0, -2);

?>

<?php
        

	// Query to look up categories
	$selectCats = "SELECT id, name, description from b_categories ORDER by sortorder ASC, id ASC";
	try
	{
		$resultCats = $pdo3->prepare("$selectCats");
		$resultCats->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$j = 1;
			$datai = 2;
		$x = 3;
	while ($sort = $resultCats->fetch()) {
		
		$categoryid = $sort['id'];
		$name = $sort['name'];
		
			$type = "u";
		
			// Query to look up open products, extracts next
			$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, adminComment, inMenu, barCode FROM b_purchases WHERE closedAt IS NULL AND category = $categoryid ORDER by purchaseDate DESC";
			try
			{
				$result = $pdo3->prepare("$selectOpenPurchases");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
			if ($data) {
			   if($datai > 2){
	   	    $datai  = $datai+1;
	   	    $x = $x+1;


	   }
					$objPHPExcel->getActiveSheet()
		                ->setCellValue('E'.$datai, $name ."(".$type.")");
		   $objPHPExcel->getActiveSheet()->getStyle('E'.$datai)->getFont()->setBold(true); 

		
						
?>

	
<?php

		foreach ($data as $purchase) {
	
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
			
			$inMenu = $purchase['inMenu'];
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['adminComment']}</div>
		                <script>
		                  	$('#comment$purchaseid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$purchaseid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$purchaseid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		

		$selectProduct = "SELECT name FROM b_products WHERE $productid = productid";
		try
		{
			$productResult = $pdo3->prepare("$selectProduct");
			$productResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $productResult->fetch();
			$name = $row['name'];
			$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity) FROM b_salesdetails WHERE purchaseid = $purchaseid";
		try
		{
			$sale = $pdo3->prepare("$selectSales");
			$sale->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $sale->fetch();
			$sales = $row['SUM(quantity)'];

		$selectPermAdditions = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10 OR movementTypeid = 22)";
		try
		{
			$sale = $pdo3->prepare("$selectPermAdditions");
			$sale->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $sale->fetch();
				$permAdditions = $row['SUM(quantity)'];
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
		try
		{
			$sale = $pdo3->prepare("$selectPermRemovals");
			$sale->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $sale->fetch();
				$permRemovals = $row['SUM(quantity)'];
				
		// Calculate what's in Internal stash
		$selectStashedInt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		try
		{
			$sale = $pdo3->prepare("$selectStashedInt");
			$sale->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $sale->fetch();
				$stashedInt = $row['SUM(quantity)'];
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		try
		{
			$sale = $pdo3->prepare("$selectUnStashedInt");
			$sale->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $sale->fetch();
				$unStashedInt = $row['SUM(quantity)'];
	
				
			$inStashInt = $stashedInt - $unStashedInt;
			$inStashInt = $inStashInt;
	
	
		// Calculate what's in External stash
		$selectStashedExt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		try
		{
			$sale = $pdo3->prepare("$selectStashedExt");
			$sale->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $sale->fetch();
				$stashedExt = $row['SUM(quantity)'];
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		try
		{
			$sale = $pdo3->prepare("$selectUnStashedExt");
			$sale->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $sale->fetch();
				$unStashedExt = $row['SUM(quantity)'];

			
		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExt = $inStashExt;
		
		$inStash = $inStashInt + $inStashExt;
		$estStock = $purchase['purchaseQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
		
	// Show G or U?
	if ($type == 'g') {

		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
		                // KONSTANT CODE UPDATE BEGIN
                $selectSales = "SELECT id FROM b_volume_discounts WHERE purchaseid =". $purchase['purchaseid'];
                try
                {
                    $result = $pdo3->prepare("$selectSales");
                    $result->execute();
                    $data = $result->fetch();
                }
                catch (PDOException $e)
                {
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
                } 
                if ($data['id'] != '') {
                    $discountImg = "<img src='images/discount.png' width='15' />";
		} else {
                    $discountImg = "";
		}
                // KONSTANT CODE UPDATE END		
/*	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='bar-purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='bar-purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.0f $_SESSION['currencyoperator']</td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td style='text-align: center' href='bar-purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	   <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);*/

			$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$x, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$x, $name);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$x,  $purchase['purchaseQuantity']." g");
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$x, $purchase['purchaseQuantity']." g"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$x, $purchase['purchasePrice'] * $purchase['purchaseQuantity']." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$x, $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$x,  $purchase['salesPrice'] - $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('H'.$x, $purchase['salesPrice']." ".$_SESSION['currencyoperator']."/g");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$x, $estStock." g");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$x, $inStash." g");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('K'.$x, $inMenu);	
	  
  	} else {
	  	
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
		     // KONSTANT CODE UPDATE BEGIN
                $selectSales = "SELECT id FROM b_volume_discounts WHERE purchaseid =". $purchase['purchaseid'];
                try
                {
                    $result = $pdo3->prepare("$selectSales");
                    $result->execute();
                    $data = $result->fetch();
                }
                catch (PDOException $e)
                {
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
                } 
                if ($data['id'] != '') {
                    $discountImg = "<img src='images/discount.png' width='15' />";
		} else {
                    $discountImg = "";
		}
                // KONSTANT CODE UPDATE END

/*	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='bar-purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='bar-purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.1f u</td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.0f $_SESSION['currencyoperator']</td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /u</span></td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /u</span></td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /u</span></td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='bar-purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td style='text-align: center' href='bar-purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	    <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);*/
	  	 $objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$x, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$x, $name);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$x,  $purchase['purchaseQuantity']." u");
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$x, $purchase['purchaseQuantity']." u"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$x, $purchase['purchasePrice'] * $purchase['purchaseQuantity']." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$x, $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/u"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$x,  $purchase['salesPrice'] - $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/u"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('H'.$x, $purchase['salesPrice']." ".$_SESSION['currencyoperator']."/u");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$x, $estStock." u");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$x, $inStash." u");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('K'.$x, $inMenu);
  	}
	    	
	 
	   $datai++;
	  $x++;
	  
	}
	
	$j++;
	
	
}		
			
?>

<?php
	

	}
	ob_end_clean();
			    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			    header('Content-type: application/vnd.ms-excel');
			    header('Content-Disposition: attachment;filename=bar-open-purchases.xlsx');
			    header("Content-Type: application/download");
			    //header('Cache-Control: max-age = 0');
			    $objWriter->save('php://output');
    			die;
