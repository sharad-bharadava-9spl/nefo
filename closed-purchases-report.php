<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	

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
					            ->setCellValue('I1',$lang['global-comment']);
					$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('J1',$lang['pur-closedat']);
					$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('K1','Cerrado en');
					$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true); 
				// FIND OUT HOW MANY TABLES TO EXPORT

	// FIND OUT HOW MANY TABLES TO EXPORT
	$i = 1;
	// Query to look up open products, flowers first
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode, closingDate FROM purchases WHERE closedAt IS NOT NULL AND category = 1 ORDER by purchaseDate DESC";
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

		$exp1 .= "'t$i', ";

	}
	
	$i++;
	
	// Query to look up open products, extracts next
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode, closingDate FROM purchases WHERE closedAt IS NOT NULL AND category = 2 ORDER by purchaseDate DESC";
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

		$exp1 .= "'t$i', ";

	}
	
	$i++;
	
		$selectCats = "SELECT id, name, description, type from categories WHERE id > 2 ORDER by id ASC";
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
			$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode, closingDate FROM purchases WHERE closedAt IS NOT NULL AND category = $categoryid ORDER by purchaseDate DESC";
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
	
	// Query to look up closed products, flowers first
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, closingComment, closedAt, estClosing, inMenu, closingComment, barCode, closingDate FROM purchases WHERE closedAt IS NOT NULL AND category = 1 ORDER by purchaseDate DESC";
	try
	{
		$result = $pdo3->prepare("$selectOpenPurchases");
		$result->execute();
		$data = $result->fetchAll();
		$flowercount = $result->rowCount();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	if ($data) {

	$objPHPExcel->getActiveSheet()
		                ->setCellValue('E2',$lang['global-flowerscaps']." (g.)");
		   $objPHPExcel->getActiveSheet()->getStyle('E2')->getFont()->setBold(true); 

?>

<?php
		$x = 3;
		foreach ($data as $purchase) {
	
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
		
			$inMenu = $purchase['inMenu'];
			
			$closedAt = $purchase['closedAt'];
			
			if ($closedAt == 0) {
				$closeColour = '';
			}
			else if ($closedAt < 0) {
				$closeColour = 'negative';
			} else if ($closedAt > 0) {
				$closeColour = 'positive';
			}
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['closingComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['closingComment']}</div>
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

		$selectProduct = "SELECT name, breed2 FROM flower WHERE $productid = flowerid";
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
			$formattedDate = date("d-m-Y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
			$breed2 = $row['breed2'];
		
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity), SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid";
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
		if ($_SESSION['realWeight'] == 1) {		
			$sales = $row['SUM(realQuantity)'];
		} else {
			$sales = $row['SUM(quantity)'];
		}

		$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
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
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
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
		$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
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
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
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
		$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
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
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
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
		$estStock = $purchase['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;

		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
/*	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?closed&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?closed&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.0f $_SESSION['currencyoperator']</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'><span class=' %s'>%0.02f g</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%s</span></td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $commentRead, $purchase['purchaseid'], $closeColour, $closedAt, $purchase['purchaseid'], date("d-m-Y", strtotime($purchase['closingDate'])));*/
	    	
	  //echo $purchase_row;
	  		$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$x, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$x, $name);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$x,  $purchase['purchaseQuantity']." g");
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$x, $purchase['realQuantity']." g"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$x, $purchase['purchasePrice'] * $purchase['purchaseQuantity']." ".$_SESSION['currencyoperator'].""); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$x, $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$x,  $purchase['salesPrice'] - $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('H'.$x, $purchase['salesPrice']." ".$_SESSION['currencyoperator']."/g");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$x, $purchase['closingComment']);	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$x, $closedAt);	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('K'.$x, $purchase['closingDate']);
	  $x++;
	}
	
}

?>


	 
<?php

   $datai = count($data) + 3;
   $y = $datai + 1;


	// Query to look up closed products, extracts next
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, closingComment, closedAt, estClosing, inMenu, barCode, closingDate FROM purchases WHERE closedAt IS NOT NULL AND category = 2 ORDER by purchaseDate DESC";
		try
		{
			$result = $pdo3->prepare("$selectOpenPurchases");
			$result->execute();
			$data = $result->fetchAll();
			$extractcount = $result->rowCount();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if ($data) {

	

		$objPHPExcel->getActiveSheet()
		                ->setCellValue('E'.$datai, $lang['global-extractscaps']. "(g.)");
		   $objPHPExcel->getActiveSheet()->getStyle('E'.$datai)->getFont()->setBold(true); 
						
?>

	

<?php

		foreach ($data as $purchase) {
	
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
			
			$inMenu = $purchase['inMenu'];
			
			$closedAt = $purchase['closedAt'];
			
			if ($closedAt == 0) {
				$closeColour = '';
			}
			else if ($closedAt < 0) {
				$closeColour = 'negative';
			} else if ($closedAt > 0) {
				$closeColour = 'positive';
			}
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['closingComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['closingComment']}</div>
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

		$selectProduct = "SELECT name FROM extract WHERE $productid = extractid";
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
			$formattedDate = date("d-m-Y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity), SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid";
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
		if ($_SESSION['realWeight'] == 1) {		
			$sales = $row['SUM(realQuantity)'];
		} else {
			$sales = $row['SUM(quantity)'];
		}

		$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
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
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
		
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
		$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
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
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
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
		$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
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
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
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
		$estStock = $purchase['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;

		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
	/*$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?closed&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?closed&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.0f $_SESSION['currencyoperator']</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'><span class=' %s'>%0.02f g</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%s</span></td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $commentRead, $purchase['purchaseid'], $closeColour, $closedAt, $purchase['purchaseid'], date("d-m-Y", strtotime($purchase['closingDate'])));*/
	    	
	  	 $objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$y, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$y, $name);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$y,  $purchase['purchaseQuantity']." g");
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$y, $purchase['realQuantity']." g"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$y, $purchase['purchasePrice'] * $purchase['purchaseQuantity']." ".$_SESSION['currencyoperator'].""); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$y, $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$y,  $purchase['salesPrice'] - $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('H'.$y, $purchase['salesPrice']." ".$_SESSION['currencyoperator']."/g");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$y, $purchase['closingComment']);	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$y, $closedAt." g");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('K'.$y, date("d-m-Y", strtotime($purchase['closingDate'])));
	  $y++;
	  
	}
}
		
		
?>

	
	 
<?php
		
		// Query to look up categories, then products in each category
		$selectCats = "SELECT id, name, description, type from categories WHERE id > 2 ORDER by sortorder ASC, id ASC";
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
		
			$j = 3;
			$dataj = $flowercount + $extractcount + 4;
			$z = $dataj + 1;
		while ($category = $resultCats->fetch()) {
			
			$categoryname = $category['name'];
			$categoryid = $category['id'];
			$type = $category['type'];
			
			if ($type == 0) {
				$type = "u";
			} else {
				$type = "g";
			}
			
			
	// Query to look up closed products, extracts next
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, closingComment, closedAt, estClosing, inMenu, barCode, closingDate FROM purchases WHERE closedAt IS NOT NULL AND category = $categoryid ORDER by purchaseDate DESC";
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
					 if($dataj > ($flowercount + $extractcount +4)){
					   	    $dataj  = $dataj+1;
					   	    $z = $z+1;


					   }
		
$objPHPExcel->getActiveSheet()
		                ->setCellValue('E'.$dataj, $categoryname ."(".$type.")");
		   $objPHPExcel->getActiveSheet()->getStyle('E'.$dataj)->getFont()->setBold(true); 
		
						
?>

	

<?php

		foreach ($data as $purchase) {
	
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
			
			$inMenu = $purchase['inMenu'];
			
			$closedAt = $purchase['closedAt'];
			
			if ($closedAt == 0) {
				$closeColour = '';
			}
			else if ($closedAt < 0) {
				$closeColour = 'negative';
			} else if ($closedAt > 0) {
				$closeColour = 'positive';
			}
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['closingComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['closingComment']}</div>
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

		$selectProduct = "SELECT name FROM products WHERE $productid = productid";
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
			$formattedDate = date("d-m-Y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity), SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid";
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
		if ($_SESSION['realWeight'] == 1) {		
			$sales = $row['SUM(realQuantity)'];
		} else {
			$sales = $row['SUM(quantity)'];
		}

		$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
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
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
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
		$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
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
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
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
		$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
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
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
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
		$estStock = $purchase['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;

	// Show G or U?
	if ($type == 'g') {
		
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
/*	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?closed&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?closed&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.0f $_SESSION['currencyoperator']</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'><span class=' %s'>%0.00f g</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%s</span></td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $commentRead, $purchase['purchaseid'], $closeColour, $closedAt, $purchase['purchaseid'], date("d-m-Y", strtotime($purchase['closingDate'])));*/

			$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$z, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$z, $name);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$z,  $purchase['purchaseQuantity']." g");
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$z, $purchase['realQuantity']." g"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$z, $purchase['purchasePrice'] * $purchase['purchaseQuantity']." ".$_SESSION['currencyoperator'].""); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$z, $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$z,  $purchase['salesPrice'] - $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('H'.$z, $purchase['salesPrice']." ".$_SESSION['currencyoperator']."/g");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$z, $purchase['closingComment']);	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$z, $closedAt." g");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('K'.$z, date("d-m-Y", strtotime($purchase['closingDate'])));	

	  
  	} else {
	  	
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
	/*$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?closed&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?closed&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.1f u</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.0f $_SESSION['currencyoperator']</td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /u</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /u</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /u</span></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'><span class=' %s'>%0.00f u</span></td>
  	   <td class='clickableRow right' href='purchase.php?closed&purchaseid=%d'>%s</span></td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $commentRead, $purchase['purchaseid'], $closeColour, $closedAt, $purchase['purchaseid'], date("d-m-Y", strtotime($purchase['closingDate'])));*/
	  	
  	}
	    		 $objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$z, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$z, $name);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$z,  $purchase['purchaseQuantity']." u");
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$z, $purchase['realQuantity']." u"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$z, $purchase['purchasePrice'] * $purchase['purchaseQuantity']." ".$_SESSION['currencyoperator'].""); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$z, $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/u"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$z,  $purchase['salesPrice'] - $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/u"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('H'.$z, $purchase['salesPrice']." ".$_SESSION['currencyoperator']."/u");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$z, $purchase['closingComment']);	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$z, $closedAt." u");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('K'.$z, date("d-m-Y", strtotime($purchase['closingDate'])));
	 
	  $dataj++;
	  $z++;
	}
	
	$j++;
} else {
	$j++;	
}		
			
?>

	
	 
<?php
		}
			ob_end_clean();
			    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			    header('Content-type: application/vnd.ms-excel');
			    header('Content-Disposition: attachment;filename=closed-purchases.xlsx');
			    header("Content-Type: application/download");
			    //header('Cache-Control: max-age = 0');
			    $objWriter->save('php://output');
    			die;

