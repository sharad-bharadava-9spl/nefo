<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-closing.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$closingtime = $_SESSION['closingtime'];
	
	$openingid = $_SESSION['openingid'];
	$openingtime = $_SESSION['openingtime'];
	$dayopeningid = $_SESSION['dayopeningid'];
	$dayopeningtime = $_SESSION['dayopeningtime'];
	
if ($_SESSION['realWeight'] == 1) {

	// If the page re-submitted, let's save Closing values for Reception! Also save Opening to 2.
	
	if (isset($_GET['saveDispensary'])) {
		// Retrieve variables for CLOSING insert
		$_SESSION['confirmedClose'] = $_POST['confirmedClose'];

		foreach($_SESSION['confirmedClose'] as $confirmedCloseCalc) {

			// Common
			$weight = $confirmedCloseCalc['weight'];
			$shake = $confirmedCloseCalc['shake'];			
			$category = $confirmedCloseCalc['category'];
			
			// Shift
			$weightToday = $confirmedCloseCalc['weightToday'];
			$addedToday = $confirmedCloseCalc['addedToday'];
			$soldToday = $confirmedCloseCalc['soldToday'];
			$takeoutsToday = $confirmedCloseCalc['takeoutsToday'];
			$estWeight = $confirmedCloseCalc['estWeight'];
			$weightDelta = $weight - $estWeight;
			
			// Day
			$dayweightToday = $confirmedCloseCalc['dayweightToday'];
			$dayaddedToday = $confirmedCloseCalc['dayaddedToday'];
			$daysoldToday = $confirmedCloseCalc['daysoldToday'];
			$daytakeoutsToday = $confirmedCloseCalc['daytakeoutsToday'];
			$dayestWeight = $confirmedCloseCalc['dayestWeight'];
			$dayweightDelta = $weight - $dayestWeight;

			if ($category == '1') {
				
				$prodOpeningFlower = $prodOpeningFlower + $weightToday;
				$prodAddedFlower = $prodAddedFlower + $addedToday;
				$prodRemovedFlower = $prodRemovedFlower + $takeoutsToday;
				$prodEstStockFlower = $prodEstStockFlower + $estWeight;
				$flowerWeight = $flowerWeight + $weight;
				$flowerDelta = $flowerDelta + $weightDelta;
				$weightWithoutShake = ($weight - ($weight * ($shake / 100)));
				$flowerWeightWithoutShake = $flowerWeightWithoutShake + $weightWithoutShake;
				
				$dayprodOpeningFlower = $dayprodOpeningFlower + $dayweightToday;
				$dayprodAddedFlower = $dayprodAddedFlower + $dayaddedToday;
				$dayprodRemovedFlower = $dayprodRemovedFlower + $daytakeoutsToday;
				$dayprodEstStockFlower = $dayprodEstStockFlower + $dayestWeight;
				$dayflowerWeight = $dayflowerWeight + $weight;
				$dayflowerDelta = $dayflowerDelta + $dayweightDelta;

			} else if ($category == '2') {
				
				$prodOpeningExtract = $prodOpeningExtract + $weightToday;
				$prodAddedExtract = $prodAddedExtract + $addedToday;
				$prodRemovedExtract = $prodRemovedExtract + $takeoutsToday;
				$prodEstStockExtract = $prodEstStockExtract + $estWeight;
				$extractWeight = $extractWeight + $weight;
				$extractDelta = $extractDelta + $weightDelta;
				
				$dayprodOpeningExtract = $dayprodOpeningExtract + $dayweightToday;
				$dayprodAddedExtract = $dayprodAddedExtract + $dayaddedToday;
				$dayprodRemovedExtract = $dayprodRemovedExtract + $daytakeoutsToday;
				$dayprodEstStockExtract = $dayprodEstStockExtract + $dayestWeight;
				$dayextractWeight = $dayextractWeight + $weight;
				$dayextractDelta = $dayextractDelta + $dayweightDelta;
				
			}

		$prodOpening = $prodOpeningFlower + $prodOpeningExtract;
		$prodAdded = $prodAddedFlower + $prodAddedExtract;
		$prodRemoved = $prodRemovedFlower + $prodRemovedExtract;
		$prodEstStock = $prodEstStockFlower + $prodEstStockExtract;
		$prodStock = $flowerWeight + $extractWeight;
		$stockDelta = $flowerDelta + $extractDelta;
		
		$dayprodAdded = $dayprodAddedFlower + $dayprodAddedExtract;
		$dayprodRemoved = $dayprodRemovedFlower + $dayprodRemovedExtract;
		$dayprodEstStock = $dayprodEstStockFlower + $dayprodEstStockExtract;
		$dayprodStock = $dayflowerWeight + $dayextractWeight;
		$daystockDelta = $dayflowerDelta + $dayextractDelta;

}		

			
		/****** SHIFT FIRST ******/
		
		// Look up today's sales by category
		$selectSalesFlowers = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = '1'";

		$result = mysql_query($selectSalesFlowers)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$flowerSalesToday = $row['SUM(d.amount)'];
			$flowerDispensed = $row['SUM(d.quantity)'];
			$flowerSalesTodayReal = $row['SUM(d.realQuantity)'];

		$selectSalesExtracts = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = '2'";

		$result = mysql_query($selectSalesExtracts)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$extractSalesToday = $row['SUM(d.amount)'];
			$extractDispensed = $row['SUM(d.quantity)'];
			$extractSalesTodayReal = $row['SUM(d.realQuantity)'];
			
		$soldTodayReal = $flowerSalesTodayReal + $extractSalesTodayReal;
			
		// Look up today's bar sales
		$selectBarSales = "SELECT SUM(s.amount), SUM(d.quantity) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime'";
	
		$result = mysql_query($selectBarSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$barSales = $row['SUM(s.amount)'];
			$barUnits = $row['SUM(d.quantity)'];
			
		// FLOWERS STASH
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		$stashedInt = mysql_query($selectStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedInt);
				$stashedInt = $row['SUM(m.quantity)'];
				
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		$unStashedInt = mysql_query($selectUnStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedInt);
				$unStashedInt = $row['SUM(m.quantity)'];
							
	
			$inStashInt = $stashedInt - $unStashedInt;
			$inStashIntFlower = $inStashInt;
			
					
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		$stashedExt = mysql_query($selectStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedExt);
				$stashedExt = $row['SUM(m.quantity)'];
						
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		$unStashedExt = mysql_query($selectUnStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($unStashedExt);
			$unStashedExt = $row['SUM(m.quantity)'];
						

		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExtFlower = $inStashExt;
		
		$flowerTotal = $flowerWeight + $inStashIntFlower + $inStashExtFlower;
		$flowerTotalWithoutShake = $flowerWeightWithoutShake + $inStashInt + $inStashExt;
		
		
		
		// EXTRACTS
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		$stashedInt = mysql_query($selectStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedInt);
				$stashedInt = $row['SUM(m.quantity)'];
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		$unStashedInt = mysql_query($selectUnStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
		$row = mysql_fetch_array($unStashedInt);
			$unStashedInt = $row['SUM(m.quantity)'];
						

		$inStashInt = $stashedInt - $unStashedInt;
		$inStashIntExtracts = $inStashInt;
		
				
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		
	
		$stashedExt = mysql_query($selectStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
			
	
		$row = mysql_fetch_array($stashedExt);
			$stashedExt = $row['SUM(m.quantity)'];
			

					
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		$unStashedExt = mysql_query($selectUnStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($unStashedExt);
			$unStashedExt = $row['SUM(m.quantity)'];
						

		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExtExtracts = $inStashExt;

		$extractTotal = $extractWeight + $inStashIntExtracts + $inStashExtExtracts;
		
		// Aggregates:
		$inStashIntTotal = $inStashIntFlower + $inStashIntExtracts;
		$inStashExtTotal = $inStashExtFlower + $inStashExtExtracts;
		$totalWithShake = $flowerTotal + $extractTotal;
		$totalWithoutShake = $flowerTotalWithoutShake + $extractTotal;
		
		
		
		/****** THEN DAY ******/
		
		// Look up today's sales by category
		$selectSalesFlowers = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.category = '1'";

		$result = mysql_query($selectSalesFlowers)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$dayflowerSalesToday = $row['SUM(d.amount)'];
			$dayflowerDispensed = $row['SUM(d.quantity)'];
			$dayflowerSalesTodayReal = $row['SUM(d.realQuantity)'];

		$selectSalesExtracts = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.category = '2'";

		$result = mysql_query($selectSalesExtracts)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$dayextractSalesToday = $row['SUM(d.amount)'];
			$dayextractDispensed = $row['SUM(d.quantity)'];
			$dayextractSalesTodayReal = $row['SUM(d.realQuantity)'];
			
		$daysoldTodayReal = $dayflowerSalesTodayReal + $dayextractSalesTodayReal;
			
		// Look up today's bar sales
		$selectBarSales = "SELECT SUM(s.amount), SUM(d.quantity) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime'";
	
		$result = mysql_query($selectBarSales)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$daybarSales = $row['SUM(s.amount)'];
			$daybarUnits = $row['SUM(d.quantity)'];
			
		// FLOWERS STASH
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		$stashedInt = mysql_query($selectStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedInt);
				$daystashedInt = $row['SUM(m.quantity)'];
				
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		$unStashedInt = mysql_query($selectUnStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedInt);
				$dayunStashedInt = $row['SUM(m.quantity)'];
							
	
			$dayinStashInt = $daystashedInt - $dayunStashedInt;
			$dayinStashIntFlower = $dayinStashInt;
			
					
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		$stashedExt = mysql_query($selectStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedExt);
				$daystashedExt = $row['SUM(m.quantity)'];
						
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		$unStashedExt = mysql_query($selectUnStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($unStashedExt);
			$dayunStashedExt = $row['SUM(m.quantity)'];
						

		$dayinStashExt = $daystashedExt - $dayunStashedExt;
		$dayinStashExtFlower = $dayinStashExt;
		
		$dayflowerTotal = $dayflowerWeight + $dayinStashIntFlower + $dayinStashExtFlower;
		$dayflowerTotalWithoutShake = $dayflowerWeightWithoutShake + $dayinStashInt + $dayinStashExt;
		
		
		
		// EXTRACTS
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		$stashedInt = mysql_query($selectStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedInt);
				$daystashedInt = $row['SUM(m.quantity)'];
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		$unStashedInt = mysql_query($selectUnStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
		$row = mysql_fetch_array($unStashedInt);
			$dayunStashedInt = $row['SUM(m.quantity)'];
						

		$dayinStashInt = $daystashedInt - $dayunStashedInt;
		$dayinStashIntExtracts = $dayinStashInt;
		
				
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		
	
		$stashedExt = mysql_query($selectStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
			
	
		$row = mysql_fetch_array($stashedExt);
			$daystashedExt = $row['SUM(m.quantity)'];
			

					
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		$unStashedExt = mysql_query($selectUnStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($unStashedExt);
			$dayunStashedExt = $row['SUM(m.quantity)'];
						

		$dayinStashExt = $daystashedExt - $dayunStashedExt;
		$dayinStashExtExtracts = $dayinStashExt;

		$dayextractTotal = $dayextractWeight + $dayinStashIntExtracts + $dayinStashExtExtracts;
		
		// Aggregates:
		$dayinStashIntTotal = $dayinStashIntFlower + $dayinStashIntExtracts;
		$dayinStashExtTotal = $dayinStashExtFlower + $dayinStashExtExtracts;
		$daytotalWithShake = $dayflowerTotal + $dayextractTotal;
		$daytotalWithoutShake = $dayflowerTotalWithoutShake + $dayextractTotal;		
		

		$openingLookup = "SELECT dayClosedNo FROM opening WHERE openingid = $dayopeningid";
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$dayClosedNo = $row['dayClosedNo'];
			
		$openingLookup = "SELECT shiftClosedNo FROM shiftopen WHERE openingid = $openingid";
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$shiftClosedNo = $row['shiftClosedNo'];
			
		if ($dayClosedNo > 0) {
		
			// Means part of the day has been closed already, so use UPDATE
			
			// Close shift first
			$realClosingtime = date('Y-m-d H:i:s');
			
			$closingid = $dayClosedNo;

		  	$query = sprintf("UPDATE shiftclose SET closingtime = '%s', shiftEnd = '%s', prodOpening = '%f', prodAdded = '%f', prodRemoved = '%f', prodEstStock = '%f', prodStock = '%f', stockDelta = '%f', prodStockFlower = '%f', prodStockExtract = '%f', prodOpeningFlower = '%f', prodOpeningExtract = '%f', prodAddedFlower = '%f', prodAddedExtract = '%f', prodRemovedFlower = '%f', prodRemovedExtract = '%f', prodEstStockFlower = '%f', prodEstStockExtract = '%f', stockDeltaFlower = '%f', stockDeltaExtract = '%f', closedby = '%d', intStash = '%f', extStash = '%f', totalWeight = '%f', totalNoShake = '%f', flowerintStash = '%f', flowerextStash = '%f', flowerweightNoShake = '%f', flowertotalWeight = '%f', flowertotalNoShake = '%f', extractintStash = '%f', extractextStash = '%f', extracttotalWeight = '%f', flowerDispensed = '%f', extractDispensed = '%f', soldTodayFlower = '%f', soldTodayExtract = '%f', soldtodayBar = '%f', unitsSoldBar = '%f', quantitySoldReal = '%f', soldTodayFlowerReal = '%f', soldTodayExtractReal = '%f' WHERE closingid = '%d';",
		  	$realClosingtime, $closingtime, $prodOpening, $prodAdded, $prodRemoved, $prodEstStock, $prodStock, $stockDelta, $flowerWeight, $extractWeight, $prodOpeningFlower, $prodOpeningExtract, $prodAddedFlower, $prodAddedExtract, $prodRemovedFlower, $prodRemovedExtract, $prodEstStockFlower, $prodEstStockExtract, $flowerDelta, $extractDelta, $_SESSION['user_id'], $inStashIntTotal, $inStashExtTotal, $totalWithShake, $totalWithoutShake, $inStashIntFlower, $inStashExtFlower, $flowerWeightWithoutShake, $flowerTotal, $flowerTotalWithoutShake, $inStashIntExtracts, $inStashExtExtracts, $extractTotal, $flowerDispensed, $extractDispensed, $flowerSalesToday, $extractSalesToday, $barSales, $barUnits, $soldTodayReal, $flowerSalesTodayReal, $extractSalesTodayReal, $shiftClosedNo);
		  	
		  
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
	
			$updateOpening = sprintf("UPDATE shiftopen SET disClosed = 2, disClosedAt = '%s' WHERE openingid = '%d';",
				$realClosingtime,
				mysql_real_escape_string($openingid)
				);
				
			mysql_query($updateOpening)
				or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());
				
				
			// Now close Day
			$realClosingtime2 = date('Y-m-d H:i:s', time() + 5);

		  	$query = sprintf("UPDATE closing SET closingtime = '%s', shiftEnd = '%s', prodOpening = '%f', prodAdded = '%f', prodRemoved = '%f', prodEstStock = '%f', prodStock = '%f', stockDelta = '%f', prodStockFlower = '%f', prodStockExtract = '%f', prodOpeningFlower = '%f', prodOpeningExtract = '%f', prodAddedFlower = '%f', prodAddedExtract = '%f', prodRemovedFlower = '%f', prodRemovedExtract = '%f', prodEstStockFlower = '%f', prodEstStockExtract = '%f', stockDeltaFlower = '%f', stockDeltaExtract = '%f', closedby = '%d', intStash = '%f', extStash = '%f', totalWeight = '%f', totalNoShake = '%f', flowerintStash = '%f', flowerextStash = '%f', flowerweightNoShake = '%f', flowertotalWeight = '%f', flowertotalNoShake = '%f', extractintStash = '%f', extractextStash = '%f', extracttotalWeight = '%f', flowerDispensed = '%f', extractDispensed = '%f', soldTodayFlower = '%f', soldTodayExtract = '%f', soldtodayBar = '%f', unitsSoldBar = '%f', quantitySoldReal = '%f', soldTodayFlowerReal = '%f', soldTodayExtractReal = '%f' WHERE closingid = '%d';",
		  	$realClosingtime2, $closingtime, $dayprodOpening, $dayprodAdded, $dayprodRemoved, $dayprodEstStock, $dayprodStock, $daystockDelta, $dayflowerWeight, $dayextractWeight, $dayprodOpeningFlower, $dayprodOpeningExtract, $dayprodAddedFlower, $dayprodAddedExtract, $dayprodRemovedFlower, $dayprodRemovedExtract, $dayprodEstStockFlower, $dayprodEstStockExtract, $dayflowerDelta, $dayextractDelta, $_SESSION['user_id'], $dayinStashIntTotal, $dayinStashExtTotal, $daytotalWithShake, $daytotalWithoutShake, $dayinStashIntFlower, $dayinStashExtFlower, $dayflowerWeightWithoutShake, $dayflowerTotal, $dayflowerTotalWithoutShake, $dayinStashIntExtracts, $dayinStashExtExtracts, $dayextractTotal, $dayflowerDispensed, $dayextractDispensed, $dayflowerSalesToday, $dayextractSalesToday, $daybarSales, $daybarUnits, $daysoldTodayReal, $dayflowerSalesTodayReal, $dayextractSalesTodayReal, $dayClosedNo);
			  	
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());

			$updateOpening = sprintf("UPDATE opening SET disClosed = 2, disClosedAt = '%s' WHERE openingid = '%d';",
				$realClosingtime2,
				mysql_real_escape_string($dayopeningid)
				);

			mysql_query($updateOpening)
				or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());
			
		
		} else {	
			
			// Close shift first
			$realClosingtime = date('Y-m-d H:i:s');
			
			// Query to add Closing - 37 arguments
			$query = sprintf("INSERT INTO shiftclose (closingtime, shiftEnd, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, stockDelta, prodStockFlower, prodStockExtract, prodOpeningFlower, prodOpeningExtract, prodAddedFlower, prodAddedExtract, prodRemovedFlower, prodRemovedExtract, prodEstStockFlower, prodEstStockExtract, stockDeltaFlower, stockDeltaExtract, closedby, intStash, extStash, totalWeight, totalNoShake, flowerintStash, flowerextStash, flowerweightNoShake, flowertotalWeight, flowertotalNoShake, extractintStash, extractextStash, extracttotalWeight, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, soldtodayBar, unitsSoldBar, quantitySoldReal, soldTodayFlowerReal, soldTodayExtractReal) VALUES ('%s', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
			$realClosingtime, $closingtime, $prodOpening, $prodAdded, $prodRemoved, $prodEstStock, $prodStock, $stockDelta, $flowerWeight, $extractWeight, $prodOpeningFlower, $prodOpeningExtract, $prodAddedFlower, $prodAddedExtract, $prodRemovedFlower, $prodRemovedExtract, $prodEstStockFlower, $prodEstStockExtract, $flowerDelta, $extractDelta, $_SESSION['user_id'], $inStashIntTotal, $inStashExtTotal, $totalWithShake, $totalWithoutShake, $inStashIntFlower, $inStashExtFlower, $flowerWeightWithoutShake, $flowerTotal, $flowerTotalWithoutShake, $inStashIntExtracts, $inStashExtExtracts, $extractTotal, $flowerDispensed, $extractDispensed, $flowerSalesToday, $extractSalesToday, $barSales, $barUnits, $soldTodayReal, $flowerSalesTodayReal, $extractSalesTodayReal);

		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
		$shiftClosedNo = mysql_insert_id();
		
		$updateOpening = sprintf("UPDATE shiftopen SET disClosed = 2, disClosedAt = '%s', shiftClosedNo = '%d' WHERE openingid = '%d';",
			$realClosingtime,
			mysql_real_escape_string($shiftClosedNo),
			mysql_real_escape_string($openingid)
			);
				
		mysql_query($updateOpening)
			or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
		

		// Now close Day
		$realClosingtime2 = date('Y-m-d H:i:s', time() + 5);
		
		$query = sprintf("INSERT INTO closing (closingtime, shiftEnd, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, stockDelta, prodStockFlower, prodStockExtract, prodOpeningFlower, prodOpeningExtract, prodAddedFlower, prodAddedExtract, prodRemovedFlower, prodRemovedExtract, prodEstStockFlower, prodEstStockExtract, stockDeltaFlower, stockDeltaExtract, closedby, intStash, extStash, totalWeight, totalNoShake, flowerintStash, flowerextStash, flowerweightNoShake, flowertotalWeight, flowertotalNoShake, extractintStash, extractextStash, extracttotalWeight, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, soldtodayBar, unitsSoldBar, quantitySoldReal, soldTodayFlowerReal, soldTodayExtractReal) VALUES ('%s', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
		$realClosingtime2, $closingtime, $dayprodOpening, $dayprodAdded, $dayprodRemoved, $dayprodEstStock, $dayprodStock, $daystockDelta, $dayflowerWeight, $dayextractWeight, $dayprodOpeningFlower, $dayprodOpeningExtract, $dayprodAddedFlower, $dayprodAddedExtract, $dayprodRemovedFlower, $dayprodRemovedExtract, $dayprodEstStockFlower, $dayprodEstStockExtract, $dayflowerDelta, $dayextractDelta, $_SESSION['user_id'], $dayinStashIntTotal, $dayinStashExtTotal, $daytotalWithShake, $daytotalWithoutShake, $dayinStashIntFlower, $dayinStashExtFlower, $dayflowerWeightWithoutShake, $dayflowerTotal, $dayflowerTotalWithoutShake, $dayinStashIntExtracts, $dayinStashExtExtracts, $dayextractTotal, $dayflowerDispensed, $dayextractDispensed, $dayflowerSalesToday, $dayextractSalesToday, $daybarSales, $daybarUnits, $daysoldTodayReal, $dayflowerSalesTodayReal, $dayextractSalesTodayReal);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
		$dayClosedNo = mysql_insert_id();
		
		$updateOpening = sprintf("UPDATE opening SET disClosed = 2, disClosedAt = '%s', dayClosedNo = '%d' WHERE openingid = '%d';",
			$realClosingtime,
			mysql_real_escape_string($dayClosedNo),
			mysql_real_escape_string($dayopeningid)
			);

		mysql_query($updateOpening)
			or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());
			
		}
		
		foreach($_SESSION['confirmedClose'] as $confirmedClose) {
			$name = $confirmedClose['name'];
			$category = $confirmedClose['category'];
			$productid = $confirmedClose['productid'];
			$purchaseid = $confirmedClose['purchaseid'];
			$weightToday = $confirmedClose['weightToday'];
			$addedToday = $confirmedClose['addedToday'];
			$soldToday = $confirmedClose['soldToday'];
			$takeoutsToday = $confirmedClose['takeoutsToday'];
			$estWeight = $confirmedClose['estWeight'];
			$weight = $confirmedClose['weight'];
			$prodclosecomment = $confirmedClose['prodclosecomment'];
			$shake = $confirmedClose['shake'];
			$growtype = $confirmedClose['growtype'];
			$breed2 = $confirmedClose['breed2'];
			$productStatus = $confirmedClose['productStatus'];
			$inMenu = $confirmedClose['inMenu'];
			
			$weightDelta = 0;
			$weightDelta = $weight - $estWeight;
			
			$weightWithoutShake = ($weight - ($weight * ($shake / 100)));
			
			$dayweightToday = $confirmedClose['dayweightToday'];
			$dayaddedToday = $confirmedClose['dayaddedToday'];
			$daysoldToday = $confirmedClose['daysoldToday'];
			$daytakeoutsToday = $confirmedClose['daytakeoutsToday'];
			$dayestWeight = $confirmedClose['dayestWeight'];
			
			$dayweightDelta = 0;
			$dayweightDelta = $weight - $dayestWeight;
			
			if ($category == 1) {

				/****** SHIFT FIRST ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$unStashedInt = $row['SUM(m.quantity)'];
									
			
					$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedExt);
					$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedExt);
					$unStashedExt = $row['SUM(m.quantity)'];
								
		
				$inStashExt = $stashedExt - $unStashedExt;
				
				$prodTotal = $weight + $inStashInt + $inStashExt;
				$prodTotalWithoutShake = $weightWithoutShake + $inStashInt + $inStashExt;

				/****** THEN DAY ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
					$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedExt);
					$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedExt);
					$dayunStashedExt = $row['SUM(m.quantity)'];
								
		
				$dayinStashExt = $daystashedExt - $dayunStashedExt;
				
				$dayprodTotal = $weight + $dayinStashInt + $dayinStashExt;
				$dayprodTotalWithoutShake = $weightWithoutShake + $dayinStashInt + $dayinStashExt;
					
			
			
		    	// Query to add to shiftclosedetails table - 12 arguments
				$query = sprintf("INSERT INTO shiftclosedetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $shiftClosedNo, $category, $productid, $purchaseid, $weightToday, $addedToday, $soldToday, $takeoutsToday, $weight, $estWeight, $weightDelta, $prodclosecomment, $shake, $inStashInt, $inStashExt, $weightWithoutShake, $prodTotal, $prodTotalWithoutShake, $inMenu);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
					
		    	// Query to add to closingdetails table - 12 arguments
				$query = sprintf("INSERT INTO closingdetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $dayClosedNo, $category, $productid, $purchaseid, $dayweightToday, $dayaddedToday, $daysoldToday, $daytakeoutsToday, $weight, $dayestWeight, $dayweightDelta, $prodclosecomment, $shake, $dayinStashInt, $dayinStashExt, $weightWithoutShake, $dayprodTotal, $dayprodTotalWithoutShake, $inMenu);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
		
			} else if ($category == '2') {
	
				/****** SHIFT FIRST ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedInt);
					$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedInt);
					$unStashedInt = $row['SUM(m.quantity)'];
									
			
				$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedExt);
					$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedExt);
					$unStashedExt = $row['SUM(m.quantity)'];
									
			
				$inStashExt = $stashedExt - $unStashedExt;
					
				$prodTotal = $weight + $inStashInt + $inStashExt;

			
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedInt);
					$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedInt);
					$unStashedInt = $row['SUM(m.quantity)'];
									
			
				$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedExt);
					$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedExt);
					$unStashedExt = $row['SUM(m.quantity)'];
									
			
				$inStashExt = $stashedExt - $unStashedExt;
					
				$prodTotal = $weight + $inStashInt + $inStashExt;
				
				
				
				
				
				
				
				/****** THEN DAY ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedInt);
					$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedInt);
					$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
				$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedExt);
					$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedExt);
					$dayunStashedExt = $row['SUM(m.quantity)'];
									
			
				$dayinStashExt = $daystashedExt - $dayunStashedExt;
					
				$dayprodTotal = $weight + $dayinStashInt + $dayinStashExt;

			
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedInt);
					$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedInt);
					$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
				$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedExt);
					$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedExt);
					$dayunStashedExt = $row['SUM(m.quantity)'];
									
			
				$dayinStashExt = $daystashedExt - $dayunStashedExt;
					
				$dayprodTotal = $weight + $dayinStashInt + $dayinStashExt;				
				
				
				
		    	// Query to add to shiftclosedetails table
				$query = sprintf("INSERT INTO shiftclosedetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $shiftClosedNo, $category, $productid, $purchaseid, $weightToday, $addedToday, $soldToday, $takeoutsToday, $weight, $estWeight, $weightDelta, $prodclosecomment, $shake, $inStashInt, $inStashExt, $weightWithoutShake, $prodTotal, $prodTotal, $inMenu);
		  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
						
		    	// Query to add to closingdetails table
				$query = sprintf("INSERT INTO closingdetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $dayClosedNo, $category, $productid, $purchaseid, $dayweightToday, $dayaddedToday, $daysoldToday, $daytakeoutsToday, $weight, $dayestWeight, $dayweightDelta, $prodclosecomment, $shake, $dayinStashInt, $dayinStashExt, $weightWithoutShake, $dayprodTotal, $dayprodTotal, $inMenu);
		  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
				
	}

		
} // Product loop ends
		
	
		// For each other cat, look up units sold 
		$selectCats = "SELECT id, name from categories ORDER by id ASC";
	
		$resultCats = mysql_query($selectCats)
			or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
	
		while ($category = mysql_fetch_array($resultCats)) {
			
			$categoryid = $category['id'];
			$name = $category['name'];
			
			$selectProducts = "SELECT pr.productid, p.purchaseid, p.inMenu from products pr, purchases p WHERE p.category = $categoryid AND pr.productid = p.productid AND (p.closedAt IS NULL OR p.closingDate BETWEEN '$dayopeningtime' AND '$closingtime') ORDER BY pr.name ASC;";
		
			$resultProducts = mysql_query($selectProducts)
				or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
				
	
			while ($product = mysql_fetch_array($resultProducts)) {
				
				$productid = $product['productid'];
				$purchaseid = $product['purchaseid'];
				$inMenu = $product['inMenu'];
				
		
				/****** SHIFT FIRST ******/
				
				$selectSalesProducts = "SELECT SUM(d.quantity) FROM sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime' AND purchaseid = $purchaseid";
						
				$result = mysql_query($selectSalesProducts)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
				$row = mysql_fetch_array($result);
					$unitsToday = $row['SUM(d.quantity)'];
					
					
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$unStashedInt = $row['SUM(m.quantity)'];
									
			
					$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedExt);
						$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedExt);
						$unStashedExt = $row['SUM(m.quantity)'];
									
			
					$inStashExt = $stashedExt - $unStashedExt;
					
					
				/****** THEN DAY ******/
				
				$selectSalesProducts = "SELECT SUM(d.quantity) FROM sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND purchaseid = $purchaseid";
						
				$result = mysql_query($selectSalesProducts)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
				$row = mysql_fetch_array($result);
					$dayunitsToday = $row['SUM(d.quantity)'];
					
					
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
					$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedExt);
						$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedExt);
						$dayunStashedExt = $row['SUM(m.quantity)'];
									
			
					$dayinStashExt = $daystashedExt - $dayunStashedExt;
					
					
		    	// Query to add to shiftclosedetails table
				$query = sprintf("INSERT INTO shiftclosedetails (closingid, category, productid, purchaseid, soldToday, inMenu, intStash, extStash) VALUES ('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d');",
			  			 $shiftClosedNo, $categoryid, $productid, $purchaseid, $unitsToday, $inMenu, $inStashInt, $inStashExt);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
					
		    	// Query to add to closingdetails table
				$query = sprintf("INSERT INTO closingdetails (closingid, category, productid, purchaseid, soldToday, inMenu, intStash, extStash) VALUES ('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d');",
			  			 $dayClosedNo, $categoryid, $productid, $purchaseid, $dayunitsToday, $inMenu, $dayinStashInt, $dayinStashExt);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());

			}
		}
		
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['dispensary-closed-successfully'];
		header("Location: close-shift-and-day.php");
		exit();

		
	}
	## ON PAGE SUBMISSION END ##
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

		
	// Did the user choose not to weigh products?
	if ($_POST['noWeighing'] == 'yes') {
		
	$confirmLeave = <<<EOD
    $(document).ready(function() {   	    
document.querySelector('button').addEventListener("click", function(){
    window.btn_clicked = true;      //set btn_clicked to true
});

$(window).bind('beforeunload', function(){
    if(!window.btn_clicked){
        return "{$lang['closeday-leavepage']}";
    }
});
  }); // end ready
EOD;

	pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "step6", $lang['closeday-dis-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
	$_SESSION['daycloseProduct'] = $_POST['daycloseProduct'];
	
	echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='?saveDispensary' method='POST'><br />";
	echo "<input type='hidden' name='productConfirm' value='yes'><br />";
	
	$i=0;
		foreach($_POST['daycloseProduct'] as $prodClose) {
			
			$name = $prodClose['name'];
			$category = $prodClose['category'];
			$productid = $prodClose['productid'];
			$purchaseid = $prodClose['purchaseid'];
			$fullWeight = $prodClose['weight'];
			$shake = $prodClose['shake'];
			$growtype = $prodClose['growtype'];
			$breed2 = $prodClose['breed2'];
			$closed = $prodClose['closed'];
			$inMenu = $prodClose['inMenu'];
			$tupperWeight = $prodClose['tupperWeight'];
			
			$weight = $fullWeight - $tupperWeight;
			
			
			if ($closed == 'yes') {
				$disableOrNot = "disabled style='color: red'";
				$productStatus = "Closed";
				$radioDisable = "disabled";
			} else if ($inMenu == 0) {
				$disableOrNot = "";
				$productStatus = "Not in menu";
				$radioDisable = "";
			} else {
				$disableOrNot = "";
				$productStatus = "In menu";
				$radioDisable = "";
			}
						
			$required0 = '';
			$required25 = '';
			$required50 = '';
			$required75 = '';
			
			if ($shake == '0') {
				$required0 = 'checked';
			} else if ($shake == '25') {
				$required25 = 'checked';
			} else if ($shake == '50') {
				$required50 = 'checked';
			} else if ($shake == '75') {
				$required75 = 'checked';
			}

			
			/****** SHIFT FIRST ******/
			
			// Look up todays sales
			$selectSales = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";

			$result = mysql_query($selectSales)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$soldToday = $row['SUM(d.realQuantity)'];
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$openingtime' AND '$closingtime' ORDER by purchaseDate DESC";

			$result = mysql_query($selectPurchase)
				or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$newPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM shiftopendetails d, shiftopen o WHERE o.openingid = $openingid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
			
			$result = mysql_query($openingLookup)
				or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
		
			// Retrieve todays opening data
			$row = mysql_fetch_array($result);
				$weightToday = $row['weight'];
			
			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
		
			$additions = mysql_query($selectAdditions)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($additions);
				$addedToday = $row['SUM(quantity)'];
				
			$removals = mysql_query($selectRemovals)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($removals);
				$takeoutsToday = $row['SUM(quantity)'];
					
			// Exception if there was no weight this morning, we use the new product weight
			if (($weightToday == 0)) {
				$addedToday = $newPurchaseWeight + $addedToday;
			}
				
			// Calculate estimated weight and weight Delta:
			$estWeight = $weightToday - $soldToday - $takeoutsToday + $addedToday;
			$weightDelta = $weight - $estWeight;
					
			
			/****** NOW DAY ******/
			
			// Look up todays sales
			$selectSales = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE s.saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";

			$result = mysql_query($selectSales)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$daysoldToday = $row['SUM(d.realQuantity)'];
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$dayopeningtime' AND '$closingtime' ORDER by purchaseDate DESC";

			$result = mysql_query($selectPurchase)
				or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$daynewPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE o.openingid = $dayopeningid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
			
			$result = mysql_query($openingLookup)
				or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
		
			// Retrieve todays opening data
			$row = mysql_fetch_array($result);
				$dayweightToday = $row['weight'];
			

			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
		
			$additions = mysql_query($selectAdditions)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($additions);
				$dayaddedToday = $row['SUM(quantity)'];
				
		
			$removals = mysql_query($selectRemovals)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($removals);
				$daytakeoutsToday = $row['SUM(quantity)'];
					
			// Exception if there was no weight this morning, we use the new product weight
			if (($dayweightToday == 0)) {
				$dayaddedToday = $daynewPurchaseWeight + $dayaddedToday;
			}
				
			// Calculate estimated weight and weight Delta:
			$dayestWeight = $dayweightToday - $daysoldToday - $daytakeoutsToday + $dayaddedToday;
			$dayweightDelta = $weight - $dayestWeight;
			
			
			
			
			
			
			// Determine colour of weight delta field
			if ($weightDelta < 0) {
				$deltaColour = ' negative';
			} else if ($weightDelta > 0) {
				$deltaColour = ' positive';
			} else {
				$deltaColour = '';
			}
			
			$i++;
			
			if ($category == 1) {
				
				if ($flowerheader != 1) {
					echo "<h3 class='title'>{$lang['global-flowerscaps']}</h3>";
				}		
			$flowerheader = '1';
			
			$product_row = sprintf("
	
<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#fullWeight%d').val();
          var b = $('#estWeight%d').val();
          var c = $('#tupperWeight%d').val();
          var total = (a - c) - b;
          var roundedtotal = total.toFixed(2);
          $('#weightDelta%d').val(roundedtotal);
          
          var realTotal = a - c;
          var roundedrealTotal = realTotal.toFixed(2);
          $('#weight%d').val(roundedrealTotal);

          var wdelta%d = $('#weightDelta%d').val();
          
          if (wdelta%d < '0.00') {
          	$('#weightDelta%d').css('color', 'red');
      	  }
      	  if (wdelta%d > '0.00') {
          	$('#weightDelta%d').css('color', 'green');
      	  }
    }

        $('#weight%d').bind('keypress keyup blur', compute);
        $('#fullWeight%d').bind('keypress keyup blur', compute);
        $('#tupperWeight%d').bind('keypress keyup blur', compute);
        

  }); // end ready
</script>
		<div class='productbox'>
		 <h3>%s %s</h3>
		 %s<br />
		 <table>
		  <tr>
		   <td>{$lang['closeday-openingweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-dispensed']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-takeouts']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}:</td>
		   <td>
		    <input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit' value='%0.02f' readonly />
		    <input type='hidden' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit' value='%0.02f' step='0.01' readonly />
		   </td>
		  </tr>
		 </table><br />

		 {$lang['global-comment']}?<br />
		 <textarea name='confirmedClose[%d][prodclosecomment]'></textarea>
		</div>
  	   <input type='hidden' name='confirmedClose[%d][name]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][category]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][productid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][growtype]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][breed2]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][productStatus]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][dayweightToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayaddedToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daysoldToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daytakeoutsToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayestWeight]' value='%f' />",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $prodClose['name'], $breed2, $growtype, $i, $weightToday, $i, $addedToday, $i, $soldToday, $i, $takeoutsToday, $i, $i, $estWeight, $i, $i, $estWeight,  $i, $i, $name, $i, $category, $i, $productid, $i, $purchaseid, $i, $growtype, $i, $breed2, $i, $productStatus, $i, $inMenu,
	  $i, $dayweightToday, $i, $dayaddedToday, $i, $daysoldToday, $i, $daytakeoutsToday, $i, $dayestWeight
	  );
	  
  } else if ($category == '2') {
	  
		if ($extractheader != 1) {
			echo "<h3 class='title'>{$lang['global-extractscaps']}</h3>";
		}		
	$extractheader = '1';
	
	$product_row = sprintf("
	
<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#fullWeight%d').val();
          var b = $('#estWeight%d').val();
          var c = $('#tupperWeight%d').val();
          var total = (a - c) - b;
          var roundedtotal = total.toFixed(2);
          $('#weightDelta%d').val(roundedtotal);
          
          var realTotal = a - c;
          var roundedrealTotal = realTotal.toFixed(2);
          $('#weight%d').val(roundedrealTotal);

          var wdelta%d = $('#weightDelta%d').val();
          
          if (wdelta%d < '0.00') {
          	$('#weightDelta%d').css('color', 'red');
      	  }
      	  if (wdelta%d > '0.00') {
          	$('#weightDelta%d').css('color', 'green');
      	  }
    }

        $('#weight%d').bind('keypress keyup blur', compute);
        $('#fullWeight%d').bind('keypress keyup blur', compute);
        $('#tupperWeight%d').bind('keypress keyup blur', compute);
        

  }); // end ready
</script>
		<div class='productbox'>
		 <h3>%s</h3>
		 <table>
		  <tr>
		   <td>{$lang['closeday-openingweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-dispensed']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-takeouts']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}:</td>
		   <td>
		    <input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit' value='%0.02f' readonly />
		    <input type='hidden' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit' value='%0.02f' step='0.01' readonly />
		   </td>
		  </tr>
		 </table><br />
		   {$lang['global-comment']}?<br />
		   <textarea name='confirmedClose[%d][prodclosecomment]'></textarea>
		</div>
  	   <input type='hidden' name='confirmedClose[%d][name]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][category]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][productid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][productStatus]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][dayweightToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayaddedToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daysoldToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daytakeoutsToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayestWeight]' value='%f' />",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $prodClose['name'], $i, $weightToday, $i, $addedToday, $i, $soldToday, $i, $takeoutsToday, $i, $i, $estWeight, $i, $i, $estWeight, $i, $i, $name, $i, $category, $i, $productid, $i, $purchaseid, $i, $productStatus, $i, $inMenu, $i, $dayweightToday, $i, $dayaddedToday, $i, $daysoldToday, $i, $daytakeoutsToday, $i, $dayestWeight
	  );
	    }
	  echo $product_row;
	  
		// End loop for each product
		}
		
		echo "<input type='hidden' name='step3' value='complete' />";
 		echo "<button name='oneClick' type='submit'>{$lang['global-confirm']}</button>";
		echo "</form>";
		displayFooter();
		exit();
		
		## FORM INPUT END ##
		
	} else if ($_POST['closingConfirm'] == 'yes') {
		
	$confirmLeave = <<<EOD
    $(document).ready(function() {   	    
document.querySelector('button').addEventListener("click", function(){
    window.btn_clicked = true;      //set btn_clicked to true
});

$(window).bind('beforeunload', function(){
    if(!window.btn_clicked){
        return "{$lang['closeday-leavepage']}";
    }
});
  }); // end ready
EOD;

	pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "step6", $lang['closeday-dis-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
	$_SESSION['daycloseProduct'] = $_POST['daycloseProduct'];
	
	echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='?saveDispensary' method='POST'><br />";
	echo "<input type='hidden' name='productConfirm' value='yes'><br />";
	
	$i=0;
		foreach($_POST['daycloseProduct'] as $prodClose) {
			$name = $prodClose['name'];
			$category = $prodClose['category'];
			$productid = $prodClose['productid'];
			$purchaseid = $prodClose['purchaseid'];
			$fullWeight = $prodClose['weight'];
			$shake = $prodClose['shake'];
			$growtype = $prodClose['growtype'];
			$breed2 = $prodClose['breed2'];
			$closed = $prodClose['closed'];
			$inMenu = $prodClose['inMenu'];
			$tupperWeight = $prodClose['tupperWeight'];
			
			$weight = $fullWeight - $tupperWeight;
			
			
			if ($closed == 'yes') {
				$disableOrNot = "disabled style='color: red'";
				$productStatus = "Closed";
				$radioDisable = "disabled";
			} else if ($inMenu == 0) {
				$disableOrNot = "";
				$productStatus = "Not in menu";
				$radioDisable = "";
			} else {
				$disableOrNot = "";
				$productStatus = "In menu";
				$radioDisable = "";
			}
						
			$required0 = '';
			$required25 = '';
			$required50 = '';
			$required75 = '';
			
			if ($shake == '0') {
				$required0 = 'checked';
			} else if ($shake == '25') {
				$required25 = 'checked';
			} else if ($shake == '50') {
				$required50 = 'checked';
			} else if ($shake == '75') {
				$required75 = 'checked';
			}

			
			/****** SHIFT FIRST ******/
			
			// Look up todays sales
			$selectSales = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";

			$result = mysql_query($selectSales)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$soldToday = $row['SUM(d.realQuantity)'];
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$openingtime' AND '$closingtime' ORDER by purchaseDate DESC";

			$result = mysql_query($selectPurchase)
				or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$newPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM shiftopendetails d, shiftopen o WHERE o.openingid = $openingid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
			
			$result = mysql_query($openingLookup)
				or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
		
			// Retrieve todays opening data
			$row = mysql_fetch_array($result);
				$weightToday = $row['weight'];
			
			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
		
			$additions = mysql_query($selectAdditions)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($additions);
				$addedToday = $row['SUM(quantity)'];
				
		
			$removals = mysql_query($selectRemovals)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($removals);
				$takeoutsToday = $row['SUM(quantity)'];
			
			// Exception if there was no weight this morning, we use the new product weight
			if (($weightToday == 0)) {
				$addedToday = $newPurchaseWeight + $addedToday;
			}
				
			// Calculate estimated weight and weight Delta:
			$estWeight = $weightToday - $soldToday - $takeoutsToday + $addedToday;
			$weightDelta = $weight - $estWeight;
			
			
			
			/****** THEN DAY ******/
			
			// Look up todays sales
			$selectSales = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE s.saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";

			$result = mysql_query($selectSales)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$daysoldToday = $row['SUM(d.realQuantity)'];
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$dayopeningtime' AND '$closingtime' ORDER by purchaseDate DESC";

			$result = mysql_query($selectPurchase)
				or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$daynewPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE o.openingid = $dayopeningid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
			
			$result = mysql_query($openingLookup)
				or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
		
			// Retrieve todays opening data
			$row = mysql_fetch_array($result);
				$dayweightToday = $row['weight'];
			
			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
		
			$additions = mysql_query($selectAdditions)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($additions);
				$dayaddedToday = $row['SUM(quantity)'];
		
			$removals = mysql_query($selectRemovals)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($removals);
				$daytakeoutsToday = $row['SUM(quantity)'];
			
			// Exception if there was no weight this morning, we use the new product weight
			if (($dayweightToday == 0)) {
				$dayaddedToday = $daynewPurchaseWeight + $dayaddedToday;
			}
			
			// Calculate estimated weight and weight Delta:
			$dayestWeight = $dayweightToday - $daysoldToday - $daytakeoutsToday + $dayaddedToday;
			$dayweightDelta = $weight - $dayestWeight;
			
			
			
			
	// Determine colour of weight delta field
	if ($weightDelta < 0) {
		$deltaColour = ' negative';
	} else if ($weightDelta > 0) {
		$deltaColour = ' positive';
	} else {
		$deltaColour = '';
	}
	
	$i++;
	
	if ($category == 1) {
		
		if ($flowerheader != 1) {
			echo "<h3 class='title'>{$lang['global-flowerscaps']}</h3>";
		}		
	$flowerheader = '1';

	$product_row = sprintf("
	
<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#fullWeight%d').val();
          var b = $('#estWeight%d').val();
          var c = $('#tupperWeight%d').val();
          var total = (a - c) - b;
          var roundedtotal = total.toFixed(2);
          $('#weightDelta%d').val(roundedtotal);
          
          var realTotal = a - c;
          var roundedrealTotal = realTotal.toFixed(2);
          $('#weight%d').val(roundedrealTotal);

          var wdelta%d = $('#weightDelta%d').val();
          
          if (wdelta%d < '0.00') {
          	$('#weightDelta%d').css('color', 'red');
      	  }
      	  if (wdelta%d > '0.00') {
          	$('#weightDelta%d').css('color', 'green');
      	  }
    }

        $('#weight%d').bind('keypress keyup blur', compute);
        $('#fullWeight%d').bind('keypress keyup blur', compute);
        $('#tupperWeight%d').bind('keypress keyup blur', compute);
        

  }); // end ready
</script>
		<div class='productbox'>
		 <h3>%s %s</h3>
		 %s<br />
		 <table>
		  <tr>
		   <td>{$lang['closeday-openingweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-dispensed']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-takeouts']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		 <tr>
		  <td colspan='2'>&nbsp;</td>
		 </tr>
		 <tr>
		  <td>{$lang['weightnow']}:</td>
		  <td><input type='number' lang='nb' name='confirmedClose[%d][fullWeight]' id='fullWeight%d' class='fourDigit' value='%0.02f' step='0.01' /></td>
		 </tr>
		 <tr>
		  <td>- {$lang['jar-weight']}:</td>
		  <td><input type='number' lang='nb' name='confirmedClose[%d][tupperWeight]' id='tupperWeight%d' class='fourDigit red' value='%0.02f' step='0.01' /></td>
		 </tr>
		  <tr>
		   <td>{$lang['add-realweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit' value='%0.02f' step='0.01' %s readonly /></td>
		  </tr>
		  <tr>
		   <td><strong>{$lang['global-delta']}:</strong></td>
		   <td><strong><input type='number' lang='nb' name='confirmedClose[%d][weightDelta]' id='weightDelta%d' class='fourDigit%s' value='%0.02f' step='0.01' readonly /></strong></td>
		  </tr>
		 </table><br />
		 {$lang['global-shake']}:<br />
    	<input type='radio' name='confirmedClose[%d][shake]' value='0' style='margin-left: 5px; width: 12px;' %s %s>0%%</input><br />
    	<input type='radio' name='confirmedClose[%d][shake]' value='25' style='margin-left: 5px; width: 12px;' %s %s>25%%</input><br />
    	<input type='radio' name='confirmedClose[%d][shake]' value='50' style='margin-left: 5px; width: 12px;' %s %s>50%%</input><br />
    <input type='radio' name='confirmedClose[%d][shake]' value='75' style='margin-left: 5px; width: 12px;' %s %s>75%%</input><br /><br />

		 {$lang['global-comment']}?<br />
		 <textarea name='confirmedClose[%d][prodclosecomment]'></textarea>
		</div>
  	   <input type='hidden' name='confirmedClose[%d][name]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][category]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][productid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][growtype]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][breed2]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][productStatus]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][dayweightDelta]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayweightToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayaddedToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daysoldToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daytakeoutsToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayestWeight]' value='%f' />",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $prodClose['name'], $breed2, $growtype, $i, $weightToday, $i, $addedToday, $i, $soldToday, $i, $takeoutsToday, $i, $i, $estWeight, $i, $i, $fullWeight, $i, $i, $tupperWeight, $i, $i, $weight, $disableOrNot, $i, $i, $deltaColour, $weightDelta, $i, $required0, $radioDisable, $i, $required25, $radioDisable, $i, $required50, $radioDisable, $i, $required75, $radioDisable, $i, $i, $name, $i, $category, $i, $productid, $i, $purchaseid, $i, $growtype, $i, $breed2, $i, $productStatus, $i, $inMenu, $i, $dayweightDelta, $i, $dayweightToday, $i, $dayaddedToday, $i, $daysoldToday, $i, $daytakeoutsToday, $i, $dayestWeight
	  );
	  
  } else if ($category == '2') {
	  
		if ($extractheader != 1) {
			echo "<h3 class='title'>{$lang['global-extractscaps']}</h3>";
		}		
	$extractheader = '1';
	
	$product_row = sprintf("
	
<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#fullWeight%d').val();
          var b = $('#estWeight%d').val();
          var c = $('#tupperWeight%d').val();
          var total = (a - c) - b;
          var roundedtotal = total.toFixed(2);
          $('#weightDelta%d').val(roundedtotal);
          
          var realTotal = a - c;
          var roundedrealTotal = realTotal.toFixed(2);
          $('#weight%d').val(roundedrealTotal);

          var wdelta%d = $('#weightDelta%d').val();
          
          if (wdelta%d < '0.00') {
          	$('#weightDelta%d').css('color', 'red');
      	  }
      	  if (wdelta%d > '0.00') {
          	$('#weightDelta%d').css('color', 'green');
      	  }
    }

        $('#weight%d').bind('keypress keyup blur', compute);
        $('#fullWeight%d').bind('keypress keyup blur', compute);
        $('#tupperWeight%d').bind('keypress keyup blur', compute);
        

  }); // end ready
</script>
		<div class='productbox'>
		 <h3>%s</h3>
		 <table>
		  <tr>
		   <td>{$lang['closeday-openingweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-dispensed']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-takeouts']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		 <tr>
		  <td colspan='2'>&nbsp;</td>
		 </tr>
		 <tr>
		  <td>{$lang['weightnow']}:</td>
		  <td><input type='number' lang='nb' name='confirmedClose[%d][fullWeight]' id='fullWeight%d' class='fourDigit' value='%0.02f' step='0.01' /></td>
		 </tr>
		 <tr>
		  <td>- {$lang['jar-weight']}:</td>
		  <td><input type='number' lang='nb' name='confirmedClose[%d][tupperWeight]' id='tupperWeight%d' class='fourDigit red' value='%0.02f' step='0.01' /></td>
		 </tr>
		  <tr>
		   <td>{$lang['add-realweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit' value='%0.02f' step='0.01' %s readonly /><br />
		  </tr>
		  <tr>
		   <td><strong>{$lang['global-delta']}:</strong></td>
		   <td><strong><input type='number' lang='nb' name='confirmedClose[%d][weightDelta]' id='weightDelta%d' class='fourDigit%s' value='%0.02f' step='0.01' readonly /></strong><br />
		  </tr>
		 </table><br />
		   {$lang['global-comment']}?<br />
		   <textarea name='confirmedClose[%d][prodclosecomment]'></textarea>
		</div>
  	   <input type='hidden' name='confirmedClose[%d][name]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][category]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][productid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][productStatus]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][dayweightDelta]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayweightToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayaddedToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daysoldToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daytakeoutsToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayestWeight]' value='%f' />",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $prodClose['name'], $i, $weightToday, $i, $addedToday, $i, $soldToday, $i, $takeoutsToday, $i, $i, $estWeight, $i, $i, $fullWeight, $i, $i, $tupperWeight, $i, $i, $weight, $disableOrNot, $i, $i, $deltaColour, $weightDelta, $i, $i, $name, $i, $category, $i, $productid, $i, $purchaseid, $i, $productStatus, $i, $inMenu, $i, $dayweightDelta, $i, $dayweightToday, $i, $dayaddedToday, $i, $daysoldToday, $i, $daytakeoutsToday, $i, $dayestWeight
	  );
	    }
	  echo $product_row;
	  
		// End loop for each product
		}
		
		echo "<input type='hidden' name='step3' value='complete' />";
 		echo "<button name='oneClick' type='submit'>{$lang['global-confirm']}</button>";
		echo "</form>";
		displayFooter();
		exit();
		
		## FORM INPUT END ##
		
	} else if ($_POST['step3'] != 'complete') {
		handleError($lang['global-fivenotcomplete'],"");
	}
	
} else {
	
	// If the page re-submitted, let's save Closing values for Reception! Also save Opening to 2.
	
	if (isset($_GET['saveDispensary'])) {
		
		// Retrieve variables for CLOSING insert
		$_SESSION['confirmedClose'] = $_POST['confirmedClose'];

		foreach($_SESSION['confirmedClose'] as $confirmedCloseCalc) {

			// Common
			$weight = $confirmedCloseCalc['weight'];
			$shake = $confirmedCloseCalc['shake'];
			$category = $confirmedCloseCalc['category'];
			
			// Shift
			$weightToday = $confirmedCloseCalc['weightToday'];
			$addedToday = $confirmedCloseCalc['addedToday'];
			$soldToday = $confirmedCloseCalc['soldToday'];
			$takeoutsToday = $confirmedCloseCalc['takeoutsToday'];
			$estWeight = $confirmedCloseCalc['estWeight'];
			$weightDelta = $weight - $estWeight;
			
			// Day
			$dayweightToday = $confirmedCloseCalc['dayweightToday'];
			$dayaddedToday = $confirmedCloseCalc['dayaddedToday'];
			$daysoldToday = $confirmedCloseCalc['daysoldToday'];
			$daytakeoutsToday = $confirmedCloseCalc['daytakeoutsToday'];
			$dayestWeight = $confirmedCloseCalc['dayestWeight'];
			$dayweightDelta = $weight - $dayestWeight;

			if ($category == '1') {
				
				$prodOpeningFlower = $prodOpeningFlower + $weightToday;
				$prodAddedFlower = $prodAddedFlower + $addedToday;
				$prodRemovedFlower = $prodRemovedFlower + $takeoutsToday;
				$prodEstStockFlower = $prodEstStockFlower + $estWeight;
				$flowerWeight = $flowerWeight + $weight;
				$flowerDelta = $flowerDelta + $weightDelta;
				$weightWithoutShake = ($weight - ($weight * ($shake / 100)));
				$flowerWeightWithoutShake = $flowerWeightWithoutShake + $weightWithoutShake;
				
				$dayprodOpeningFlower = $dayprodOpeningFlower + $dayweightToday;
				$dayprodAddedFlower = $dayprodAddedFlower + $dayaddedToday;
				$dayprodRemovedFlower = $dayprodRemovedFlower + $daytakeoutsToday;
				$dayprodEstStockFlower = $dayprodEstStockFlower + $dayestWeight;
				$dayflowerWeight = $dayflowerWeight + $weight;
				$dayflowerDelta = $dayflowerDelta + $dayweightDelta;

			} else if ($category == '2') {
				
				$prodOpeningExtract = $prodOpeningExtract + $weightToday;
				$prodAddedExtract = $prodAddedExtract + $addedToday;
				$prodRemovedExtract = $prodRemovedExtract + $takeoutsToday;
				$prodEstStockExtract = $prodEstStockExtract + $estWeight;
				$extractWeight = $extractWeight + $weight;
				$extractDelta = $extractDelta + $weightDelta;
				
				$dayprodOpeningExtract = $dayprodOpeningExtract + $dayweightToday;
				$dayprodAddedExtract = $dayprodAddedExtract + $dayaddedToday;
				$dayprodRemovedExtract = $dayprodRemovedExtract + $daytakeoutsToday;
				$dayprodEstStockExtract = $dayprodEstStockExtract + $dayestWeight;
				$dayextractWeight = $dayextractWeight + $weight;
				$dayextractDelta = $dayextractDelta + $dayweightDelta;
				
			}

		$prodOpening = $prodOpeningFlower + $prodOpeningExtract;
		$prodAdded = $prodAddedFlower + $prodAddedExtract;
		$prodRemoved = $prodRemovedFlower + $prodRemovedExtract;
		$prodEstStock = $prodEstStockFlower + $prodEstStockExtract;
		$prodStock = $flowerWeight + $extractWeight;
		$stockDelta = $flowerDelta + $extractDelta;
		
		$dayprodAdded = $dayprodAddedFlower + $dayprodAddedExtract;
		$dayprodRemoved = $dayprodRemovedFlower + $dayprodRemovedExtract;
		$dayprodEstStock = $dayprodEstStockFlower + $dayprodEstStockExtract;
		$dayprodStock = $dayflowerWeight + $dayextractWeight;
		$daystockDelta = $dayflowerDelta + $dayextractDelta;

}		

			
		/****** SHIFT FIRST ******/
		
		// Look up today's sales by category	
		$selectSalesFlowers = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = '1'";

		$result = mysql_query($selectSalesFlowers)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$flowerSalesToday = $row['SUM(d.amount)'];
			$flowerDispensed = $row['SUM(d.quantity)'];

		$selectSalesExtracts = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = '2'";

		$result = mysql_query($selectSalesExtracts)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$extractSalesToday = $row['SUM(d.amount)'];
			$extractDispensed = $row['SUM(d.quantity)'];
			
			
		// Look up today's bar sales
		$selectBarSales = "SELECT SUM(s.amount), SUM(d.quantity) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime'";
	
		$result = mysql_query($selectBarSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$barSales = $row['SUM(s.amount)'];
			$barUnits = $row['SUM(d.quantity)'];
			
		// FLOWERS STASH
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		$stashedInt = mysql_query($selectStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedInt);
				$stashedInt = $row['SUM(m.quantity)'];
				
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		$unStashedInt = mysql_query($selectUnStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedInt);
				$unStashedInt = $row['SUM(m.quantity)'];
							
	
			$inStashInt = $stashedInt - $unStashedInt;
			$inStashIntFlower = $inStashInt;
			
					
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		$stashedExt = mysql_query($selectStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedExt);
				$stashedExt = $row['SUM(m.quantity)'];
						
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		$unStashedExt = mysql_query($selectUnStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
		$row = mysql_fetch_array($unStashedExt);
			$unStashedExt = $row['SUM(m.quantity)'];
						

		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExtFlower = $inStashExt;
		
		$flowerTotal = $flowerWeight + $inStashIntFlower + $inStashExtFlower;
		$flowerTotalWithoutShake = $flowerWeightWithoutShake + $inStashInt + $inStashExt;
			
	
			
		// EXTRACTS
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		$stashedInt = mysql_query($selectStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedInt);
				$stashedInt = $row['SUM(m.quantity)'];
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		$unStashedInt = mysql_query($selectUnStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
		$row = mysql_fetch_array($unStashedInt);
			$unStashedInt = $row['SUM(m.quantity)'];
						

		$inStashInt = $stashedInt - $unStashedInt;
		$inStashIntExtracts = $inStashInt;
			
		
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		
	
		$stashedExt = mysql_query($selectStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
			
		
		$row = mysql_fetch_array($stashedExt);
			$stashedExt = $row['SUM(m.quantity)'];
				
	
						
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
		$unStashedExt = mysql_query($selectUnStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
		$row = mysql_fetch_array($unStashedExt);
			$unStashedExt = $row['SUM(m.quantity)'];
						

		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExtExtracts = $inStashExt;

		$extractTotal = $extractWeight + $inStashIntExtracts + $inStashExtExtracts;
		
		// Aggregates:
		$inStashIntTotal = $inStashIntFlower + $inStashIntExtracts;
		$inStashExtTotal = $inStashExtFlower + $inStashExtExtracts;
		$totalWithShake = $flowerTotal + $extractTotal;
		$totalWithoutShake = $flowerTotalWithoutShake + $extractTotal;
		
		
		
		/****** THEN DAY ******/
		
		// Look up today's sales by category	
		$selectSalesFlowers = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.category = '1'";

		$result = mysql_query($selectSalesFlowers)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$dayflowerSalesToday = $row['SUM(d.amount)'];
			$dayflowerDispensed = $row['SUM(d.quantity)'];

		$selectSalesExtracts = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.category = '2'";

		$result = mysql_query($selectSalesExtracts)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$dayextractSalesToday = $row['SUM(d.amount)'];
			$dayextractDispensed = $row['SUM(d.quantity)'];
			
			
		// Look up today's bar sales
		$selectBarSales = "SELECT SUM(s.amount), SUM(d.quantity) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime'";
	
		$result = mysql_query($selectBarSales)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$daybarSales = $row['SUM(s.amount)'];
			$daybarUnits = $row['SUM(d.quantity)'];
			
		// FLOWERS STASH
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		$stashedInt = mysql_query($selectStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedInt);
				$daystashedInt = $row['SUM(m.quantity)'];
				
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		$unStashedInt = mysql_query($selectUnStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedInt);
				$dayunStashedInt = $row['SUM(m.quantity)'];
							
	
			$dayinStashInt = $daystashedInt - $dayunStashedInt;
			$dayinStashIntFlower = $dayinStashInt;
			
					
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		$stashedExt = mysql_query($selectStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
		$row = mysql_fetch_array($stashedExt);
			$daystashedExt = $row['SUM(m.quantity)'];
						
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		$unStashedExt = mysql_query($selectUnStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
		$row = mysql_fetch_array($unStashedExt);
			$dayunStashedExt = $row['SUM(m.quantity)'];
						

		$dayinStashExt = $daystashedExt - $dayunStashedExt;
		$dayinStashExtFlower = $dayinStashExt;
		
		$dayflowerTotal = $dayflowerWeight + $dayinStashIntFlower + $dayinStashExtFlower;
		$dayflowerTotalWithoutShake = $dayflowerWeightWithoutShake + $dayinStashInt + $dayinStashExt;
			
	
			
		// EXTRACTS
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		$stashedInt = mysql_query($selectStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
		$row = mysql_fetch_array($stashedInt);
			$daystashedInt = $row['SUM(m.quantity)'];
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		$unStashedInt = mysql_query($selectUnStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
		$row = mysql_fetch_array($unStashedInt);
			$dayunStashedInt = $row['SUM(m.quantity)'];
						

		$dayinStashInt = $daystashedInt - $dayunStashedInt;
		$dayinStashIntExtracts = $dayinStashInt;
			
		
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		
	
		$stashedExt = mysql_query($selectStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
			
		
		$row = mysql_fetch_array($stashedExt);
			$daystashedExt = $row['SUM(m.quantity)'];
				
	
						
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
		$unStashedExt = mysql_query($selectUnStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
		$row = mysql_fetch_array($unStashedExt);
			$dayunStashedExt = $row['SUM(m.quantity)'];
						

		$dayinStashExt = $daystashedExt - $dayunStashedExt;
		$dayinStashExtExtracts = $dayinStashExt;

		$dayextractTotal = $dayextractWeight + $dayinStashIntExtracts + $dayinStashExtExtracts;
		
		// Aggregates:
		$dayinStashIntTotal = $dayinStashIntFlower + $dayinStashIntExtracts;
		$dayinStashExtTotal = $dayinStashExtFlower + $dayinStashExtExtracts;
		$daytotalWithShake = $dayflowerTotal + $dayextractTotal;
		$daytotalWithoutShake = $dayflowerTotalWithoutShake + $dayextractTotal;		
		
		$openingLookup = "SELECT dayClosedNo FROM opening WHERE openingid = $dayopeningid";
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$dayClosedNo = $row['dayClosedNo'];
			
		$openingLookup = "SELECT shiftClosedNo FROM shiftopen WHERE openingid = $openingid";
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$shiftClosedNo = $row['shiftClosedNo'];
			
		if ($dayClosedNo > 0) {
		
			// Means part of the day has been closed already, so use UPDATE
			
			// Close shift first
			$realClosingtime = date('Y-m-d H:i:s');
			
			$closingid = $dayClosedNo;

		  	$query = sprintf("UPDATE shiftclose SET closingtime = '%s', shiftEnd = '%s', prodOpening = '%f', prodAdded = '%f', prodRemoved = '%f', prodEstStock = '%f', prodStock = '%f', stockDelta = '%f', prodStockFlower = '%f', prodStockExtract = '%f', prodOpeningFlower = '%f', prodOpeningExtract = '%f', prodAddedFlower = '%f', prodAddedExtract = '%f', prodRemovedFlower = '%f', prodRemovedExtract = '%f', prodEstStockFlower = '%f', prodEstStockExtract = '%f', stockDeltaFlower = '%f', stockDeltaExtract = '%f', closedby = '%d', intStash = '%f', extStash = '%f', totalWeight = '%f', totalNoShake = '%f', flowerintStash = '%f', flowerextStash = '%f', flowerweightNoShake = '%f', flowertotalWeight = '%f', flowertotalNoShake = '%f', extractintStash = '%f', extractextStash = '%f', extracttotalWeight = '%f', flowerDispensed = '%f', extractDispensed = '%f', soldTodayFlower = '%f', soldTodayExtract = '%f', soldtodayBar = '%f', unitsSoldBar = '%f', quantitySoldReal = '%f', soldTodayFlowerReal = '%f', soldTodayExtractReal = '%f' WHERE closingid = '%d';",
		  	$realClosingtime, $closingtime, $prodOpening, $prodAdded, $prodRemoved, $prodEstStock, $prodStock, $stockDelta, $flowerWeight, $extractWeight, $prodOpeningFlower, $prodOpeningExtract, $prodAddedFlower, $prodAddedExtract, $prodRemovedFlower, $prodRemovedExtract, $prodEstStockFlower, $prodEstStockExtract, $flowerDelta, $extractDelta, $_SESSION['user_id'], $inStashIntTotal, $inStashExtTotal, $totalWithShake, $totalWithoutShake, $inStashIntFlower, $inStashExtFlower, $flowerWeightWithoutShake, $flowerTotal, $flowerTotalWithoutShake, $inStashIntExtracts, $inStashExtExtracts, $extractTotal, $flowerDispensed, $extractDispensed, $flowerSalesToday, $extractSalesToday, $barSales, $barUnits, $flowerDispensed + $extractDispensed, $flowerDispensed, $extractDispensed, $shiftClosedNo);
		  	
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
	
			$updateOpening = sprintf("UPDATE shiftopen SET disClosed = 2, disClosedAt = '%s' WHERE openingid = '%d';",
				$realClosingtime,
				mysql_real_escape_string($openingid)
				);
				
			mysql_query($updateOpening)
				or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());
				
				
			// Now close Day
			$realClosingtime2 = date('Y-m-d H:i:s', time() + 5);

		  	$query = sprintf("UPDATE closing SET closingtime = '%s', shiftEnd = '%s', prodOpening = '%f', prodAdded = '%f', prodRemoved = '%f', prodEstStock = '%f', prodStock = '%f', stockDelta = '%f', prodStockFlower = '%f', prodStockExtract = '%f', prodOpeningFlower = '%f', prodOpeningExtract = '%f', prodAddedFlower = '%f', prodAddedExtract = '%f', prodRemovedFlower = '%f', prodRemovedExtract = '%f', prodEstStockFlower = '%f', prodEstStockExtract = '%f', stockDeltaFlower = '%f', stockDeltaExtract = '%f', closedby = '%d', intStash = '%f', extStash = '%f', totalWeight = '%f', totalNoShake = '%f', flowerintStash = '%f', flowerextStash = '%f', flowerweightNoShake = '%f', flowertotalWeight = '%f', flowertotalNoShake = '%f', extractintStash = '%f', extractextStash = '%f', extracttotalWeight = '%f', flowerDispensed = '%f', extractDispensed = '%f', soldTodayFlower = '%f', soldTodayExtract = '%f', soldtodayBar = '%f', unitsSoldBar = '%f', quantitySoldReal = '%f', soldTodayFlowerReal = '%f', soldTodayExtractReal = '%f' WHERE closingid = '%d';",
		  	$realClosingtime2, $closingtime, $dayprodOpening, $dayprodAdded, $dayprodRemoved, $dayprodEstStock, $dayprodStock, $daystockDelta, $dayflowerWeight, $dayextractWeight, $dayprodOpeningFlower, $dayprodOpeningExtract, $dayprodAddedFlower, $dayprodAddedExtract, $dayprodRemovedFlower, $dayprodRemovedExtract, $dayprodEstStockFlower, $dayprodEstStockExtract, $dayflowerDelta, $dayextractDelta, $_SESSION['user_id'], $dayinStashIntTotal, $dayinStashExtTotal, $daytotalWithShake, $daytotalWithoutShake, $dayinStashIntFlower, $dayinStashExtFlower, $dayflowerWeightWithoutShake, $dayflowerTotal, $dayflowerTotalWithoutShake, $dayinStashIntExtracts, $dayinStashExtExtracts, $dayextractTotal, $dayflowerDispensed, $dayextractDispensed, $dayflowerSalesToday, $dayextractSalesToday, $daybarSales, $daybarUnits, $dayflowerDispensed + $dayextractDispensed, $dayflowerDispensed, $dayextractDispensed, $dayClosedNo);
			  	
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());

			$updateOpening = sprintf("UPDATE opening SET disClosed = 2, disClosedAt = '%s' WHERE openingid = '%d';",
				$realClosingtime2,
				mysql_real_escape_string($dayopeningid)
				);

			mysql_query($updateOpening)
				or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());

						
		} else {	
			
			// Close shift first
			$realClosingtime = date('Y-m-d H:i:s');
			
			// Query to add Closing - 37 arguments
			$query = sprintf("INSERT INTO shiftclose (closingtime, shiftEnd, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, stockDelta, prodStockFlower, prodStockExtract, prodOpeningFlower, prodOpeningExtract, prodAddedFlower, prodAddedExtract, prodRemovedFlower, prodRemovedExtract, prodEstStockFlower, prodEstStockExtract, stockDeltaFlower, stockDeltaExtract, closedby, intStash, extStash, totalWeight, totalNoShake, flowerintStash, flowerextStash, flowerweightNoShake, flowertotalWeight, flowertotalNoShake, extractintStash, extractextStash, extracttotalWeight, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, soldtodayBar, unitsSoldBar, quantitySoldReal, soldTodayFlowerReal, soldTodayExtractReal) VALUES ('%s', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
			$realClosingtime, $closingtime, $prodOpening, $prodAdded, $prodRemoved, $prodEstStock, $prodStock, $stockDelta, $flowerWeight, $extractWeight, $prodOpeningFlower, $prodOpeningExtract, $prodAddedFlower, $prodAddedExtract, $prodRemovedFlower, $prodRemovedExtract, $prodEstStockFlower, $prodEstStockExtract, $flowerDelta, $extractDelta, $_SESSION['user_id'], $inStashIntTotal, $inStashExtTotal, $totalWithShake, $totalWithoutShake, $inStashIntFlower, $inStashExtFlower, $flowerWeightWithoutShake, $flowerTotal, $flowerTotalWithoutShake, $inStashIntExtracts, $inStashExtExtracts, $extractTotal, $flowerDispensed, $extractDispensed, $flowerSalesToday, $extractSalesToday, $barSales, $barUnits, $flowerDispensed + $extractDispensed, $flowerDispensed, $extractDispensed);

		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
		$shiftClosedNo = mysql_insert_id();
		
		$updateOpening = sprintf("UPDATE shiftopen SET disClosed = 2, disClosedAt = '%s', shiftClosedNo = '%d' WHERE openingid = '%d';",
			$realClosingtime,
			mysql_real_escape_string($shiftClosedNo),
			mysql_real_escape_string($openingid)
			);
				
		mysql_query($updateOpening)
			or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
		

		// Now close Day
		$realClosingtime2 = date('Y-m-d H:i:s', time() + 5);
		
		$query = sprintf("INSERT INTO closing (closingtime, shiftEnd, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, stockDelta, prodStockFlower, prodStockExtract, prodOpeningFlower, prodOpeningExtract, prodAddedFlower, prodAddedExtract, prodRemovedFlower, prodRemovedExtract, prodEstStockFlower, prodEstStockExtract, stockDeltaFlower, stockDeltaExtract, closedby, intStash, extStash, totalWeight, totalNoShake, flowerintStash, flowerextStash, flowerweightNoShake, flowertotalWeight, flowertotalNoShake, extractintStash, extractextStash, extracttotalWeight, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, soldtodayBar, unitsSoldBar, quantitySoldReal, soldTodayFlowerReal, soldTodayExtractReal) VALUES ('%s', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
		$realClosingtime2, $closingtime, $dayprodOpening, $dayprodAdded, $dayprodRemoved, $dayprodEstStock, $dayprodStock, $daystockDelta, $dayflowerWeight, $dayextractWeight, $dayprodOpeningFlower, $dayprodOpeningExtract, $dayprodAddedFlower, $dayprodAddedExtract, $dayprodRemovedFlower, $dayprodRemovedExtract, $dayprodEstStockFlower, $dayprodEstStockExtract, $dayflowerDelta, $dayextractDelta, $_SESSION['user_id'], $dayinStashIntTotal, $dayinStashExtTotal, $daytotalWithShake, $daytotalWithoutShake, $dayinStashIntFlower, $dayinStashExtFlower, $dayflowerWeightWithoutShake, $dayflowerTotal, $dayflowerTotalWithoutShake, $dayinStashIntExtracts, $dayinStashExtExtracts, $dayextractTotal, $dayflowerDispensed, $dayextractDispensed, $dayflowerSalesToday, $dayextractSalesToday, $daybarSales, $daybarUnits, $dayflowerDispensed + $dayextractDispensed, $dayflowerDispensed, $dayextractDispensed);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
		$dayClosedNo = mysql_insert_id();
		
		$updateOpening = sprintf("UPDATE opening SET disClosed = 2, disClosedAt = '%s', dayClosedNo = '%d' WHERE openingid = '%d';",
			$realClosingtime,
			mysql_real_escape_string($dayClosedNo),
			mysql_real_escape_string($dayopeningid)
			);

		mysql_query($updateOpening)
			or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());
			
		}
		
		foreach($_SESSION['confirmedClose'] as $confirmedClose) {
			$name = $confirmedClose['name'];
			$category = $confirmedClose['category'];
			$productid = $confirmedClose['productid'];
			$purchaseid = $confirmedClose['purchaseid'];
			$weightToday = $confirmedClose['weightToday'];
			$addedToday = $confirmedClose['addedToday'];
			$soldToday = $confirmedClose['soldToday'];
			$takeoutsToday = $confirmedClose['takeoutsToday'];
			$estWeight = $confirmedClose['estWeight'];
			$weight = $confirmedClose['weight'];
			$prodclosecomment = $confirmedClose['prodclosecomment'];
			$shake = $confirmedClose['shake'];
			$growtype = $confirmedClose['growtype'];
			$breed2 = $confirmedClose['breed2'];
			$productStatus = $confirmedClose['productStatus'];
			$inMenu = $confirmedClose['inMenu'];

			$weightDelta = 0;
			$weightDelta = $weight - $estWeight;
			
			$weightWithoutShake = ($weight - ($weight * ($shake / 100)));
			
			$dayweightToday = $confirmedClose['dayweightToday'];
			$dayaddedToday = $confirmedClose['dayaddedToday'];
			$daysoldToday = $confirmedClose['daysoldToday'];
			$daytakeoutsToday = $confirmedClose['daytakeoutsToday'];
			$dayestWeight = $confirmedClose['dayestWeight'];
			
			$dayweightDelta = 0;
			$dayweightDelta = $weight - $dayestWeight;
			
			if ($category == 1) {

				/****** SHIFT FIRST ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$unStashedInt = $row['SUM(m.quantity)'];
									
			
					$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedExt);
					$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedExt);
					$unStashedExt = $row['SUM(m.quantity)'];
									
			
				$inStashExt = $stashedExt - $unStashedExt;
				
				$prodTotal = $weight + $inStashInt + $inStashExt;
				$prodTotalWithoutShake = $weightWithoutShake + $inStashInt + $inStashExt;

				/****** THEN DAY ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
					$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedExt);
					$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedExt);
					$dayunStashedExt = $row['SUM(m.quantity)'];
									
			
				$dayinStashExt = $daystashedExt - $dayunStashedExt;
				
				$dayprodTotal = $weight + $dayinStashInt + $dayinStashExt;
				$dayprodTotalWithoutShake = $weightWithoutShake + $dayinStashInt + $dayinStashExt;
					
			
			
		    	// Query to add to shiftclosedetails table - 12 arguments
				$query = sprintf("INSERT INTO shiftclosedetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $shiftClosedNo, $category, $productid, $purchaseid, $weightToday, $addedToday, $soldToday, $takeoutsToday, $weight, $estWeight, $weightDelta, $prodclosecomment, $shake, $inStashInt, $inStashExt, $weightWithoutShake, $prodTotal, $prodTotalWithoutShake, $inMenu);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
					
		    	// Query to add to closingdetails table - 12 arguments
				$query = sprintf("INSERT INTO closingdetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $dayClosedNo, $category, $productid, $purchaseid, $dayweightToday, $dayaddedToday, $daysoldToday, $daytakeoutsToday, $weight, $dayestWeight, $dayweightDelta, $prodclosecomment, $shake, $dayinStashInt, $dayinStashExt, $weightWithoutShake, $dayprodTotal, $dayprodTotalWithoutShake, $inMenu);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
		
			} else if ($category == '2') {
	
				/****** SHIFT FIRST ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedInt);
					$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedInt);
					$unStashedInt = $row['SUM(m.quantity)'];
									
			
				$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedExt);
					$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedExt);
					$unStashedExt = $row['SUM(m.quantity)'];
									
			
				$inStashExt = $stashedExt - $unStashedExt;
					
				$prodTotal = $weight + $inStashInt + $inStashExt;

			
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedInt);
					$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedInt);
					$unStashedInt = $row['SUM(m.quantity)'];
									
			
				$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedExt);
					$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedExt);
					$unStashedExt = $row['SUM(m.quantity)'];
									
			
				$inStashExt = $stashedExt - $unStashedExt;
					
				$prodTotal = $weight + $inStashInt + $inStashExt;
				
				
				
				
				
				
				
				/****** THEN DAY ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedInt);
					$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedInt);
					$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
				$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedExt);
					$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedExt);
					$dayunStashedExt = $row['SUM(m.quantity)'];
									
			
				$dayinStashExt = $daystashedExt - $dayunStashedExt;
					
				$dayprodTotal = $weight + $dayinStashInt + $dayinStashExt;

			
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedInt);
					$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedInt);
					$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
				$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($stashedExt);
					$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
				$row = mysql_fetch_array($unStashedExt);
					$dayunStashedExt = $row['SUM(m.quantity)'];
									
			
				$dayinStashExt = $daystashedExt - $dayunStashedExt;
					
				$dayprodTotal = $weight + $dayinStashInt + $dayinStashExt;
		
				
			
		    	// Query to add to shiftclosedetails table
				$query = sprintf("INSERT INTO shiftclosedetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $shiftClosedNo, $category, $productid, $purchaseid, $weightToday, $addedToday, $soldToday, $takeoutsToday, $weight, $estWeight, $weightDelta, $prodclosecomment, $shake, $inStashInt, $inStashExt, $weightWithoutShake, $prodTotal, $prodTotal, $inMenu);
		  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
						
		    	// Query to add to closingdetails table
				$query = sprintf("INSERT INTO closingdetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $dayClosedNo, $category, $productid, $purchaseid, $dayweightToday, $dayaddedToday, $daysoldToday, $daytakeoutsToday, $weight, $dayestWeight, $dayweightDelta, $prodclosecomment, $shake, $dayinStashInt, $dayinStashExt, $weightWithoutShake, $dayprodTotal, $dayprodTotal, $inMenu);
		  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
				
	}

		
} // Product loop ends
		
	
		// For each other cat, look up units sold 
		$selectCats = "SELECT id, name from categories ORDER by id ASC";
	
		$resultCats = mysql_query($selectCats)
			or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
	
		while ($category = mysql_fetch_array($resultCats)) {
			
			$categoryid = $category['id'];
			$name = $category['name'];
			
			$selectProducts = "SELECT pr.productid, p.purchaseid, p.inMenu from products pr, purchases p WHERE p.category = $categoryid AND pr.productid = p.productid AND (p.closedAt IS NULL OR p.closingDate BETWEEN '$dayopeningtime' AND '$closingtime') ORDER BY pr.name ASC;";
		
			$resultProducts = mysql_query($selectProducts)
				or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
				
	
			while ($product = mysql_fetch_array($resultProducts)) {
				
				$productid = $product['productid'];
				$purchaseid = $product['purchaseid'];
				$inMenu = $product['inMenu'];
				
		
				/****** SHIFT FIRST ******/
				
				$selectSalesProducts = "SELECT SUM(d.quantity) FROM sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime' AND purchaseid = $purchaseid";
						
				$result = mysql_query($selectSalesProducts)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
				$row = mysql_fetch_array($result);
					$unitsToday = $row['SUM(d.quantity)'];
					
					
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$unStashedInt = $row['SUM(m.quantity)'];
									
			
					$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedExt);
						$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedExt);
						$unStashedExt = $row['SUM(m.quantity)'];
									
			
					$inStashExt = $stashedExt - $unStashedExt;
					
					
				/****** THEN DAY ******/
				
				$selectSalesProducts = "SELECT SUM(d.quantity) FROM sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND purchaseid = $purchaseid";
						
				$result = mysql_query($selectSalesProducts)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
				$row = mysql_fetch_array($result);
					$dayunitsToday = $row['SUM(d.quantity)'];
					
					
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
					$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedExt);
						$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedExt);
						$dayunStashedExt = $row['SUM(m.quantity)'];
									
			
					$dayinStashExt = $daystashedExt - $dayunStashedExt;
					
					
		    	// Query to add to shiftclosedetails table
				$query = sprintf("INSERT INTO shiftclosedetails (closingid, category, productid, purchaseid, soldToday, inMenu, intStash, extStash) VALUES ('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d');",
			  			 $shiftClosedNo, $categoryid, $productid, $purchaseid, $unitsToday, $inMenu, $inStashInt, $inStashExt);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
					
		    	// Query to add to closingdetails table
				$query = sprintf("INSERT INTO closingdetails (closingid, category, productid, purchaseid, soldToday, inMenu, intStash, extStash) VALUES ('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d');",
			  			 $dayClosedNo, $categoryid, $productid, $purchaseid, $dayunitsToday, $inMenu, $dayinStashInt, $dayinStashExt);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());

			}
		}
		
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['dispensary-closed-successfully'];
		header("Location: close-shift-and-day.php");
		exit();

		
	}
	## ON PAGE SUBMISSION END ##
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

		
	// Did the user choose not to weigh products?
	if ($_POST['noWeighing'] == 'yes') {
		
	$confirmLeave = <<<EOD
    $(document).ready(function() {   	    
document.querySelector('button').addEventListener("click", function(){
    window.btn_clicked = true;      //set btn_clicked to true
});

$(window).bind('beforeunload', function(){
    if(!window.btn_clicked){
        return "{$lang['closeday-leavepage']}";
    }
});
  }); // end ready
EOD;

	pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "step6", $lang['closeday-dis-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
	$_SESSION['daycloseProduct'] = $_POST['daycloseProduct'];
	
	echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='?saveDispensary' method='POST'><br />";
	echo "<input type='hidden' name='productConfirm' value='yes'><br />";
	
	$i=0;
		foreach($_POST['daycloseProduct'] as $prodClose) {
			$name = $prodClose['name'];
			$category = $prodClose['category'];
			$productid = $prodClose['productid'];
			$purchaseid = $prodClose['purchaseid'];
			$fullWeight = $prodClose['weight'];
			$shake = $prodClose['shake'];
			$growtype = $prodClose['growtype'];
			$breed2 = $prodClose['breed2'];
			$closed = $prodClose['closed'];
			$inMenu = $prodClose['inMenu'];
			$tupperWeight = $prodClose['tupperWeight'];
			
			$weight = $fullWeight - $tupperWeight;
			
			
			if ($closed == 'yes') {
				$disableOrNot = "disabled style='color: red'";
				$productStatus = "Closed";
				$radioDisable = "disabled";
			} else if ($inMenu == 0) {
				$disableOrNot = "";
				$productStatus = "Not in menu";
				$radioDisable = "";
			} else {
				$disableOrNot = "";
				$productStatus = "In menu";
				$radioDisable = "";
			}
						
			$required0 = '';
			$required25 = '';
			$required50 = '';
			$required75 = '';
			
			if ($shake == '0') {
				$required0 = 'checked';
			} else if ($shake == '25') {
				$required25 = 'checked';
			} else if ($shake == '50') {
				$required50 = 'checked';
			} else if ($shake == '75') {
				$required75 = 'checked';
			}

			
			/****** SHIFT FIRST ******/
			
			// Look up todays sales
			$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";

			$result = mysql_query($selectSales)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$soldToday = $row['SUM(d.quantity)'];
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$openingtime' AND '$closingtime' ORDER by purchaseDate DESC";

			$result = mysql_query($selectPurchase)
				or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$newPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM shiftopendetails d, shiftopen o WHERE o.openingid = $openingid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
			
			$result = mysql_query($openingLookup)
				or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
		
			// Retrieve todays opening data
			$row = mysql_fetch_array($result);
				$weightToday = $row['weight'];
			
			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
		
			$additions = mysql_query($selectAdditions)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($additions);
				$addedToday = $row['SUM(quantity)'];
				
		
			$removals = mysql_query($selectRemovals)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($removals);
				$takeoutsToday = $row['SUM(quantity)'];
			
			// Exception if there was no weight this morning, we use the new product weight
			if (($weightToday == 0)) {
				$addedToday = $newPurchaseWeight + $addedToday;
			}
				
			// Calculate estimated weight and weight Delta:
			$estWeight = $weightToday - $soldToday - $takeoutsToday + $addedToday;
			$weightDelta = $weight - $estWeight;
			
			
			/****** THEN DAY ******/
			
			// Look up todays sales
			$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE s.saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";

			$result = mysql_query($selectSales)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$daysoldToday = $row['SUM(d.quantity)'];
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$dayopeningtime' AND '$closingtime' ORDER by purchaseDate DESC";

			$result = mysql_query($selectPurchase)
				or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$daynewPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE o.openingid = $dayopeningid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
			
			$result = mysql_query($openingLookup)
				or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
		
			// Retrieve todays opening data
			$row = mysql_fetch_array($result);
				$dayweightToday = $row['weight'];
			
			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
		
			$additions = mysql_query($selectAdditions)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($additions);
				$dayaddedToday = $row['SUM(quantity)'];
				
		
			$removals = mysql_query($selectRemovals)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($removals);
				$daytakeoutsToday = $row['SUM(quantity)'];
			
			// Exception if there was no weight this morning, we use the new product weight
			if (($dayweightToday == 0)) {
				$dayaddedToday = $daynewPurchaseWeight + $dayaddedToday;
			}
			
			// Calculate estimated weight and weight Delta:
			$dayestWeight = $dayweightToday - $daysoldToday - $daytakeoutsToday + $dayaddedToday;
			$dayweightDelta = $weight - $dayestWeight;
			
			
			
			// Determine colour of weight delta field
			if ($weightDelta < 0) {
				$deltaColour = ' negative';
			} else if ($weightDelta > 0) {
				$deltaColour = ' positive';
			} else {
				$deltaColour = '';
			}
			
			$i++;
			
			if ($category == 1) {
				
				if ($flowerheader != 1) {
					echo "<h3 class='title'>{$lang['global-flowerscaps']}</h3>";
				}		
			$flowerheader = '1';
			
			$product_row = sprintf("
	
<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#fullWeight%d').val();
          var b = $('#estWeight%d').val();
          var c = $('#tupperWeight%d').val();
          var total = (a - c) - b;
          var roundedtotal = total.toFixed(2);
          $('#weightDelta%d').val(roundedtotal);
          
          var realTotal = a - c;
          var roundedrealTotal = realTotal.toFixed(2);
          $('#weight%d').val(roundedrealTotal);

          var wdelta%d = $('#weightDelta%d').val();
          
          if (wdelta%d < '0.00') {
          	$('#weightDelta%d').css('color', 'red');
      	  }
      	  if (wdelta%d > '0.00') {
          	$('#weightDelta%d').css('color', 'green');
      	  }
    }

        $('#weight%d').bind('keypress keyup blur', compute);
        $('#fullWeight%d').bind('keypress keyup blur', compute);
        $('#tupperWeight%d').bind('keypress keyup blur', compute);
        

  }); // end ready
</script>
		<div class='productbox'>
		 <h3>%s %s</h3>
		 %s<br />
		 <table>
		  <tr>
		   <td>{$lang['closeday-openingweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-dispensed']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-takeouts']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}:</td>
		   <td>
		    <input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit' value='%0.02f' readonly />
		    <input type='hidden' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit' value='%0.02f' step='0.01' readonly />
		   </td>
		  </tr>
		 </table><br />

		 {$lang['global-comment']}?<br />
		 <textarea name='confirmedClose[%d][prodclosecomment]'></textarea>
		</div>
  	   <input type='hidden' name='confirmedClose[%d][name]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][category]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][productid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][growtype]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][breed2]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][productStatus]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][dayweightToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayaddedToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daysoldToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daytakeoutsToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayestWeight]' value='%f' />",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $prodClose['name'], $breed2, $growtype, $i, $weightToday, $i, $addedToday, $i, $soldToday, $i, $takeoutsToday, $i, $i, $estWeight, $i, $i, $estWeight,  $i, $i, $name, $i, $category, $i, $productid, $i, $purchaseid, $i, $growtype, $i, $breed2, $i, $productStatus, $i, $inMenu, $i, $dayweightToday, $i, $dayaddedToday, $i, $daysoldToday, $i, $daytakeoutsToday, $i, $dayestWeight
	  );
	  
  } else if ($category == '2') {
	  
		if ($extractheader != 1) {
			echo "<h3 class='title'>{$lang['global-extractscaps']}</h3>";
		}		
	$extractheader = '1';
	
	$product_row = sprintf("
	
<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#fullWeight%d').val();
          var b = $('#estWeight%d').val();
          var c = $('#tupperWeight%d').val();
          var total = (a - c) - b;
          var roundedtotal = total.toFixed(2);
          $('#weightDelta%d').val(roundedtotal);
          
          var realTotal = a - c;
          var roundedrealTotal = realTotal.toFixed(2);
          $('#weight%d').val(roundedrealTotal);

          var wdelta%d = $('#weightDelta%d').val();
          
          if (wdelta%d < '0.00') {
          	$('#weightDelta%d').css('color', 'red');
      	  }
      	  if (wdelta%d > '0.00') {
          	$('#weightDelta%d').css('color', 'green');
      	  }
    }

        $('#weight%d').bind('keypress keyup blur', compute);
        $('#fullWeight%d').bind('keypress keyup blur', compute);
        $('#tupperWeight%d').bind('keypress keyup blur', compute);
        

  }); // end ready
</script>
		<div class='productbox'>
		 <h3>%s</h3>
		 <table>
		  <tr>
		   <td>{$lang['closeday-openingweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-dispensed']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-takeouts']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}:</td>
		   <td>
		    <input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit' value='%0.02f' readonly />
		    <input type='hidden' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit' value='%0.02f' step='0.01' readonly />
		   </td>
		  </tr>
		 </table><br />
		   {$lang['global-comment']}?<br />
		   <textarea name='confirmedClose[%d][prodclosecomment]'></textarea>
		</div>
  	   <input type='hidden' name='confirmedClose[%d][name]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][category]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][productid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][productStatus]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][dayweightToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayaddedToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daysoldToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daytakeoutsToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayestWeight]' value='%f' />",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $prodClose['name'], $i, $weightToday, $i, $addedToday, $i, $soldToday, $i, $takeoutsToday, $i, $i, $estWeight, $i, $i, $estWeight, $i, $i, $name, $i, $category, $i, $productid, $i, $purchaseid, $i, $productStatus, $i, $inMenu, $i, $dayweightToday, $i, $dayaddedToday, $i, $daysoldToday, $i, $daytakeoutsToday, $i, $dayestWeight
	  );
	    }
	  echo $product_row;
	  
		// End loop for each product
		}
		
		echo "<input type='hidden' name='step3' value='complete' />";
 		echo "<button name='oneClick' type='submit'>{$lang['global-confirm']}</button>";
		echo "</form>";
		displayFooter();
		exit();
		
		## FORM INPUT END ##
		
	} else if ($_POST['closingConfirm'] == 'yes') {
		
		$openingtime = $_SESSION['openingtime'];
		$closingtime = $_SESSION['closingtime'];
 
		
	$confirmLeave = <<<EOD
    $(document).ready(function() {   	    
document.querySelector('button').addEventListener("click", function(){
    window.btn_clicked = true;      //set btn_clicked to true
});

$(window).bind('beforeunload', function(){
    if(!window.btn_clicked){
        return "{$lang['closeday-leavepage']}";
    }
});
  }); // end ready
EOD;

	pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "step6", $lang['closeday-dis-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
	$_SESSION['daycloseProduct'] = $_POST['daycloseProduct'];
	
	echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='?saveDispensary' method='POST'><br />";
	echo "<input type='hidden' name='productConfirm' value='yes'><br />";
	
	$i=0;
		foreach($_POST['daycloseProduct'] as $prodClose) {
			$name = $prodClose['name'];
			$category = $prodClose['category'];
			$productid = $prodClose['productid'];
			$purchaseid = $prodClose['purchaseid'];
			$fullWeight = $prodClose['weight'];
			$shake = $prodClose['shake'];
			$growtype = $prodClose['growtype'];
			$breed2 = $prodClose['breed2'];
			$closed = $prodClose['closed'];
			$inMenu = $prodClose['inMenu'];
			$tupperWeight = $prodClose['tupperWeight'];
			
			$weight = $fullWeight - $tupperWeight;
			
			
			if ($closed == 'yes') {
				$disableOrNot = "disabled style='color: red'";
				$productStatus = "Closed";
				$radioDisable = "disabled";
			} else if ($inMenu == 0) {
				$disableOrNot = "";
				$productStatus = "Not in menu";
				$radioDisable = "";
			} else {
				$disableOrNot = "";
				$productStatus = "In menu";
				$radioDisable = "";
			}
						
			$required0 = '';
			$required25 = '';
			$required50 = '';
			$required75 = '';
			
			if ($shake == '0') {
				$required0 = 'checked';
			} else if ($shake == '25') {
				$required25 = 'checked';
			} else if ($shake == '50') {
				$required50 = 'checked';
			} else if ($shake == '75') {
				$required75 = 'checked';
			}

			
			/****** SHIFT FIRST ******/
			
			// Look up todays sales
			$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";

			$result = mysql_query($selectSales)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$soldToday = $row['SUM(d.quantity)'];
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$openingtime' AND '$closingtime' ORDER by purchaseDate DESC";

			$result = mysql_query($selectPurchase)
				or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$newPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM shiftopendetails d, shiftopen o WHERE o.openingid = $openingid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
				
			$result = mysql_query($openingLookup)
				or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
		
			// Retrieve todays opening data
			$row = mysql_fetch_array($result);
				$weightToday = $row['weight'];

			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
		
			$additions = mysql_query($selectAdditions)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($additions);
				$addedToday = $row['SUM(quantity)'];
				
		
			$removals = mysql_query($selectRemovals)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($removals);
				$takeoutsToday = $row['SUM(quantity)'];
			
			// Exception if there was no weight this morning, we use the new product weight
			if (($weightToday == 0)) {
				$addedToday = $newPurchaseWeight + $addedToday;
			}
				
			// Calculate estimated weight and weight Delta:
			$estWeight = $weightToday - $soldToday - $takeoutsToday + $addedToday;
			$weightDelta = $weight - $estWeight;
			
			
			/****** THEN DAY ******/
			
			// Look up todays sales
			$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE s.saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";

			$result = mysql_query($selectSales)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$daysoldToday = $row['SUM(d.quantity)'];
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$dayopeningtime' AND '$closingtime' ORDER by purchaseDate DESC";

			$result = mysql_query($selectPurchase)
				or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$daynewPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE o.openingid = $dayopeningid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
			
			$result = mysql_query($openingLookup)
				or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
		
			// Retrieve todays opening data
			$row = mysql_fetch_array($result);
				$dayweightToday = $row['weight'];

			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
		
			$additions = mysql_query($selectAdditions)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($additions);
				$dayaddedToday = $row['SUM(quantity)'];
		
			$removals = mysql_query($selectRemovals)
				or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
		
			$row = mysql_fetch_array($removals);
				$daytakeoutsToday = $row['SUM(quantity)'];
			
			// Exception if there was no weight this morning, we use the new product weight
			if (($dayweightToday == 0)) {
				$dayaddedToday = $daynewPurchaseWeight + $dayaddedToday;
			}
			
			// Calculate estimated weight and weight Delta:
			$dayestWeight = $dayweightToday - $daysoldToday - $daytakeoutsToday + $dayaddedToday;
			$dayweightDelta = $weight - $dayestWeight;
			
			
			// Determine colour of weight delta field
			if ($weightDelta < 0) {
				$deltaColour = ' negative';
			} else if ($weightDelta > 0) {
				$deltaColour = ' positive';
			} else {
				$deltaColour = '';
			}
			
			$i++;
			
			if ($category == 1) {
				
				if ($flowerheader != 1) {
					echo "<h3 class='title'>{$lang['global-flowerscaps']}</h3>";
				}		
			$flowerheader = '1';
		
			$product_row = sprintf("
	
<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#fullWeight%d').val();
          var b = $('#estWeight%d').val();
          var c = $('#tupperWeight%d').val();
          var total = (a - c) - b;
          var roundedtotal = total.toFixed(2);
          $('#weightDelta%d').val(roundedtotal);
          
          var realTotal = a - c;
          var roundedrealTotal = realTotal.toFixed(2);
          $('#weight%d').val(roundedrealTotal);

          var wdelta%d = $('#weightDelta%d').val();
          
          if (wdelta%d < '0.00') {
          	$('#weightDelta%d').css('color', 'red');
      	  }
      	  if (wdelta%d > '0.00') {
          	$('#weightDelta%d').css('color', 'green');
      	  }
    }

        $('#weight%d').bind('keypress keyup blur', compute);
        $('#fullWeight%d').bind('keypress keyup blur', compute);
        $('#tupperWeight%d').bind('keypress keyup blur', compute);
        

  }); // end ready
</script>
		<div class='productbox'>
		 <h3>%s %s</h3>
		 %s<br />
		 <table>
		  <tr>
		   <td>{$lang['closeday-openingweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-dispensed']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-takeouts']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		 <tr>
		  <td colspan='2'>&nbsp;</td>
		 </tr>
		 <tr>
		  <td>{$lang['weightnow']}:</td>
		  <td><input type='number' lang='nb' name='confirmedClose[%d][fullWeight]' id='fullWeight%d' class='fourDigit' value='%0.02f' step='0.01' /></td>
		 </tr>
		 <tr>
		  <td>- {$lang['jar-weight']}:</td>
		  <td><input type='number' lang='nb' name='confirmedClose[%d][tupperWeight]' id='tupperWeight%d' class='fourDigit red' value='%0.02f' step='0.01' /></td>
		 </tr>
		  <tr>
		   <td>{$lang['add-realweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit' value='%0.02f' step='0.01' %s readonly /></td>
		  </tr>
		  <tr>
		   <td><strong>{$lang['global-delta']}:</strong></td>
		   <td><strong><input type='number' lang='nb' name='confirmedClose[%d][weightDelta]' id='weightDelta%d' class='fourDigit%s' value='%0.02f' step='0.01' readonly /></strong></td>
		  </tr>
		 </table><br />
		 {$lang['global-shake']}:<br />
    	<input type='radio' name='confirmedClose[%d][shake]' value='0' style='margin-left: 5px; width: 12px;' %s %s>0%%</input><br />
    	<input type='radio' name='confirmedClose[%d][shake]' value='25' style='margin-left: 5px; width: 12px;' %s %s>25%%</input><br />
    	<input type='radio' name='confirmedClose[%d][shake]' value='50' style='margin-left: 5px; width: 12px;' %s %s>50%%</input><br />
    <input type='radio' name='confirmedClose[%d][shake]' value='75' style='margin-left: 5px; width: 12px;' %s %s>75%%</input><br /><br />

		 {$lang['global-comment']}?<br />
		 <textarea name='confirmedClose[%d][prodclosecomment]'></textarea>
		</div>
  	   <input type='hidden' name='confirmedClose[%d][name]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][category]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][productid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][growtype]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][breed2]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][productStatus]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][dayweightDelta]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayweightToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayaddedToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daysoldToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daytakeoutsToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayestWeight]' value='%f' />",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $prodClose['name'], $breed2, $growtype, $i, $weightToday, $i, $addedToday, $i, $soldToday, $i, $takeoutsToday, $i, $i, $estWeight, $i, $i, $fullWeight, $i, $i, $tupperWeight, $i, $i, $weight, $disableOrNot, $i, $i, $deltaColour, $weightDelta, $i, $required0, $radioDisable, $i, $required25, $radioDisable, $i, $required50, $radioDisable, $i, $required75, $radioDisable, $i, $i, $name, $i, $category, $i, $productid, $i, $purchaseid, $i, $growtype, $i, $breed2, $i, $productStatus, $i, $inMenu, $i, $dayweightDelta, $i, $dayweightToday, $i, $dayaddedToday, $i, $daysoldToday, $i, $daytakeoutsToday, $i, $dayestWeight
	  );
	  
  } else if ($category == '2') {
	  
		if ($extractheader != 1) {
			echo "<h3 class='title'>{$lang['global-extractscaps']}</h3>";
		}		
	$extractheader = '1';
	
	$product_row = sprintf("
	
<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#fullWeight%d').val();
          var b = $('#estWeight%d').val();
          var c = $('#tupperWeight%d').val();
          var total = (a - c) - b;
          var roundedtotal = total.toFixed(2);
          $('#weightDelta%d').val(roundedtotal);
          
          var realTotal = a - c;
          var roundedrealTotal = realTotal.toFixed(2);
          $('#weight%d').val(roundedrealTotal);

          var wdelta%d = $('#weightDelta%d').val();
          
          if (wdelta%d < '0.00') {
          	$('#weightDelta%d').css('color', 'red');
      	  }
      	  if (wdelta%d > '0.00') {
          	$('#weightDelta%d').css('color', 'green');
      	  }
    }

        $('#weight%d').bind('keypress keyup blur', compute);
        $('#fullWeight%d').bind('keypress keyup blur', compute);
        $('#tupperWeight%d').bind('keypress keyup blur', compute);
        

  }); // end ready
</script>
		<div class='productbox'>
		 <h3>%s</h3>
		 <table>
		  <tr>
		   <td>{$lang['closeday-openingweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-dispensed']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>- {$lang['closeday-takeouts']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit' value='%0.02f' readonly /><br />
		  </tr>
		 <tr>
		  <td colspan='2'>&nbsp;</td>
		 </tr>
		 <tr>
		  <td>{$lang['weightnow']}:</td>
		  <td><input type='number' lang='nb' name='confirmedClose[%d][fullWeight]' id='fullWeight%d' class='fourDigit' value='%0.02f' step='0.01' /></td>
		 </tr>
		 <tr>
		  <td>- {$lang['jar-weight']}:</td>
		  <td><input type='number' lang='nb' name='confirmedClose[%d][tupperWeight]' id='tupperWeight%d' class='fourDigit red' value='%0.02f' step='0.01' /></td>
		 </tr>
		  <tr>
		   <td>{$lang['add-realweight']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit' value='%0.02f' step='0.01' %s readonly /><br />
		  </tr>
		  <tr>
		   <td><strong>{$lang['global-delta']}:</strong></td>
		   <td><strong><input type='number' lang='nb' name='confirmedClose[%d][weightDelta]' id='weightDelta%d' class='fourDigit%s' value='%0.02f' step='0.01' readonly /></strong><br />
		  </tr>
		 </table><br />
		   {$lang['global-comment']}?<br />
		   <textarea name='confirmedClose[%d][prodclosecomment]'></textarea>
		</div>
  	   <input type='hidden' name='confirmedClose[%d][name]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][category]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][productid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][productStatus]' value='%s' />
  	   <input type='hidden' name='confirmedClose[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='confirmedClose[%d][dayweightDelta]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayweightToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayaddedToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daysoldToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daytakeoutsToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayestWeight]' value='%f' />",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $prodClose['name'], $i, $weightToday, $i, $addedToday, $i, $soldToday, $i, $takeoutsToday, $i, $i, $estWeight, $i, $i, $fullWeight, $i, $i, $tupperWeight, $i, $i, $weight, $disableOrNot, $i, $i, $deltaColour, $weightDelta, $i, $i, $name, $i, $category, $i, $productid, $i, $purchaseid, $i, $productStatus, $i, $inMenu, $i, $dayweightDelta, $i, $dayweightToday, $i, $dayaddedToday, $i, $daysoldToday, $i, $daytakeoutsToday, $i, $dayestWeight
	  );
	    }
	  echo $product_row;
	  
		// End loop for each product
		}
		
		echo "<input type='hidden' name='step3' value='complete' />";
 		echo "<button name='oneClick' type='submit'>{$lang['global-confirm']}</button>";
		echo "</form>";
		displayFooter();
		exit();
		
		## FORM INPUT END ##
		
	} else if ($_POST['step3'] != 'complete') {
		handleError($lang['global-fivenotcomplete'],"");
	}
	
} displayFooter();