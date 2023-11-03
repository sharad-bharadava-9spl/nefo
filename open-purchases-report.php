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
	
	
	// FIND OUT HOW MANY TABLES TO EXPORT
	$i = 1;
	// Query to look up open products, flowers first
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = 1 ORDER by purchaseDate DESC LIMIT 100";
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
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = 2 ORDER by purchaseDate DESC LIMIT 100";
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
	
		$selectCats = "SELECT id, name, description, type from categories ORDER by id ASC";
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
			$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = $categoryid ORDER by purchaseDate DESC LIMIT 100";
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
			
		}

				
	$exp1 = substr($exp1, 0, -2);
	
		
	
?>


<?php 
        

	// Query to look up categories
	$selectCats = "SELECT id, name, description, type from categories ORDER by sortorder ASC, id ASC";
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
  $worksheet = 0;
	while ($sort = $resultCats->fetch()) {
		
		$categoryid = $sort['id'];
		$name = $sort['name'];
		$type = $sort['type'];
		
		if ($type == 0) {
			$type = "u";
		} else {
			$type = "g";
		}
		
		if ($categoryid == 1) {

	
	// Query to look up open products, flowers first
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = 1 ORDER by purchaseDate DESC LIMIT 100";
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

		
		
					//$objPHPExcel->setActiveSheetIndex(0);
					$objWorkSheet = $objPHPExcel->createSheet($worksheet);
					$objPHPExcel->setActiveSheetIndex($worksheet);
					$objWorkSheet->setCellValue('C1',$lang['global-flowerscaps']." (g.)");
					            $objWorkSheet->getStyle('C1')->getFont()->setBold(true); 

					$objWorkSheet->setCellValue('A2',$lang['pur-date']);
					$objWorkSheet->getStyle('A2')->getFont()->setBold(true);  
					$objWorkSheet->setCellValue('B2',$lang['global-product']);
					$objWorkSheet->getStyle('B2')->getFont()->setBold(true);  		
					$objWorkSheet->setCellValue('C2',$lang['global-quantity']);
					$objWorkSheet->getStyle('C2')->getFont()->setBold(true);  
					$objWorkSheet->setCellValue('D2',$lang['add-realweightshort']);
					$objWorkSheet->getStyle('D2')->getFont()->setBold(true); 
					$objWorkSheet->setCellValue('E2','Precio');
					$objWorkSheet->getStyle('E2')->getFont()->setBold(true); 
					$objWorkSheet->setCellValue('F2',$lang['purchase']);
					$objWorkSheet->getStyle('F2')->getFont()->setBold(true);  
					$objWorkSheet->setCellValue('G2',$lang['pur-quota']);
					$objWorkSheet->getStyle('G2')->getFont()->setBold(true); 					
					$objWorkSheet->setCellValue('H2',$lang['global-dispense']);
					$objWorkSheet->getStyle('H2')->getFont()->setBold(true); 					
					$objWorkSheet->setCellValue('I2',$lang['pur-eststock']);
					$objWorkSheet->getStyle('I2')->getFont()->setBold(true); 					
					$objWorkSheet->setCellValue('J2',$lang['stash']);
					$objWorkSheet->getStyle('J2')->getFont()->setBold(true); 					
					$objWorkSheet->setCellValue('K2',$lang['pur-inmenu']);
					$objWorkSheet->getStyle('K2')->getFont()->setBold(true); 
					$objWorkSheet->setCellValue('L2',$lang['global-comment']);
					$objWorkSheet->getStyle('L2')->getFont()->setBold(true);  				
?>


<?php
    $index1 = 3;
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

		$selectProduct = "SELECT name, breed2 FROM flower WHERE flowerid = $productid";
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


       // Select reloads
		$selectPermAdditions = "SELECT SUM(quantity), SUM(realquantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 1";
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
			$totalReloads = $row['SUM(quantity)'];
			$realquantity = $row['SUM(realquantity)'];
			
			if ($realquantity > 0 ) {
				$totalReloads = $realquantity;
			}


		$selectPermAdditions = "SELECT SUM(quantity), SUM(realquantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 3 OR movementTypeid = 10 OR movementTypeid = 22)";
		
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
				$permAdditions = $row['SUM(quantity)'] + $totalReloads;;
		
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
		$estStock = $purchase['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
		
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
                // KONSTANT CODE UPDATE BEGIN
                $selectSales = "SELECT id FROM volume_discounts WHERE purchaseid =". $purchase['purchaseid'];
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
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f $_SESSION['currencyoperator']</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td style='text-align: center' href='purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	   <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);*/
	    	
	 					$objWorkSheet->setCellValue('A'.$index1, $formattedDate);
					    $objWorkSheet->setCellValue('B'.$index1, $name);
					    $objWorkSheet->setCellValue('C'.$index1, $purchase['purchaseQuantity']." g");
					    $objWorkSheet->setCellValue('D'.$index1, $purchase['realQuantity']." g"); 
			            $objWorkSheet->setCellValue('E'.$index1, $purchase['purchasePrice'] * $purchase['purchaseQuantity']." ".$_SESSION['currencyoperator']);  
			            $objWorkSheet->setCellValue('F'.$index1, $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g");
			           	$objWorkSheet->setCellValue('G'.$index1, $purchase['salesPrice'] - $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g"); 
			           	$objWorkSheet->setCellValue('H'.$index1, $purchase['salesPrice']." ".$_SESSION['currencyoperator']."/g"); 
			           	$objWorkSheet->setCellValue('I'.$index1, $estStock." g"); 
			           	$objWorkSheet->setCellValue('J'.$index1, $inStash." g"); 
			           	$objWorkSheet->setCellValue('K'.$index1, $inMenu); 
			           	$objWorkSheet->setCellValue('L'.$index1, $purchase['adminComment']); 
	  $index1++;
	}
	
}




		} else if ($categoryid == 2) {


	// Query to look up open products, extracts next
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = 2 ORDER by purchaseDate DESC LIMIT 100";
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

		

	/*	echo "<h3 class='title' onClick='load2()' style='cursor: pointer;'><img src='images/icon-extract.png' height='30' style='margin-right: 10px; margin-bottom: -8px;' /> {$lang['global-extractscaps']} (g.)<img src='images/expand.png' id='plus2' width='15' style='margin-left: 5px;' /><span id='spinner2'></span><input type='hidden' name='click2' id='click2' class='clickControl' value='0' /></h3><span id='menu2' style='display: none;'>";
		
		echo <<<EOD
<script>
function load2(){
	
  var x = document.getElementById("menu2");
  if (x.style.display === "none") {
	  
    x.style.display = "block";
	$("#plus2").attr("src","images/shrink.png");

  } else {
	  
    x.style.display = "none";
	$("#plus2").attr("src","images/expand.png");
	
  }

};
</script>
EOD;*/
					$objWorkSheet = $objPHPExcel->createSheet($worksheet);
					//$objPHPExcel->setActiveSheetIndex(1);
					$objWorkSheet->setCellValue('C1',$lang['global-extractscaps']." (g.)");
					            $objWorkSheet->getStyle('C1')->getFont()->setBold(true); 

					$objWorkSheet->setCellValue('A2',$lang['pur-date']);
					$objWorkSheet->getStyle('A2')->getFont()->setBold(true);  
					$objWorkSheet->setCellValue('B2',$lang['global-product']);
					$objWorkSheet->getStyle('B2')->getFont()->setBold(true);  		
					$objWorkSheet->setCellValue('C2',$lang['global-quantity']);
					$objWorkSheet->getStyle('C2')->getFont()->setBold(true);  
					$objWorkSheet->setCellValue('D2',$lang['add-realweightshort']);
					$objWorkSheet->getStyle('D2')->getFont()->setBold(true); 
					$objWorkSheet->setCellValue('E2','Precio');
					$objWorkSheet->getStyle('E2')->getFont()->setBold(true); 
					$objWorkSheet->setCellValue('F2',$lang['purchase']);
					$objWorkSheet->getStyle('F2')->getFont()->setBold(true);  
					$objWorkSheet->setCellValue('G2',$lang['pur-quota']);
					$objWorkSheet->getStyle('G2')->getFont()->setBold(true); 					
					$objWorkSheet->setCellValue('H2',$lang['global-dispense']);
					$objWorkSheet->getStyle('H2')->getFont()->setBold(true); 					
					$objWorkSheet->setCellValue('I2',$lang['pur-eststock']);
					$objWorkSheet->getStyle('I2')->getFont()->setBold(true); 					
					$objWorkSheet->setCellValue('J2',$lang['stash']);
					$objWorkSheet->getStyle('J2')->getFont()->setBold(true); 					
					$objWorkSheet->setCellValue('K2',$lang['pur-inmenu']);
					$objWorkSheet->getStyle('K2')->getFont()->setBold(true); 	
					$objWorkSheet->setCellValue('L2',$lang['global-comment']);
					$objWorkSheet->getStyle('L2')->getFont()->setBold(true); 					
?>



<?php
		$index2 =3;
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
			$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
		
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

		// Select reloads
		$selectPermAdditions = "SELECT SUM(quantity), SUM(realquantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 1";
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
			$totalReloads = $row['SUM(quantity)'];
			$realquantity = $row['SUM(realquantity)'];
			
			if ($realquantity > 0 ) {
				$totalReloads = $realquantity;
			}
			
		$selectPermAdditions = "SELECT SUM(quantity), SUM(realquantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 3 OR movementTypeid = 10 OR movementTypeid = 22)";
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
			$permAdditions = $row['SUM(quantity)'] + $totalReloads;
		
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
		                // KONSTANT CODE UPDATE BEGIN
                $selectSales = "SELECT id FROM volume_discounts WHERE purchaseid =". $purchase['purchaseid'];
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
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f $_SESSION['currencyoperator']</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td style='text-align: center' href='purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	   <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);*/
	    	
	 // echo $purchase_row;

	 					$objWorkSheet->setCellValue('A'.$index2, $formattedDate);
					    $objWorkSheet->setCellValue('B'.$index2, $name);
					    $objWorkSheet->setCellValue('C'.$index2, $purchase['purchaseQuantity']." g");
					    $objWorkSheet->setCellValue('D'.$index2, $purchase['realQuantity']." g"); 
			            $objWorkSheet->setCellValue('E'.$index2, $purchase['purchasePrice'] * $purchase['purchaseQuantity']." ".$_SESSION['currencyoperator']);  
			            $objWorkSheet->setCellValue('F'.$index2, $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g");
			           	$objWorkSheet->setCellValue('G'.$index2, $purchase['salesPrice'] - $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g"); 
			           	$objWorkSheet->setCellValue('H'.$index2, $purchase['salesPrice']." ".$_SESSION['currencyoperator']."/g"); 
			           	$objWorkSheet->setCellValue('I'.$index2, $estStock." g"); 
			           	$objWorkSheet->setCellValue('J'.$index2, $inStash." g"); 
			           	$objWorkSheet->setCellValue('K'.$index2, $inMenu); 
			           	$objWorkSheet->setCellValue('L'.$index2, $purchase['adminComment']); 
	  $index2++;
	  
	}
}
		
		
?>

	 
<?php
		
		} else {
			
			// Query to look up open products, extracts next
			$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = $categoryid ORDER by purchaseDate DESC LIMIT 100";
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
		
		

/*		echo "<h3 class='title' onClick='load$categoryid()' style='cursor: pointer;'>$name ($type) <img src='images/expand.png' id='plus$categoryid' width='15' style='margin-left: 5px;' /><span id='spinner$categoryid'></span><input type='hidden' name='click$categoryid' id='click$categoryid' class='clickControl' value='0' /></h3><span id='menu$categoryid' style='display: none;'>";
		
		echo <<<EOD
<script>
function load$categoryid(){
	
  var x = document.getElementById("menu$categoryid");
  if (x.style.display === "none") {
	  
    x.style.display = "block";
	$("#plus$categoryid").attr("src","images/shrink.png");

  } else {
	  
    x.style.display = "none";
	$("#plus$categoryid").attr("src","images/expand.png");
	
  }

};
</script>
EOD;*/
					$objWorkSheet = $objPHPExcel->createSheet($worksheet);
					//$objPHPExcel->setActiveSheetIndex(1);
					$objWorkSheet->setCellValue('C1',$name ."(".$type.")");
					            $objWorkSheet->getStyle('C1')->getFont()->setBold(true); 

					$objWorkSheet->setCellValue('A2',$lang['pur-date']);
					$objWorkSheet->getStyle('A2')->getFont()->setBold(true);  
					$objWorkSheet->setCellValue('B2',$lang['global-product']);
					$objWorkSheet->getStyle('B2')->getFont()->setBold(true);  		
					$objWorkSheet->setCellValue('C2',$lang['global-quantity']);
					$objWorkSheet->getStyle('C2')->getFont()->setBold(true);  
					$objWorkSheet->setCellValue('D2',$lang['add-realweightshort']);
					$objWorkSheet->getStyle('D2')->getFont()->setBold(true); 
					$objWorkSheet->setCellValue('E2','Precio');
					$objWorkSheet->getStyle('E2')->getFont()->setBold(true); 
					$objWorkSheet->setCellValue('F2',$lang['purchase']);
					$objWorkSheet->getStyle('F2')->getFont()->setBold(true);  
					$objWorkSheet->setCellValue('G2',$lang['pur-quota']);
					$objWorkSheet->getStyle('G2')->getFont()->setBold(true); 					
					$objWorkSheet->setCellValue('H2',$lang['global-dispense']);
					$objWorkSheet->getStyle('H2')->getFont()->setBold(true); 					
					$objWorkSheet->setCellValue('I2',$lang['pur-eststock']);
					$objWorkSheet->getStyle('I2')->getFont()->setBold(true); 					
					$objWorkSheet->setCellValue('J2',$lang['stash']);
					$objWorkSheet->getStyle('J2')->getFont()->setBold(true); 					
					$objWorkSheet->setCellValue('K2',$lang['pur-inmenu']);
					$objWorkSheet->getStyle('K2')->getFont()->setBold(true); 	
					$objWorkSheet->setCellValue('L2',$lang['global-comment']);
					$objWorkSheet->getStyle('L2')->getFont()->setBold(true); 				
?>



<?php
      $index3 =3;
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
			$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
		
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

		// Select reloads
		$selectPermAdditions = "SELECT SUM(quantity), SUM(realquantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 1";
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
			$totalReloads = $row['SUM(quantity)'];
			$realquantity = $row['SUM(realquantity)'];
			
			if ($realquantity > 0 ) {
				$totalReloads = $realquantity;
			}
			
		$selectPermAdditions = "SELECT SUM(quantity), SUM(realquantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 3 OR movementTypeid = 10 OR movementTypeid = 22)";
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
			$permAdditions = $row['SUM(quantity)'] + $totalReloads;
		
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
		 // KONSTANT CODE UPDATE BEGIN
                $selectSales = "SELECT id FROM volume_discounts WHERE purchaseid =". $purchase['purchaseid'];
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
	// Show G or U?
	if ($type == 'g') {

		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
/*	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f $_SESSION['currencyoperator']</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td style='text-align: center' href='purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	    <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);*/

	  	 				$objWorkSheet->setCellValue('A'.$index3, $formattedDate);
					    $objWorkSheet->setCellValue('B'.$index3, $name);
					    $objWorkSheet->setCellValue('C'.$index3, $purchase['purchaseQuantity']." g");
					    $objWorkSheet->setCellValue('D'.$index3, $purchase['realQuantity']." g"); 
			            $objWorkSheet->setCellValue('E'.$index3, $purchase['purchasePrice'] * $purchase['purchaseQuantity']." ".$_SESSION['currencyoperator']);  
			            $objWorkSheet->setCellValue('F'.$index3, $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g");
			           	$objWorkSheet->setCellValue('G'.$index3, $purchase['salesPrice'] - $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g"); 
			           	$objWorkSheet->setCellValue('H'.$index3, $purchase['salesPrice']." ".$_SESSION['currencyoperator']."/g"); 
			           	$objWorkSheet->setCellValue('I'.$index3, $estStock." g"); 
			           	$objWorkSheet->setCellValue('J'.$index3, $inStash." g"); 
			           	$objWorkSheet->setCellValue('K'.$index3, $inMenu); 
			           	$objWorkSheet->setCellValue('L'.$index3, $purchase['adminComment']); 
  	} else {
	  	
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}

/*	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.1f u</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f $_SESSION['currencyoperator']</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /u</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /u</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> $_SESSION['currencyoperator'] /u</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td style='text-align: center' href='purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	   <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);*/

			  	 		$objWorkSheet->setCellValue('A'.$index3, $formattedDate);
					    $objWorkSheet->setCellValue('B'.$index3, $name);
					    $objWorkSheet->setCellValue('C'.$index3, $purchase['purchaseQuantity']." u");
					    $objWorkSheet->setCellValue('D'.$index3, $purchase['realQuantity']." u"); 
			            $objWorkSheet->setCellValue('E'.$index3, $purchase['purchasePrice'] * $purchase['purchaseQuantity']." ".$_SESSION['currencyoperator']);  
			            $objWorkSheet->setCellValue('F'.$index3, $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/u");
			           	$objWorkSheet->setCellValue('G'.$index3, $purchase['salesPrice'] - $purchase['purchasePrice']." ".$_SESSION['currencyoperator']."/g"); 
			           	$objWorkSheet->setCellValue('H'.$index3, $purchase['salesPrice']." ".$_SESSION['currencyoperator']."/u"); 
			           	$objWorkSheet->setCellValue('I'.$index3, $estStock." u"); 
			           	$objWorkSheet->setCellValue('J'.$index3, $inStash." u"); 
			           	$objWorkSheet->setCellValue('K'.$index3, $inMenu); 
			           	$objWorkSheet->setCellValue('L'.$index3, $purchase['adminComment']); 
	  	
  	}
	    	
	 // echo $purchase_row;
	  $index3++;
	}
	
	$j++;
	
	
}		
			
?>

<?php
	$worksheet++;
		}
	

	}

		ob_end_clean();
	    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	    header('Content-type: application/vnd.ms-excel');
	    header('Content-Disposition: attachment;filename=open-purchases.xlsx');
	    header("Content-Type: application/download");
	    //header('Cache-Control: max-age = 0');
	    $objWriter->save('php://output');
	    
	    die;
?>
