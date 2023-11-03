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
	
	echo "<META HTTP-EQUIV='refresh' CONTENT='30'>";
	
if ($_SESSION['realWeight'] == 1) {

	
	// Query to look up today's opening details + till + bank balance
	if ($_SESSION['openAndClose'] > 2) {
		
		$openingLookup = "SELECT openingid, openingtime, tillBalance, bankBalance FROM opening ORDER BY openingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$tillBalance = $row['tillBalance'];	
			$bankBalance = $row['bankBalance'];
			$openingid = $row['openingid'];
			$openingtime = $row['openingtime'];
		
	} else if ($_SESSION['openAndClose'] == 2) {
		
		$openingLookup = "SELECT closingid, closingtime, cashintill, bankBalance FROM closing WHERE currentClosing = 0 ORDER BY closingtime DESC LIMIT 1";	
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$tillBalance = $row['cashintill'];	
			$bankBalance = $row['bankBalance'];
			$openingid = $row['closingid'];
			$openingtime = $row['closingtime'];
			
	}
	
	// Determine if club has not done opening/closing in several days, and if so use variable later down to decide how to calculate stock, dispenses etc.		
	if ($_SESSION['openAndClose'] > 1) {
		/*
		$cutoffTime = strtotime('-2 day', strtotime(date('Y-m-d H:i')));
		$lastOpenOrClose = strtotime(date('Y-m-d H:i',strtotime($openingtime)));
		
		if ($lastOpenOrClose < $cutoffTime) {

			$noActiveOpening = 'true';
			
			if ($_SESSION['openAndClose'] > 2) {
				
				$_SESSION['errorMessage'] = $lang['no-recent-opening'];
				
			} else {
				
				$_SESSION['errorMessage'] = $lang['no-recent-closing'];
				
			}
		
		} else {
			
			$noActiveOpening = 'false';
			
		}
		*/
		
	}
	
	// Query to look up categories
	$selectCats = "SELECT id, name from categories WHERE id > 2 ORDER by id ASC";
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
	

		$i = 0;
		
		while ($category = $resultCats->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		
		// Create more product queries for each category - to be used further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.productid AS productid, pr.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
		
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = $categoryid AND d.amount <> 0";
		
		try
		{
			$result = $pdo3->prepare("$selectSalesOthers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayOthers = $row['SUM(d.amount)'];
			$quantitySoldOthers = $row['SUM(d.realQuantity)'];
			
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = $categoryid AND d.amount = 0";
		try
		{
			$result = $pdo3->prepare("$selectSalesOthers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$quantityGiftedOthers = $row['SUM(d.realQuantity)'];
			
		$othersSalesPercentageToday = ($salesTodayOthers / $salesToday) * 100;
		$othersGramsPercentageToday = ($quantitySoldOthers / $unitsSold) * 100;
		
	}
	
	
	$selectProducts = "SELECT '1' AS category, g.flowerid AS productid, g.name AS name, p.purchaseid AS purchaseid, p.growType AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW())) UNION ALL SELECT '2' AS category, h.extractid AS productid, h.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
	
	$selectProducts .= $customProducts;
		try
		{
			$resultProducts = $pdo3->prepare("$selectProducts");
			$resultProducts->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		

		$x = 0;
		while ($product = $resultProducts->fetch()) {
			
			$category = $product['category'];
			$productid = $product['productid'];
			$name = $product['name'];
			$purchaseid = $product['purchaseid'];
			$growtype = $product['growtype'];
			$inMenu = $product['inMenu'];
			
			if ($inMenu == 1) {
				$inMenu = " <a href='uTil/menuchange.php?purchaseid=$purchaseid&menu=Yes&src=stock'> <img src='images/in-menu.png' width='17' title='{$lang['in-menu']}' style='margin-bottom: -1px;' /></a>";
			} else {
				$inMenu = " <a href='uTil/menuchange.php?purchaseid=$purchaseid&menu=No&src=stock'> <img src='images/not-in-menu.png' width='16' title='{$lang['not-in-menu']}' style='margin-bottom: -1px;' /></a>";
			}

		
			
			if ($category == 1) {
				// Look up growtype
				$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = '$growtype'";
		try
		{
			$result = $pdo3->prepare("$growDetails");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if (!$data) {
					$growtype = '';
			
			
		} else {

			$rowGrow = $data[0];
					
					$growtype = "(" . $rowGrow['growtype'] . ")";
					
				}
			}
			// Determine how to calculate weight and sales:
			if ($_SESSION['openAndClose'] == 0 || $noActiveOpening == 'true') {
				
				// Calculate Stock
				
				// Original purchase
   				$purchaseLookup = "SELECT realQuantity from purchases where purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$purchaseLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$openingWeight = $row['realQuantity'];

				
				// Sales
				$selectSales = "SELECT SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid AND amount <> 0";
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
					$sales = $row['SUM(realQuantity)'];
		
				// Gifts
				$selectSales = "SELECT SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid AND amount = 0";
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
					$gifts = $row['SUM(realQuantity)'];
		
					
				// Additions and Removals (not permanent, just wrong variable name)
				$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1";
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
				
				$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2";
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
				$jarWeight = $openingWeight + $permAdditions - $permRemovals - $sales - $gifts;	
				
				$weightTotal = $jarWeight + $inStashInt + $inStashExt;
				
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid AND d.amount <> 0";
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
					$soldToday = $row['SUM(d.realQuantity)'];

				// Look up todays gifts
				$selectSales = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid AND d.amount = 0";
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
					$giftedToday = $row['SUM(d.realQuantity)'];


			} else {

				// Query to look up today's opening weight
				if ($_SESSION['openAndClose'] > 2) {
					
					$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE d.openingid = $openingid AND d.openingid = o.openingid AND category = $category AND purchaseid = $purchaseid";
					
				} else if ($_SESSION['openAndClose'] == 2) {
			
					$openingLookup = "SELECT d.weight FROM closingdetails d, closing o WHERE d.closingid = $openingid AND d.closingid = o.closingid AND category = $category AND purchaseid = $purchaseid";
					
				}
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if (!$data) {
	   				$purchaseLookup = "SELECT realQuantity from purchases where purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$purchaseLookup");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		}

			
			
				
			$row = $data[0];
					$openingWeight = $row['0'];
					
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE s.saletime > '$openingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid AND d.amount <> 0";
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
					$sales = $row['SUM(d.realQuantity)'];
	
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE s.saletime > '$openingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid AND d.amount = 0";
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
					$gifts = $row['SUM(d.realQuantity)'];
	
				// Look up additions and removals
				$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime > '$openingtime'";
		try
		{
			$result = $pdo3->prepare("$selectAdditions");
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
					
				$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime > '$openingtime'";
		try
		{
			$result = $pdo3->prepare("$selectRemovals");
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
					
					
				// Calculate jar weight:
				$jarWeight = $openingWeight + $permAdditions - $permRemovals - $sales - $gifts;	
				
				
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
				$inStashInt = number_format($inStashInt,0);
			
			
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
				$inStashExt = number_format($inStashExt,0);
				
				$weightTotal = $jarWeight + $inStashInt + $inStashExt;
				
			}
			
			// Reset Other Cat totals
			$otherTotJar = 0;
			$otherTotIntSt = 0;
			$otherTotExtSt = 0;
			$otherTot = 0;
			$otherSoldToday = 0;

			
			
	  		// Create totals per category
			if ($category == 1) {
				$flowerTotOpening = $flowerTotOpening + $openingWeight;
				$flowerTotPermAdd = $flowerTotPermAdd + $permAdditions;
				$flowerTotSales = $flowerTotSales + $sales;
				$flowerTotGifts = $flowerTotGifts + $gifts;
				$flowerTotPermRem = $flowerTotPermRem + $permRemovals;
				$flowerTotJar = $flowerTotJar + $jarWeight;
				$flowerTotIntSt = $flowerTotIntSt + $inStashInt;
				$flowerTotExtSt = $flowerTotExtSt + $inStashExt;
			} else if ($category == 2) {
				$extractTotOpening = $extractTotOpening + $openingWeight;
				$extractTotPermAdd = $extractTotPermAdd + $permAdditions;
				$extractTotSales = $extractTotSales + $sales;
				$extractTotGifts = $extractTotGifts + $gifts;
				$extractTotPermRem = $extractTotPermRem + $permRemovals;
				$extractTotJar = $extractTotJar + $jarWeight;
				$extractTotIntSt = $extractTotIntSt + $inStashInt;
				$extractTotExtSt = $extractTotExtSt + $inStashExt;
				
				// Add Extract header
				if ($extractHeader != 'set') {
					$productDetails .= <<<EOD
	<h3 class='title'>{$lang['global-extractscaps']}</h3>
EOD;
				$extractHeader = 'set';
				}
				
			} else {
				
				// Query to look up categories
				$selectCats = "SELECT id, name, type from categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$selectCats");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		  	    	$catName = $row['name'];
		  	    	$catID = $row['id'];
		  	    	$type = $row['type'];
		  	    	
				if (${'otherHeader' . $catID} != 'set') {
					$productDetails .= <<<EOD
	<h3 class='title'>$catName</h3>
EOD;
				${'otherHeader' . $catID} = 'set';
				}
					
					
					$otherTotals[$catID]['catName'] = $catName;
				    $otherTotals[$catID]['unit_val'] = $unit_val;
					$otherTotals[$catID]['otherTotOpening'] = $otherTotals[$catID]['otherTotOpening'] + $openingWeight;
					$otherTotals[$catID]['otherTotPermAdd'] = $otherTotals[$catID]['otherTotPermAdd'] + $permAdditions;
					$otherTotals[$catID]['otherTotSales'] = $otherTotals[$catID]['otherTotSales'] + $sales;
					$otherTotals[$catID]['otherTotGifts'] = $otherTotals[$catID]['otherTotGifts'] + $gifts;
					$otherTotals[$catID]['otherTotPermRem'] = $otherTotals[$catID]['otherTotPermRem'] + $permRemovals;
					$otherTotals[$catID]['otherTotJar'] = $otherTotals[$catID]['otherTotJar'] + $jarWeight;
					$otherTotals[$catID]['otherTotIntSt'] = $otherTotals[$catID]['otherTotIntSt'] + $inStashInt;
					$otherTotals[$catID]['otherTotExtSt'] = $otherTotals[$catID]['otherTotExtSt'] + $inStashExt;
					$otherTotals[$catID]['otherTot'] = $otherTotals[$catID]['otherTotJar'] + $otherTotals[$catID]['otherTotIntSt'] + $otherTotals[$catID]['otherTotExtSt'];
					$otherTotals[$catID]['otherSoldToday'] = $otherTotals[$catID]['otherSoldToday'] + $soldToday;

				}
			


				$productDetails .= <<<EOD
				
		<div class='actionbox-np2'>
			 <div class='mainboxheader'><center><a href='purchase.php?purchaseid=$purchaseid' target='_blank'>$name ($purchaseid)</a> $inMenu</h3></center></div>
		 <div class='boxcontent'>
		 <center>$growtype</center>
		 <table class='purchasetable'>
		 <tr>
			 <td class='biggerFont left'>{$lang['closeday-openingweight']}<span class='purchaseNumber'>$openingWeight</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>+ {$lang['closeday-added']}<span class='purchaseNumber'>$permAdditions</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left red'>- {$lang['global-dispenses']}<span class='purchaseNumber'>$sales</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left red'>- {$lang['gifted']}<span class='purchaseNumber'>$gifts</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'><strong>{$lang['weight']}</strong><span class='purchaseNumber'><strong>$jarWeight</strong></span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['intstash']}<span class='purchaseNumber'>$inStashInt</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['extstash']}<span class='purchaseNumber'>$inStashExt</span>
			 </td>
		 </tr>
		 </table>
		</div>
		</div>
				
EOD;

		} // End product loop
		
		
		$productOverview = <<<EOD
<h3 class='title'>{$lang['summarycaps']}</h3>
	<div class='actionbox-np2'>
	<div class='mainboxheader'><center><h3>{$lang['global-flowers']}</h3></center></div>
		 <div class='boxcontent'>
		
		 <table class='purchasetable'>
		 <tr>
			 <td class='biggerFont left'>{$lang['closeday-openingweight']}<span class='purchaseNumber'>$flowerTotOpening</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>+ {$lang['closeday-added']}<span class='purchaseNumber'>$flowerTotPermAdd</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>- {$lang['global-dispenses']}<span class='purchaseNumber'>$flowerTotSales</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>- {$lang['gifted']}<span class='purchaseNumber'>$flowerTotGifts</span>
			 </td>
		 </tr>	 <tr>
			 <td class='biggerFont left'>- {$lang['closeproduct-takeouts']}<span class='purchaseNumber'>$flowerTotPermRem</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'><strong>{$lang['weight']}</strong><span class='purchaseNumber'><strong>$flowerTotJar</strong></span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['intstash']}<span class='purchaseNumber'>$flowerTotIntSt</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['extstash']}<span class='purchaseNumber'>$flowerTotExtSt</span>
			 </td>
		 </tr>
		 </table>
		</div>
		</div>
EOD;

if ($_SESSION['domain'] != 'weflowers') {
	$productOverview .= <<<EOD

<div class='actionbox-np2'>
	<div class='mainboxheader'><center><h3>{$lang['global-extracts']}</h3></center></div>
		 <div class='boxcontent'>
		
		 <table class='purchasetable'>
		 <tr>
			 <td class='biggerFont left'>{$lang['closeday-openingweight']}<span class='purchaseNumber'>$extractTotOpening</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>+ {$lang['closeday-added']}<span class='purchaseNumber'>$extractTotPermAdd</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left red'>- {$lang['global-dispenses']}<span class='purchaseNumber'>$extractTotSales</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left red'>- {$lang['gifted']}<span class='purchaseNumber'>$extractTotGifts</span>
			 </td>
		 </tr>	 <tr>
			 <td class='biggerFont left red'>- {$lang['closeproduct-takeouts']}<span class='purchaseNumber'>$extractTotPermRem</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'><strong>{$lang['weight']}</strong><span class='purchaseNumber'><strong>$extractTotJar</strong></span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['intstash']}<span class='purchaseNumber'>$extractTotIntSt</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['extstash']}<span class='purchaseNumber'>$extractTotExtSt</span>
			 </td>
		 </tr>
		 </table>
		</div>
		</div>
EOD;
}
		
	foreach ($otherTotals as $otherTotkey => $otherTotvalue) {
		   $otherCat = $otherTotvalue['catName'];
		   $catUnit = $otherTotvalue['unit_val'];
		   $catTotOpening = $otherTotvalue['otherTotOpening'];
		   $catTotPermAdd = $otherTotvalue['otherTotPermAdd'];
		   $catTotSales = $otherTotvalue['otherTotSales'];
		   $catTotGifts = $otherTotvalue['otherTotGifts'];
		   $catTotPermRem = $otherTotvalue['otherTotPermRem'];
		   $catTotJar = $otherTotvalue['otherTotJar'];
		   $catTotIntSt = $otherTotvalue['otherTotIntSt'];
		   $catTotExtSt = $otherTotvalue['otherTotExtSt'];

	$productOverview .= <<<EOD

<div class='actionbox-np2'>
	<div class='mainboxheader'><center><h3>{$otherCat}</h3></center></div>
		 <div class='boxcontent'>
		
		 <table class='purchasetable'>
		 <tr>
			 <td class='biggerFont left'>{$lang['closeday-openingweight']}<span class='purchaseNumber'>$catTotOpening</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>+ {$lang['closeday-added']}<span class='purchaseNumber'>$catTotPermAdd</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left red'>- {$lang['global-dispenses']}<span class='purchaseNumber'>$catTotSales</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left red'>- {$lang['gifted']}<span class='purchaseNumber'>$catTotGifts</span>
			 </td>
		 </tr>	 <tr>
			 <td class='biggerFont left red'>- {$lang['closeproduct-takeouts']}<span class='purchaseNumber'>$catTotPermRem</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'><strong>{$lang['weight']}</strong><span class='purchaseNumber'><strong>$catTotJar</strong></span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['intstash']}<span class='purchaseNumber'>$catTotIntSt</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['extstash']}<span class='purchaseNumber'>$catTotExtSt</span>
			 </td>
		 </tr>
		 </table>
		</div>
		</div>
EOD;
	}	



		
	pageStart($lang['title-stock'], NULL, NULL, "pstock", "product admin dev-align-center", $lang['global-stockcaps'] . " <a href='stock-table.php' class='headerlink'>(" . $lang['changeview'] . ")</a>", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
		echo $productOverview;
		
		echo "<h3 class='title'>{$lang['global-flowerscaps']}</h3>";

		echo $productDetails;
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
} else {
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	// Query to look up today's opening details + till + bank balance
	if ($_SESSION['openAndClose'] > 2) {
		
		$openingLookup = "SELECT openingid, openingtime, tillBalance, bankBalance FROM opening ORDER BY openingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$tillBalance = $row['tillBalance'];	
			$bankBalance = $row['bankBalance'];
			$openingid = $row['openingid'];
			$openingtime = $row['openingtime'];
		
	} else if ($_SESSION['openAndClose'] == 2) {
		
		$openingLookup = "SELECT closingid, closingtime, cashintill, bankBalance FROM closing WHERE currentClosing = 0 ORDER BY closingtime DESC LIMIT 1";	
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$tillBalance = $row['cashintill'];	
			$bankBalance = $row['bankBalance'];
			$openingid = $row['closingid'];
			$openingtime = $row['closingtime'];
			
	}
	
	// Determine if club has not done opening/closing in several days, and if so use variable later down to decide how to calculate stock, dispenses etc.		
	if ($_SESSION['openAndClose'] > 1) {
				/*
		$cutoffTime = strtotime('-2 day', strtotime(date('Y-m-d H:i')));
		$lastOpenOrClose = strtotime(date('Y-m-d H:i',strtotime($openingtime)));
		
		if ($lastOpenOrClose < $cutoffTime) {

			$noActiveOpening = 'true';
			
			if ($_SESSION['openAndClose'] > 2) {
				
				$_SESSION['errorMessage'] = $lang['no-recent-opening'];
				
			} else {
				
				$_SESSION['errorMessage'] = $lang['no-recent-closing'];
				
			}
		
		} else {
			
			$noActiveOpening = 'false';
			
		}
		*/
		
	}
	
	// Query to look up categories
	$selectCats = "SELECT id, name from categories WHERE id > 2 ORDER by id ASC";
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
	

		$i = 0;
		
		while ($category = $resultCats->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		
		// Create more product queries for each category - to be used further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.productid AS productid, pr.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
		
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = $categoryid";
		try
		{
			$result = $pdo3->prepare("$selectSalesOthers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayOthers = $row['SUM(d.amount)'];
			$quantitySoldOthers = $row['SUM(d.quantity)'];
			
		$othersSalesPercentageToday = ($salesTodayOthers / $salesToday) * 100;
		$othersGramsPercentageToday = ($quantitySoldOthers / $unitsSold) * 100;
		
	}
	
	
	$selectProducts = "SELECT '1' AS category, g.flowerid AS productid, g.name AS name, p.purchaseid AS purchaseid, p.growType AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW())) UNION ALL SELECT '2' AS category, h.extractid AS productid, h.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
	
	$selectProducts .= $customProducts;
		try
		{
			$resultProducts = $pdo3->prepare("$selectProducts");
			$resultProducts->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

		$x = 0;
		while ($product = $resultProducts->fetch()) {
			
			$category = $product['category'];
			$productid = $product['productid'];
			$name = $product['name'];
			$purchaseid = $product['purchaseid'];
			$growtype = $product['growtype'];
			$inMenu = $product['inMenu'];
			
			if ($inMenu == 1) {
				$inMenu = " <a href='uTil/menuchange.php?purchaseid=$purchaseid&menu=Yes&src=stock'> <img src='images/in-menu.png' width='17' title='{$lang['in-menu']}' style='margin-bottom: -1px;' /></a>";
			} else {
				$inMenu = " <a href='uTil/menuchange.php?purchaseid=$purchaseid&menu=No&src=stock'> <img src='images/not-in-menu.png' width='16' title='{$lang['not-in-menu']}' style='margin-bottom: -1px;' /></a>";
			}
					
			
			if ($category == 1) {
				// Look up growtype
				$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = '$growtype'";
		try
		{
			$result = $pdo3->prepare("$growDetails");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if (!$data) {
					$growtype = '';
			
			
		} else {

			$rowGrow = $data[0];
					
					$growtype = "(" . $rowGrow['growtype'] . ")";
					
				}
			}
			// Determine how to calculate weight and sales:
			if ($_SESSION['openAndClose'] == 0 || $noActiveOpening == 'true') {
				
				// Calculate Stock
				
				// Original purchase
   				$purchaseLookup = "SELECT realQuantity from purchases where purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$purchaseLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$openingWeight = $row['realQuantity'];

				
				// Sales
				$selectSales = "SELECT SUM(quantity) FROM salesdetails WHERE purchaseid = $purchaseid AND amount <> 0";
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
		
				// Gifts
				$selectSales = "SELECT SUM(quantity) FROM salesdetails WHERE purchaseid = $purchaseid AND amount = 0";
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
					$gifts = $row['SUM(quantity)'];
		
					
				// Additions and Removals (not permanent, just wrong variable name)
				$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1";
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
				
				$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2";
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
				$jarWeight = $openingWeight + $permAdditions - $permRemovals - $sales - $gifts;	
				
				$weightTotal = $jarWeight + $inStashInt + $inStashExt;
				
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
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
					$soldToday = $row['SUM(d.quantity)'];

				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid AND d.amount = 0";
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
					$giftedToday = $row['SUM(d.quantity)'];

			} else {
				
				// Query to look up today's opening weight
				if ($_SESSION['openAndClose'] > 2) {
					
					$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE d.openingid = $openingid AND d.openingid = o.openingid AND category = $category AND purchaseid = $purchaseid";
					
				} else if ($_SESSION['openAndClose'] == 2) {
			
					$openingLookup = "SELECT d.weight FROM closingdetails d, closing o WHERE d.closingid = $openingid AND d.closingid = o.closingid AND category = $category AND purchaseid = $purchaseid";
					
				}
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if (!$data) {
	   				$purchaseLookup = "SELECT realQuantity from purchases where purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$purchaseLookup");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		}

			
			
				
			$row = $data[0];
					$openingWeight = $row['0'];					
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE s.saletime > '$openingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid AND d.amount <> 0";
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
					$sales = $row['SUM(d.quantity)'];
	
				// Look up todays gifts
				$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE s.saletime > '$openingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid AND d.amount = 0";
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
					$gifts = $row['SUM(d.quantity)'];
	
				// Look up additions and removals
				$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime > '$openingtime'";
		try
		{
			$result = $pdo3->prepare("$selectAdditions");
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
					
				$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime > '$openingtime'";
		try
		{
			$result = $pdo3->prepare("$selectRemovals");
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
					
					
				// Calculate jar weight:
				$jarWeight = $openingWeight + $permAdditions - $permRemovals - $sales - $gifts;	
				
				
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
				$inStashInt = number_format($inStashInt,0);
			
			
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
				$inStashExt = number_format($inStashExt,0);
				
				$weightTotal = $jarWeight + $inStashInt + $inStashExt;
				
			}
			
			// Reset Other Cat totals
			$otherTotJar = 0;
			$otherTotIntSt = 0;
			$otherTotExtSt = 0;
			$otherTot = 0;
			$otherSoldToday = 0;

			
			
	  		// Create totals per category
			if ($category == 1) {
				$flowerTotOpening = $flowerTotOpening + $openingWeight;
				$flowerTotPermAdd = $flowerTotPermAdd + $permAdditions;
				$flowerTotSales = $flowerTotSales + $sales;
				$flowerTotGifts = $flowerTotGifts + $gifts;
				$flowerTotPermRem = $flowerTotPermRem + $permRemovals;
				$flowerTotJar = $flowerTotJar + $jarWeight;
				$flowerTotIntSt = $flowerTotIntSt + $inStashInt;
				$flowerTotExtSt = $flowerTotExtSt + $inStashExt;
			} else if ($category == 2) {
				$extractTotOpening = $extractTotOpening + $openingWeight;
				$extractTotPermAdd = $extractTotPermAdd + $permAdditions;
				$extractTotSales = $extractTotSales + $sales;
				$extractTotGifts = $extractTotGifts + $gifts;
				$extractTotPermRem = $extractTotPermRem + $permRemovals;
				$extractTotJar = $extractTotJar + $jarWeight;
				$extractTotIntSt = $extractTotIntSt + $inStashInt;
				$extractTotExtSt = $extractTotExtSt + $inStashExt;
				
				// Add Extract header
				if ($extractHeader != 'set') {
					$productDetails .= <<<EOD
	<h3 class='title'>{$lang['global-extractscaps']}</h3>
EOD;
				$extractHeader = 'set';
				}
				
			} else {
				
				// Query to look up categories
				$selectCats = "SELECT id, name, type from categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$selectCats");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		  	    	$catName = $row['name'];
		  	    	$catID = $row['id'];
		  	    	$type = $row['type'];

				if (${'otherHeader' . $catID} != 'set') {
					$productDetails .= <<<EOD
	<h3 class='title'>$catName</h3>
EOD;
				${'otherHeader' . $catID} = 'set';
				}
					
					
					$otherTotals[$catID]['catName'] = $catName;
					$otherTotals[$catID]['unit_val'] = $unit_val;
					$otherTotals[$catID]['otherTotOpening'] = $otherTotals[$catID]['otherTotOpening'] + $openingWeight;
					$otherTotals[$catID]['otherTotPermAdd'] = $otherTotals[$catID]['otherTotPermAdd'] + $permAdditions;
					$otherTotals[$catID]['otherTotSales'] = $otherTotals[$catID]['otherTotSales'] + $sales;
					$otherTotals[$catID]['otherTotGifts'] = $otherTotals[$catID]['otherTotGifts'] + $gifts;
					$otherTotals[$catID]['otherTotPermRem'] = $otherTotals[$catID]['otherTotPermRem'] + $permRemovals;
					$otherTotals[$catID]['otherTotJar'] = $otherTotals[$catID]['otherTotJar'] + $jarWeight;
					$otherTotals[$catID]['otherTotIntSt'] = $otherTotals[$catID]['otherTotIntSt'] + $inStashInt;
					$otherTotals[$catID]['otherTotExtSt'] = $otherTotals[$catID]['otherTotExtSt'] + $inStashExt;
					$otherTotals[$catID]['otherTot'] = $otherTotals[$catID]['otherTotJar'] + $otherTotals[$catID]['otherTotIntSt'] + $otherTotals[$catID]['otherTotExtSt'];
					$otherTotals[$catID]['otherSoldToday'] = $otherTotals[$catID]['otherSoldToday'] + $soldToday;

				}
			


				$productDetails .= <<<EOD
				
	<div class='actionbox-np2'>
			 <div class='mainboxheader'><center><h3 style='margin-bottom: 2px;'><a href='purchase.php?purchaseid=$purchaseid' target='_blank'>$name ($purchaseid)</a> $inMenu</h3></center></div>
		 <div class='boxcontent'>
		 <center>$growtype</center>
		 <table class='purchasetable'>
		 <tr>
			 <td class='biggerFont left'>{$lang['closeday-openingweight']}<span class='purchaseNumber'>$openingWeight</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>+ {$lang['closeday-added']}<span class='purchaseNumber'>$permAdditions</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left red'>- {$lang['global-dispenses']}<span class='purchaseNumber'>$sales</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left red'>- {$lang['gifted']}<span class='purchaseNumber'>$gifts</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'><strong>{$lang['weight']}</strong><span class='purchaseNumber'><strong>$jarWeight</strong></span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['intstash']}<span class='purchaseNumber'>$inStashInt</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['extstash']}<span class='purchaseNumber'>$inStashExt</span>
			 </td>
		 </tr>
		 </table>
		</div>
		</div>
				
EOD;

		} // End product loop
		
		
$productOverview = <<<EOD
        
<h3 class='title'>{$lang['summarycaps']}</h3>
	<div class='actionbox-np2'>
	<div class='mainboxheader'><center><h3>{$lang['global-flowers']}</h3></center></div>
		 <div class='boxcontent'>
		
		 <table class='purchasetable'>
		 <tr>
			 <td class='biggerFont left'>{$lang['closeday-openingweight']}<span class='purchaseNumber'>$flowerTotOpening</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>+ {$lang['closeday-added']}<span class='purchaseNumber'>$flowerTotPermAdd</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left red'>- {$lang['global-dispenses']}<span class='purchaseNumber'>$flowerTotSales</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left red'>- {$lang['gifted']}<span class='purchaseNumber'>$flowerTotGifts</span>
			 </td>
		 </tr>	 <tr>
			 <td class='biggerFont left red'>- {$lang['closeproduct-takeouts']}<span class='purchaseNumber'>$flowerTotPermRem</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'><strong>{$lang['weight']}</strong><span class='purchaseNumber'><strong>$flowerTotJar</strong></span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['intstash']}<span class='purchaseNumber'>$flowerTotIntSt</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['extstash']}<span class='purchaseNumber'>$flowerTotExtSt</span>
			 </td>
		 </tr>
		 </table>
		</div>
		</div>
EOD;

if ($_SESSION['domain'] != 'weflowers') {
	$productOverview .= <<<EOD
<div class='actionbox-np2'>
	<div class='mainboxheader'><center><h3>{$lang['global-extracts']}</h3></center></div>
		 <div class='boxcontent'>
		
		 <table class='purchasetable'>
		 <tr>
			 <td class='biggerFont left'>{$lang['closeday-openingweight']}<span class='purchaseNumber'>$extractTotOpening</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>+ {$lang['closeday-added']}<span class='purchaseNumber'>$extractTotPermAdd</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left red'>- {$lang['global-dispenses']}<span class='purchaseNumber'>$extractTotSales</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left red'>- {$lang['gifted']}<span class='purchaseNumber'>$extractTotGifts</span>
			 </td>
		 </tr>	 <tr>
			 <td class='biggerFont left red'>- {$lang['closeproduct-takeouts']}<span class='purchaseNumber'>$extractTotPermRem</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'><strong>{$lang['weight']}</strong><span class='purchaseNumber'><strong>$extractTotJar</strong></span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['intstash']}<span class='purchaseNumber'>$extractTotIntSt</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['extstash']}<span class='purchaseNumber'>$extractTotExtSt</span>
			 </td>
		 </tr>
		 </table>
		</div>
		</div>
EOD;
}
		foreach ($otherTotals as $otherTotkey => $otherTotvalue) {
		   $otherCat = $otherTotvalue['catName'];
		   $catUnit = $otherTotvalue['unit_val'];
		   $catTotOpening = $otherTotvalue['otherTotOpening'];
		   $catTotPermAdd = $otherTotvalue['otherTotPermAdd'];
		   $catTotSales = $otherTotvalue['otherTotSales'];
		   $catTotGifts = $otherTotvalue['otherTotGifts'];
		   $catTotPermRem = $otherTotvalue['otherTotPermRem'];
		   $catTotJar = $otherTotvalue['otherTotJar'];
		   $catTotIntSt = $otherTotvalue['otherTotIntSt'];
		   $catTotExtSt = $otherTotvalue['otherTotExtSt'];

	$productOverview .= <<<EOD
<div class='actionbox-np2'>
	<div class='mainboxheader'><center><h3>{$otherCat}</h3></center></div>
		 <div class='boxcontent'>
		
		 <table class='purchasetable'>
		 <tr>
			 <td class='biggerFont left'>{$lang['closeday-openingweight']}<span class='purchaseNumber'>$catTotOpening</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>+ {$lang['closeday-added']}<span class='purchaseNumber'>$catTotPermAdd</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left red'>- {$lang['global-dispenses']}<span class='purchaseNumber'>$catTotSales</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left red'>- {$lang['gifted']}<span class='purchaseNumber'>$catTotGifts</span>
			 </td>
		 </tr>	 <tr>
			 <td class='biggerFont left red'>- {$lang['closeproduct-takeouts']}<span class='purchaseNumber'>$catTotPermRem</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'><strong>{$lang['weight']}</strong><span class='purchaseNumber'><strong>$catTotJar</strong></span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['intstash']}<span class='purchaseNumber'>$catTotIntSt</span>
			 </td>
		 </tr>		 <tr>
			 <td class='biggerFont left'>{$lang['extstash']}<span class='purchaseNumber'>$catTotExtSt</span>
			 </td>
		 </tr>
		 </table>
		</div>
		</div>
EOD;
	}	
		
		



		
	pageStart($lang['title-stock'], NULL, NULL, "pstock", "product admin dev-align-center", $lang['global-stockcaps'] . " <a href='stock-table.php' class='headerlink'>(" . $lang['changeview'] . ")</a>", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
		echo $productOverview;
		
		echo "<h3 class='title'>{$lang['global-flowerscaps']}</h3>";

		echo $productDetails;
		
		
}

	displayFooter();
