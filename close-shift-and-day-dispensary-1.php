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
		
			$catArray = $_POST['catArray'];

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
				$flowerDispensed = $flowerDispensed + $soldToday;
				$weightWithoutShake = ($weight - ($weight * ($shake / 100)));
				$flowerWeightWithoutShake = $flowerWeightWithoutShake + $weightWithoutShake;
				
				$dayprodOpeningFlower = $dayprodOpeningFlower + $dayweightToday;
				$dayprodAddedFlower = $dayprodAddedFlower + $dayaddedToday;
				$dayprodRemovedFlower = $dayprodRemovedFlower + $daytakeoutsToday;
				$dayprodEstStockFlower = $dayprodEstStockFlower + $dayestWeight;
				$dayflowerWeight = $dayflowerWeight + $weight;
				$dayflowerDelta = $dayflowerDelta + $dayweightDelta;
				$dayflowerDispensed = $dayflowerDispensed + $daysoldToday;

			} else if ($category == '2') {
				
				$prodOpeningExtract = $prodOpeningExtract + $weightToday;
				$prodAddedExtract = $prodAddedExtract + $addedToday;
				$prodRemovedExtract = $prodRemovedExtract + $takeoutsToday;
				$prodEstStockExtract = $prodEstStockExtract + $estWeight;
				$extractWeight = $extractWeight + $weight;
				$extractDelta = $extractDelta + $weightDelta;
				$extractDispensed = $extractDispensed + $soldToday;
				
				$dayprodOpeningExtract = $dayprodOpeningExtract + $dayweightToday;
				$dayprodAddedExtract = $dayprodAddedExtract + $dayaddedToday;
				$dayprodRemovedExtract = $dayprodRemovedExtract + $daytakeoutsToday;
				$dayprodEstStockExtract = $dayprodEstStockExtract + $dayestWeight;
				$dayextractWeight = $dayextractWeight + $weight;
				$dayextractDelta = $dayextractDelta + $dayweightDelta;
				$dayextractDispensed = $dayextractDispensed + $soldToday;
				
				} else {
					
					${'prodOpening'.$category} = ${'prodOpening'.$category} + $weightToday;
					${'prodAdded'.$category} = ${'prodAdded'.$category} + $addedToday;
					${'prodRemoved'.$category} = ${'prodRemoved'.$category} + $takeoutsToday;
					${'prodEstStock'.$category} = ${'prodEstStock'.$category} + $estWeight;
					${'totWeight'.$category} = ${'totWeight'.$category} + $weight;
					${'totDelta'.$category} = ${'totDelta'.$category} + $weightDelta;
					${'totDispensed'.$category} = ${'totDispensed'.$category} + $soldToday;
					
					$prodOpeningother = $prodOpeningother + $weightToday;
					$prodAddedother = $prodAddedother + $addedToday;
					$prodRemovedother = $prodRemovedother + $takeoutsToday;
					$prodEstStockother = $prodEstStockother + $estWeight;
					$otherWeight = $otherWeight + $weight;
					$otherDelta = $otherDelta + $weightDelta;
					$otherDispensed = $otherDispensed + $soldToday;
					
					${'dayprodOpening'.$category} = ${'dayprodOpening'.$category} + $dayweightToday;
					${'dayprodAdded'.$category} = ${'dayprodAdded'.$category} + $dayaddedToday;
					${'dayprodRemoved'.$category} = ${'dayprodRemoved'.$category} + $daytakeoutsToday;
					${'dayprodEstStock'.$category} = ${'dayprodEstStock'.$category} + $dayestWeight;
					${'daytotWeight'.$category} = ${'daytotWeight'.$category} + $weight;
					${'daytotDelta'.$category} = ${'daytotDelta'.$category} + $dayweightDelta;
					${'daytotDispensed'.$category} = ${'daytotDispensed'.$category} + $daysoldToday;
					
					$dayprodOpeningother = $dayprodOpeningother + $dayweightToday;
					$dayprodAddedother = $dayprodAddedother + $dayaddedToday;
					$dayprodRemovedother = $dayprodRemovedother + $daytakeoutsToday;
					$dayprodEstStockother = $dayprodEstStockother + $dayestWeight;
					$dayotherWeight = $dayotherWeight + $weight;
					$dayotherDelta = $dayotherDelta + $dayweightDelta;
					$dayotherDispensed = $dayotherDispensed + $daysoldToday;
				}

		$prodOpening = $prodOpeningFlower + $prodOpeningExtract + $prodOpeningother;
		$prodAdded = $prodAddedFlower + $prodAddedExtract + $prodAddedother;
		$prodRemoved = $prodRemovedFlower + $prodRemovedExtract + $prodRemovedother;
		$prodEstStock = $prodEstStockFlower + $prodEstStockExtract + $prodEstStockother;
		$prodStock = $flowerWeight + $extractWeight + $otherWeight;
		$stockDelta = $flowerDelta + $extractDelta + $otherDelta;
		
		$dayprodOpening = $dayprodOpeningFlower + $dayprodOpeningExtract + $dayprodOpeningother;
		$dayprodAdded = $dayprodAddedFlower + $dayprodAddedExtract + $dayprodAddedother;
		$dayprodRemoved = $dayprodRemovedFlower + $dayprodRemovedExtract + $dayprodRemovedother;
		$dayprodEstStock = $dayprodEstStockFlower + $dayprodEstStockExtract + $dayprodEstStockother;
		$dayprodStock = $dayflowerWeight + $dayextractWeight + $dayotherWeight;
		$daystockDelta = $dayflowerDelta + $dayextractDelta + $dayotherDelta;

}		

			
		/****** SHIFT FIRST ******/
		
		// Look up today's sales by category
		$selectSalesFlowers = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = '1'";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlowers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$flowerSalesToday = $row['SUM(d.amount)'];
			$flowerDispensed = $row['SUM(d.quantity)'];
			$flowerSalesTodayReal = $row['SUM(d.realQuantity)'];

		$selectSalesExtracts = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = '2'";
		try
		{
			$result = $pdo3->prepare("$selectSalesExtracts");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$extractSalesToday = $row['SUM(d.amount)'];
			$extractDispensed = $row['SUM(d.quantity)'];
			$extractSalesTodayReal = $row['SUM(d.realQuantity)'];
			
		// OTHER CATEGORIES			
		foreach($catArray as $cat) {
				
			$catID = $cat;
			
			$selectSalesOther = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = $cat";
		try
		{
			$result = $pdo3->prepare("$selectSalesOther");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$otherDispensed = $otherDispensed + $row['SUM(d.amount)'];
				$otherSalesToday = $otherSalesToday + $row['SUM(d.quantity)'];
				$otherSalesTodayReal = $otherSalesTodayReal + $row['SUM(d.realQuantity)'];
				${'otherDispensed'.$cat} = ${'otherDispensed'.$cat} + $row['SUM(d.amount)'];
				${'otherSalesToday'.$cat} = ${'otherSalesToday'.$cat} + $row['SUM(d.quantity)'];
				${'otherSalesTodayReal'.$cat} = ${'otherSalesTodayReal'.$cat} + $row['SUM(d.realQuantity)'];
			
		}
			
		$soldTodayReal = $flowerSalesTodayReal + $extractSalesTodayReal + $otherSalesTodayReal;
			
		// Look up today's bar sales
		$selectBarSales = "SELECT SUM(d.amount), SUM(d.quantity) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime'";
		try
		{
			$result = $pdo3->prepare("$selectBarSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$barSales = $row['SUM(d.amount)'];
			$barUnits = $row['SUM(d.quantity)'];
			
		// FLOWERS STASH
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$stashedInt = $row['SUM(m.quantity)'];
				
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$unStashedInt = $row['SUM(m.quantity)'];
							
	
			$inStashInt = $stashedInt - $unStashedInt;
			$inStashIntFlower = $inStashInt;
			
					
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$stashedExt = $row['SUM(m.quantity)'];
						
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
			$unStashedExt = $row['SUM(m.quantity)'];
						

		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExtFlower = $inStashExt;
		
		$flowerTotal = $flowerWeight + $inStashIntFlower + $inStashExtFlower;
		$flowerTotalWithoutShake = $flowerWeightWithoutShake + $inStashInt + $inStashExt;
		
		
		
		// EXTRACTS
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$stashedInt = $row['SUM(m.quantity)'];
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
			$unStashedInt = $row['SUM(m.quantity)'];
						

		$inStashInt = $stashedInt - $unStashedInt;
		$inStashIntExtracts = $inStashInt;
		
				
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
			$stashedExt = $row['SUM(m.quantity)'];
			

					
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
			$unStashedExt = $row['SUM(m.quantity)'];
						

		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExtExtracts = $inStashExt;

		$extractTotal = $extractWeight + $inStashIntExtracts + $inStashExtExtracts;
		
		// OTHER CATEGORIES
		foreach($catArray as $cat) {
				
			$catID = $cat;
			
			// Calculate what's in internal stash
			$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$stashedInt = $row['SUM(m.quantity)'];
						
			$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$unStashedInt = $row['SUM(m.quantity)'];
							
			$inStashInt = $stashedInt - $unStashedInt;
			
			${'inStashInt'.$cat} = $inStashInt;
			
			// Calculate what's in external stash
			$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$stashedExt = $row['SUM(m.quantity)'];
						
			$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$unStashedExt = $row['SUM(m.quantity)'];
							
	
			$inStashExt = $stashedExt - $unStashedExt;
			${'inStashExt'.$cat} = $inStashExt;
			
			$inStashIntOther = $inStashIntOther + $inStashInt;
			$inStashExtOther = $inStashExtOther + $inStashExt;
			$stashTotal = $stashTotal + $inStashIntOther + $inStashExtOther;
			$weightOtherTotal = $otherWeight + $inStashInt + $inStashExt;
		}
		
		// Aggregates:
		$inStashIntTotal = $inStashIntFlower + $inStashIntExtracts + $inStashIntOther;
		$inStashExtTotal = $inStashExtFlower + $inStashExtExtracts + $inStashExtOther;
		$totalWithShake = $flowerTotal + $extractTotal + $inStashIntOther + $inStashExtOther + $otherWeight;
		$totalWithoutShake = $flowerTotalWithoutShake + $extractTotal + $inStashIntOther + $inStashExtOther + $otherWeight;
		
		
		/****** THEN DAY ******/
		
		// Look up today's sales by category
		$selectSalesFlowers = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.category = '1'";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlowers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$dayflowerSalesToday = $row['SUM(d.amount)'];
			$dayflowerDispensed = $row['SUM(d.quantity)'];
			$dayflowerSalesTodayReal = $row['SUM(d.realQuantity)'];

		$selectSalesExtracts = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.category = '2'";
		try
		{
			$result = $pdo3->prepare("$selectSalesExtracts");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$dayextractSalesToday = $row['SUM(d.amount)'];
			$dayextractDispensed = $row['SUM(d.quantity)'];
			$dayextractSalesTodayReal = $row['SUM(d.realQuantity)'];
			
		// OTHER CATEGORIES			
		foreach($catArray as $cat) {
				
			$catID = $cat;
			
			$selectSalesOther = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$dayclosingtime' AND d.category = $cat";
		try
		{
			$result = $pdo3->prepare("$selectSalesOther");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$dayotherDispensed = $dayotherDispensed + $row['SUM(d.amount)'];
				$dayotherSalesToday = $dayotherSalesToday + $row['SUM(d.quantity)'];
				$dayotherSalesTodayReal = $dayotherSalesTodayReal + $row['SUM(d.realQuantity)'];
				${'dayotherDispensed'.$cat} = ${'dayotherDispensed'.$cat} + $row['SUM(d.amount)'];
				${'dayotherSalesToday'.$cat} = ${'dayotherSalesToday'.$cat} + $row['SUM(d.quantity)'];
				${'dayotherSalesTodayReal'.$cat} = ${'dayotherSalesTodayReal'.$cat} + $row['SUM(d.realQuantity)'];
			
		}
		
		$daysoldTodayReal = $dayflowerSalesTodayReal + $dayextractSalesTodayReal + $dayotherSalesTodayReal;
			
		// Look up today's bar sales
		$selectBarSales = "SELECT SUM(d.amount), SUM(d.quantity) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime'";
		try
		{
			$result = $pdo3->prepare("$selectBarSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$daybarSales = $row['SUM(d.amount)'];
			$daybarUnits = $row['SUM(d.quantity)'];
			
		// FLOWERS STASH
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
				$daystashedInt = $row['SUM(m.quantity)'];
				
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
				$dayunStashedInt = $row['SUM(m.quantity)'];
							
	
			$dayinStashInt = $daystashedInt - $dayunStashedInt;
			$dayinStashIntFlower = $dayinStashInt;
			
					
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
				$daystashedExt = $row['SUM(m.quantity)'];
						
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
			$dayunStashedExt = $row['SUM(m.quantity)'];
						

		$dayinStashExt = $daystashedExt - $dayunStashedExt;
		$dayinStashExtFlower = $dayinStashExt;
		
		$dayflowerTotal = $dayflowerWeight + $dayinStashIntFlower + $dayinStashExtFlower;
		$dayflowerTotalWithoutShake = $dayflowerWeightWithoutShake + $dayinStashInt + $dayinStashExt;
		
		
		
		// EXTRACTS
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
				$daystashedInt = $row['SUM(m.quantity)'];
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
			$dayunStashedInt = $row['SUM(m.quantity)'];
						

		$dayinStashInt = $daystashedInt - $dayunStashedInt;
		$dayinStashIntExtracts = $dayinStashInt;
		
				
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
			$daystashedExt = $row['SUM(m.quantity)'];
			

					
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
			$dayunStashedExt = $row['SUM(m.quantity)'];
						

		$dayinStashExt = $daystashedExt - $dayunStashedExt;
		$dayinStashExtExtracts = $dayinStashExt;

		$dayextractTotal = $dayextractWeight + $dayinStashIntExtracts + $dayinStashExtExtracts;

		// OTHER CATEGORIES
		foreach($catArray as $cat) {
				
			$catID = $cat;
			
			// Calculate what's in internal stash
			$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
				$daystashedInt = $row['SUM(m.quantity)'];
						
			$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
				$dayunStashedInt = $row['SUM(m.quantity)'];
							
			$dayinStashInt = $daystashedInt - $dayunStashedInt;
			
			${'dayinStashInt'.$cat} = $dayinStashInt;
			
			// Calculate what's in external stash
			$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
				$daystashedExt = $row['SUM(m.quantity)'];
						
			$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
				$dayunStashedExt = $row['SUM(m.quantity)'];
							
	
			$dayinStashExt = $daystashedExt - $dayunStashedExt;
			${'dayinStashExt'.$cat} = $dayinStashExt;
			
			$dayinStashIntOther = $dayinStashIntOther + $dayinStashInt;
			$dayinStashExtOther = $dayinStashExtOther + $dayinStashExt;
			$daystashTotal = $daystashTotal + $dayinStashIntOther + $dayinStashExtOther;
		}
		

		// Aggregates:
		$dayinStashIntTotal = $dayinStashIntFlower + $dayinStashIntExtracts + $dayinStashIntOther;
		$dayinStashExtTotal = $dayinStashExtFlower + $dayinStashExtExtracts + $dayinStashExtOther;
		$daytotalWithShake = $dayflowerTotal + $dayextractTotal + $dayinStashIntOther + $dayinStashExtOther + $dayotherWeight;
		$daytotalWithoutShake = $dayflowerTotalWithoutShake + $dayextractTotal + $dayinStashIntOther + $dayinStashExtOther + $dayotherWeight;		
		

		$openingLookup = "SELECT dayClosedNo FROM opening WHERE openingid = $dayopeningid";
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
			$dayClosedNo = $row['dayClosedNo'];
			
		$openingLookup = "SELECT shiftClosedNo FROM shiftopen WHERE openingid = $openingid";
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
			$shiftClosedNo = $row['shiftClosedNo'];
			
		if ($dayClosedNo > 0) {
		
			// Means part of the day has been closed already, so use UPDATE
			
			// Close shift first
			$realClosingtime = date('Y-m-d H:i:s');
			
			$closingid = $dayClosedNo;

		  	$query = sprintf("UPDATE shiftclose SET closingtime = '%s', shiftEnd = '%s', prodOpening = '%f', prodAdded = '%f', prodRemoved = '%f', prodEstStock = '%f', prodStock = '%f', stockDelta = '%f', prodStockFlower = '%f', prodStockExtract = '%f', prodOpeningFlower = '%f', prodOpeningExtract = '%f', prodAddedFlower = '%f', prodAddedExtract = '%f', prodRemovedFlower = '%f', prodRemovedExtract = '%f', prodEstStockFlower = '%f', prodEstStockExtract = '%f', stockDeltaFlower = '%f', stockDeltaExtract = '%f', closedby = '%d', intStash = '%f', extStash = '%f', totalWeight = '%f', totalNoShake = '%f', flowerintStash = '%f', flowerextStash = '%f', flowerweightNoShake = '%f', flowertotalWeight = '%f', flowertotalNoShake = '%f', extractintStash = '%f', extractextStash = '%f', extracttotalWeight = '%f', flowerDispensed = '%f', extractDispensed = '%f', soldTodayFlower = '%f', soldTodayExtract = '%f', soldtodayBar = '%f', unitsSoldBar = '%f', quantitySoldReal = '%f', soldTodayFlowerReal = '%f', soldTodayExtractReal = '%f' WHERE closingid = '%d';",
		  	$realClosingtime, $closingtime, $prodOpening, $prodAdded, $prodRemoved, $prodEstStock, $prodStock, $stockDelta, $flowerWeight, $extractWeight, $prodOpeningFlower, $prodOpeningExtract, $prodAddedFlower, $prodAddedExtract, $prodRemovedFlower, $prodRemovedExtract, $prodEstStockFlower, $prodEstStockExtract, $flowerDelta, $extractDelta, $_SESSION['user_id'], $inStashIntTotal, $inStashExtTotal, $totalWithShake, $totalWithoutShake, $inStashIntFlower, $inStashExtFlower, $flowerWeightWithoutShake, $flowerTotal, $flowerTotalWithoutShake, $inStashIntExtracts, $inStashExtExtracts, $extractTotal, $flowerDispensed, $extractDispensed, $flowerSalesToday, $extractSalesToday, $barSales, $barUnits, $soldTodayReal, $flowerSalesTodayReal, $extractSalesTodayReal, $shiftClosedNo);
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
			
	
			$updateOpening = sprintf("UPDATE shiftopen SET disClosed = 2, disClosedAt = '%s' WHERE openingid = '%d';",
				$realClosingtime,
				$openingid
				);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
			// Delete from closingother
			$deleteOpenOther = "DELETE from shiftcloseother WHERE categoryType = 0 AND closingid = '$shiftClosedNo'";
		try
		{
			$result = $pdo3->prepare("$deleteOpenOther")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			// Insert into closingother
			foreach($catArray as $cat) {
				
				$catID = $cat;			

				$query = sprintf("INSERT INTO shiftcloseother (closingid, category, categoryType, stockDelta, quantitySold, soldtoday, unitsSold, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, intStash, extStash, quantitySoldReal) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
				  $shiftClosedNo, $catID, 0, ${'totDelta'.$cat}, ${'otherSalesToday'.$cat}, ${'otherDispensed'.$cat}, 0, ${'prodOpening'.$cat}, ${'prodAdded'.$cat}, ${'prodRemoved'.$cat}, ${'prodEstStock'.$cat}, ${'totWeight'.$cat}, ${'inStashInt'.$cat}, ${'inStashExt'.$cat}, ${'otherSalesTodayReal'.$cat});
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
				
			// Now close Day
			$realClosingtime2 = date('Y-m-d H:i:s', time() + 5);

		  	$query = sprintf("UPDATE closing SET closingtime = '%s', shiftEnd = '%s', prodOpening = '%f', prodAdded = '%f', prodRemoved = '%f', prodEstStock = '%f', prodStock = '%f', stockDelta = '%f', prodStockFlower = '%f', prodStockExtract = '%f', prodOpeningFlower = '%f', prodOpeningExtract = '%f', prodAddedFlower = '%f', prodAddedExtract = '%f', prodRemovedFlower = '%f', prodRemovedExtract = '%f', prodEstStockFlower = '%f', prodEstStockExtract = '%f', stockDeltaFlower = '%f', stockDeltaExtract = '%f', closedby = '%d', intStash = '%f', extStash = '%f', totalWeight = '%f', totalNoShake = '%f', flowerintStash = '%f', flowerextStash = '%f', flowerweightNoShake = '%f', flowertotalWeight = '%f', flowertotalNoShake = '%f', extractintStash = '%f', extractextStash = '%f', extracttotalWeight = '%f', flowerDispensed = '%f', extractDispensed = '%f', soldTodayFlower = '%f', soldTodayExtract = '%f', soldtodayBar = '%f', unitsSoldBar = '%f', quantitySoldReal = '%f', soldTodayFlowerReal = '%f', soldTodayExtractReal = '%f' WHERE closingid = '%d';",
		  	$realClosingtime2, $closingtime, $dayprodOpening, $dayprodAdded, $dayprodRemoved, $dayprodEstStock, $dayprodStock, $daystockDelta, $dayflowerWeight, $dayextractWeight, $dayprodOpeningFlower, $dayprodOpeningExtract, $dayprodAddedFlower, $dayprodAddedExtract, $dayprodRemovedFlower, $dayprodRemovedExtract, $dayprodEstStockFlower, $dayprodEstStockExtract, $dayflowerDelta, $dayextractDelta, $_SESSION['user_id'], $dayinStashIntTotal, $dayinStashExtTotal, $daytotalWithShake, $daytotalWithoutShake, $dayinStashIntFlower, $dayinStashExtFlower, $dayflowerWeightWithoutShake, $dayflowerTotal, $dayflowerTotalWithoutShake, $dayinStashIntExtracts, $dayinStashExtExtracts, $dayextractTotal, $dayflowerDispensed, $dayextractDispensed, $dayflowerSalesToday, $dayextractSalesToday, $daybarSales, $daybarUnits, $daysoldTodayReal, $dayflowerSalesTodayReal, $dayextractSalesTodayReal, $dayClosedNo);
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

			$updateOpening = sprintf("UPDATE opening SET disClosed = 2, disClosedAt = '%s' WHERE openingid = '%d';",
				$realClosingtime2,
				$dayopeningid
				);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			// Delete from closingother
			$deleteOpenOther = "DELETE from closingother WHERE categoryType = 0 AND closingid = '$closingid'";
		try
		{
			$result = $pdo3->prepare("$deleteOpenOther")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			// Insert into closingother
			foreach($catArray as $cat) {
				
				$catID = $cat;			

				$query = sprintf("INSERT INTO closingother (closingid, category, categoryType, stockDelta, quantitySold, soldtoday, unitsSold, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, intStash, extStash, quantitySoldReal) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
				  $closingid, $catID, 0, ${'daytotDelta'.$cat}, ${'dayotherSalesToday'.$cat}, ${'dayotherDispensed'.$cat}, 0, ${'dayprodOpening'.$cat}, ${'dayprodAdded'.$cat}, ${'dayprodRemoved'.$cat}, ${'dayprodEstStock'.$cat}, ${'daytotWeight'.$cat}, ${'dayinStashInt'.$cat}, ${'dayinStashExt'.$cat}, ${'dayotherSalesTodayReal'.$cat});
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
		
		} else {	
			
			// Close shift first
			$realClosingtime = date('Y-m-d H:i:s');
			
			// Query to add Closing - 37 arguments
			$query = sprintf("INSERT INTO shiftclose (closingtime, shiftEnd, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, stockDelta, prodStockFlower, prodStockExtract, prodOpeningFlower, prodOpeningExtract, prodAddedFlower, prodAddedExtract, prodRemovedFlower, prodRemovedExtract, prodEstStockFlower, prodEstStockExtract, stockDeltaFlower, stockDeltaExtract, closedby, intStash, extStash, totalWeight, totalNoShake, flowerintStash, flowerextStash, flowerweightNoShake, flowertotalWeight, flowertotalNoShake, extractintStash, extractextStash, extracttotalWeight, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, soldtodayBar, unitsSoldBar, quantitySoldReal, soldTodayFlowerReal, soldTodayExtractReal) VALUES ('%s', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
			$realClosingtime, $closingtime, $prodOpening, $prodAdded, $prodRemoved, $prodEstStock, $prodStock, $stockDelta, $flowerWeight, $extractWeight, $prodOpeningFlower, $prodOpeningExtract, $prodAddedFlower, $prodAddedExtract, $prodRemovedFlower, $prodRemovedExtract, $prodEstStockFlower, $prodEstStockExtract, $flowerDelta, $extractDelta, $_SESSION['user_id'], $inStashIntTotal, $inStashExtTotal, $totalWithShake, $totalWithoutShake, $inStashIntFlower, $inStashExtFlower, $flowerWeightWithoutShake, $flowerTotal, $flowerTotalWithoutShake, $inStashIntExtracts, $inStashExtExtracts, $extractTotal, $flowerDispensed, $extractDispensed, $flowerSalesToday, $extractSalesToday, $barSales, $barUnits, $soldTodayReal, $flowerSalesTodayReal, $extractSalesTodayReal);
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
			
		$shiftClosedNo = $pdo3->lastInsertId();
		
		$updateOpening = sprintf("UPDATE shiftopen SET disClosed = 2, disClosedAt = '%s', shiftClosedNo = '%d' WHERE openingid = '%d';",
			$realClosingtime,
			$shiftClosedNo,
			$openingid
			);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			// Insert into closingother
			foreach($catArray as $cat) {
				
				$catID = $cat;			

				$query = sprintf("INSERT INTO shiftcloseother (closingid, category, categoryType, stockDelta, quantitySold, soldtoday, unitsSold, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, intStash, extStash, quantitySoldReal) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
				  $shiftClosedNo, $catID, 0, ${'totDelta'.$cat}, ${'otherSalesToday'.$cat}, ${'otherDispensed'.$cat}, 0, ${'prodOpening'.$cat}, ${'prodAdded'.$cat}, ${'prodRemoved'.$cat}, ${'prodEstStock'.$cat}, ${'totWeight'.$cat}, ${'inStashInt'.$cat}, ${'inStashExt'.$cat}, ${'otherSalesTodayReal'.$cat});
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
		

		// Now close Day
		$realClosingtime2 = date('Y-m-d H:i:s', time() + 5);
		
		$query = sprintf("INSERT INTO closing (closingtime, shiftEnd, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, stockDelta, prodStockFlower, prodStockExtract, prodOpeningFlower, prodOpeningExtract, prodAddedFlower, prodAddedExtract, prodRemovedFlower, prodRemovedExtract, prodEstStockFlower, prodEstStockExtract, stockDeltaFlower, stockDeltaExtract, closedby, intStash, extStash, totalWeight, totalNoShake, flowerintStash, flowerextStash, flowerweightNoShake, flowertotalWeight, flowertotalNoShake, extractintStash, extractextStash, extracttotalWeight, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, soldtodayBar, unitsSoldBar, quantitySoldReal, soldTodayFlowerReal, soldTodayExtractReal) VALUES ('%s', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
		$realClosingtime2, $closingtime, $dayprodOpening, $dayprodAdded, $dayprodRemoved, $dayprodEstStock, $dayprodStock, $daystockDelta, $dayflowerWeight, $dayextractWeight, $dayprodOpeningFlower, $dayprodOpeningExtract, $dayprodAddedFlower, $dayprodAddedExtract, $dayprodRemovedFlower, $dayprodRemovedExtract, $dayprodEstStockFlower, $dayprodEstStockExtract, $dayflowerDelta, $dayextractDelta, $_SESSION['user_id'], $dayinStashIntTotal, $dayinStashExtTotal, $daytotalWithShake, $daytotalWithoutShake, $dayinStashIntFlower, $dayinStashExtFlower, $dayflowerWeightWithoutShake, $dayflowerTotal, $dayflowerTotalWithoutShake, $dayinStashIntExtracts, $dayinStashExtExtracts, $dayextractTotal, $dayflowerDispensed, $dayextractDispensed, $dayflowerSalesToday, $dayextractSalesToday, $daybarSales, $daybarUnits, $daysoldTodayReal, $dayflowerSalesTodayReal, $dayextractSalesTodayReal);
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
			
		$dayClosedNo = $pdo3->lastInsertId();
		
		$updateOpening = sprintf("UPDATE opening SET disClosed = 2, disClosedAt = '%s', dayClosedNo = '%d' WHERE openingid = '%d';",
			$realClosingtime,
			$dayClosedNo,
			$dayopeningid
			);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			// Insert into closingother
			foreach($catArray as $cat) {
				
				$catID = $cat;			

				$query = sprintf("INSERT INTO closingother (closingid, category, categoryType, stockDelta, quantitySold, soldtoday, unitsSold, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, intStash, extStash, quantitySoldReal) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
				  $dayClosedNo, $catID, 0, ${'daytotDelta'.$cat}, ${'dayotherSalesToday'.$cat}, ${'dayotherDispensed'.$cat}, 0, ${'dayprodOpening'.$cat}, ${'dayprodAdded'.$cat}, ${'dayprodRemoved'.$cat}, ${'dayprodEstStock'.$cat}, ${'daytotWeight'.$cat}, ${'dayinStashInt'.$cat}, ${'dayinStashExt'.$cat}, ${'dayotherSalesTodayReal'.$cat});
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
						$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
						$unStashedInt = $row['SUM(m.quantity)'];
									
			
					$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$unStashedExt = $row['SUM(m.quantity)'];
								
		
				$inStashExt = $stashedExt - $unStashedExt;
				
				$prodTotal = $weight + $inStashInt + $inStashExt;
				$prodTotalWithoutShake = $weightWithoutShake + $inStashInt + $inStashExt;

				/****** THEN DAY ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
				$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$dayunStashedExt = $row['SUM(m.quantity)'];
								
		
				$dayinStashExt = $daystashedExt - $dayunStashedExt;
				
				$dayprodTotal = $weight + $dayinStashInt + $dayinStashExt;
				$dayprodTotalWithoutShake = $weightWithoutShake + $dayinStashInt + $dayinStashExt;
					
			
			
		    	// Query to add to shiftclosedetails table - 12 arguments
				$query = sprintf("INSERT INTO shiftclosedetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $shiftClosedNo, $category, $productid, $purchaseid, $weightToday, $addedToday, $soldToday, $takeoutsToday, $weight, $estWeight, $weightDelta, $prodclosecomment, $shake, $inStashInt, $inStashExt, $weightWithoutShake, $prodTotal, $prodTotalWithoutShake, $inMenu);
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
					
		    	// Query to add to closingdetails table - 12 arguments
				$query = sprintf("INSERT INTO closingdetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $dayClosedNo, $category, $productid, $purchaseid, $dayweightToday, $dayaddedToday, $daysoldToday, $daytakeoutsToday, $weight, $dayestWeight, $dayweightDelta, $prodclosecomment, $shake, $dayinStashInt, $dayinStashExt, $weightWithoutShake, $dayprodTotal, $dayprodTotalWithoutShake, $inMenu);
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
		
			} else if ($category == '2') {
	
				/****** SHIFT FIRST ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$unStashedInt = $row['SUM(m.quantity)'];
									
			
				$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$unStashedExt = $row['SUM(m.quantity)'];
									
			
				$inStashExt = $stashedExt - $unStashedExt;
					
				$prodTotal = $weight + $inStashInt + $inStashExt;

				
				/****** THEN DAY ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
				$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$dayunStashedExt = $row['SUM(m.quantity)'];
									
			
				$dayinStashExt = $daystashedExt - $dayunStashedExt;
					
				$dayprodTotal = $weight + $dayinStashInt + $dayinStashExt;

			
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
				$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$dayunStashedExt = $row['SUM(m.quantity)'];
									
			
				$dayinStashExt = $daystashedExt - $dayunStashedExt;
					
				$dayprodTotal = $weight + $dayinStashInt + $dayinStashExt;				
				
				
				
		    	// Query to add to shiftclosedetails table
				$query = sprintf("INSERT INTO shiftclosedetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $shiftClosedNo, $category, $productid, $purchaseid, $weightToday, $addedToday, $soldToday, $takeoutsToday, $weight, $estWeight, $weightDelta, $prodclosecomment, $shake, $inStashInt, $inStashExt, $weightWithoutShake, $prodTotal, $prodTotal, $inMenu);
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
						
		    	// Query to add to closingdetails table
				$query = sprintf("INSERT INTO closingdetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $dayClosedNo, $category, $productid, $purchaseid, $dayweightToday, $dayaddedToday, $daysoldToday, $daytakeoutsToday, $weight, $dayestWeight, $dayweightDelta, $prodclosecomment, $shake, $dayinStashInt, $dayinStashExt, $weightWithoutShake, $dayprodTotal, $dayprodTotal, $inMenu);
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
				
			} else {
	
				/****** SHIFT FIRST ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$unStashedInt = $row['SUM(m.quantity)'];
									
			
				$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$unStashedExt = $row['SUM(m.quantity)'];
									
			
				$inStashExt = $stashedExt - $unStashedExt;
					
				$prodTotal = $weight + $inStashInt + $inStashExt;

			
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$unStashedInt = $row['SUM(m.quantity)'];
									
			
				$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$unStashedExt = $row['SUM(m.quantity)'];
									
			
				$inStashExt = $stashedExt - $unStashedExt;
					
				$prodTotal = $weight + $inStashInt + $inStashExt;
				
				
				
				
				
				
				
				/****** THEN DAY ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
				$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$dayunStashedExt = $row['SUM(m.quantity)'];
									
			
				$dayinStashExt = $daystashedExt - $dayunStashedExt;
					
				$dayprodTotal = $weight + $dayinStashInt + $dayinStashExt;
				
				
				
		    	// Query to add to shiftclosedetails table
				$query = sprintf("INSERT INTO shiftclosedetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $shiftClosedNo, $category, $productid, $purchaseid, $weightToday, $addedToday, $soldToday, $takeoutsToday, $weight, $estWeight, $weightDelta, $prodclosecomment, $shake, $inStashInt, $inStashExt, $weightWithoutShake, $prodTotal, $prodTotal, $inMenu);
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
						
		    	// Query to add to closingdetails table
				$query = sprintf("INSERT INTO closingdetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $dayClosedNo, $category, $productid, $purchaseid, $dayweightToday, $dayaddedToday, $daysoldToday, $daytakeoutsToday, $weight, $dayestWeight, $dayweightDelta, $prodclosecomment, $shake, $dayinStashInt, $dayinStashExt, $weightWithoutShake, $dayprodTotal, $dayprodTotal, $inMenu);
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
		
} // Product loop ends
		
		
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

	pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "step6 dev-align-center", $lang['closeday-dis-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
	$_SESSION['daycloseProduct'] = $_POST['daycloseProduct'];
	
	echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='?saveDispensary' method='POST'><br />";
	echo "<input type='hidden' name='productConfirm' value='yes'><br />";
	
	$i=0;
	
	$catArray = $_POST['catArray'];
	
	foreach($catArray as $value) {
		echo '<input type="hidden" name="catArray[]" value="'. $value. '">';
	}
	
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
				$productStatus = $lang['closeday-main-closed'];
				$radioDisable = "disabled";
			} else if ($inMenu == 0) {
				$disableOrNot = "";
				$productStatus = $lang['not-in-menu'];
				$radioDisable = "";
			} else {
				$disableOrNot = "";
				$productStatus = $lang['pur-inmenu'];
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
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$openingtime' AND '$closingtime' ORDER by purchaseDate DESC";
		try
		{
			$result = $pdo3->prepare("$selectPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$newPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM shiftopendetails d, shiftopen o WHERE o.openingid = $openingid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
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
				$weightToday = $row['weight'];
			
			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
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
				$addedToday = $row['SUM(quantity)'];
				
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
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
				$daysoldToday = $row['SUM(d.realQuantity)'];
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$dayopeningtime' AND '$closingtime' ORDER by purchaseDate DESC";
		try
		{
			$result = $pdo3->prepare("$selectPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$daynewPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE o.openingid = $dayopeningid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
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
				$dayweightToday = $row['weight'];			

			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
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
				$dayaddedToday = $row['SUM(quantity)'];
				
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
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
					echo "<h5>{$lang['global-flowerscaps']}</h5>";
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
		<div class='actionbox-np2'>
		  <div class='mainboxheader'>%s %s ($purchaseid)</div>
		 %s<br />
		 <div class='boxcontent'>
		 <table class='purchasetable'>
		  <tr>
		   <td>{$lang['closeday-openingweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-dispensed']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-takeouts']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}
		  
				<input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit purchaseNumber' value='%0.02f' readonly />
		    <input type='hidden' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' readonly />
		   </td>
		  </tr>
		 </table>
		 </div>
		 <br />

		 {$lang['global-comment']}?<br />
		 <textarea  class='defaultinput' name='confirmedClose[%d][prodclosecomment]'></textarea>
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
			echo "<h5>{$lang['global-extractscaps']}</h5>";
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
		<div class='actionbox-np2'>
		 <div class='mainboxheader'>>%s ($purchaseid)</div>
		 <div class='boxcontent'>
		 <table class='purchasetable'>
		  <tr>
		   <td>{$lang['closeday-openingweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-dispensed']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-takeouts']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}
		   
				<input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit purchaseNumber' value='%0.02f' readonly />
		    <input type='hidden' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' readonly />
		   </td>
		  </tr>
		 </table>
		 </div>
		 <br />
		   {$lang['global-comment']}?<br />
		   <textarea class='defaultinput' name='confirmedClose[%d][prodclosecomment]'></textarea>
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
	    } else {
		    
			if (${'divider'.$category} != 'set') {
				
				${'divider'.$category} = 'set';
				echo "<h5>{$name}</h5>";
				
			}
			
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
		<div class='actionbox-np2'>
		 <div class='mainboxheader'>%s ($purchaseid)</div>
		 <div class='boxcontent'>
		 <table class='purchasetable'>
		  <tr>
		   <td>{$lang['closeday-openingweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-dispensed']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-takeouts']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}
		   
				<input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit purchaseNumber' value='%0.02f' readonly />
		    <input type='hidden' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' readonly />
		   </td>
		  </tr>
		 </table>
		 </div>

		 <br />
		   {$lang['global-comment']}?<br />
		   <textarea class='defaultinput' name='confirmedClose[%d][prodclosecomment]'></textarea>
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
 		echo "<br><button class='cta1' name='oneClick' type='submit'>{$lang['global-confirm']}</button>";
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

	pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "step6 dev-align-center", $lang['closeday-dis-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
	$_SESSION['daycloseProduct'] = $_POST['daycloseProduct'];
	
	echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='?saveDispensary' method='POST'><br />";
	echo "<input type='hidden' name='productConfirm' value='yes'><br />";
	
		$i=0;
		
		$catArray = $_POST['catArray2'];
		
		foreach($catArray as $value) {
			echo '<input type="hidden" name="catArray[]" value="'. $value. '">';
		}
		
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
			
			$auto = $prodClose['auto'];
			
			// Determine if a product was set to auto -- and if so, calculate the weight!!
			
			if ($auto == 1) {
				
			/****** SHIFT FIRST ******/
			
			// Look up todays sales
			$selectSales = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
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
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$openingtime' AND '$closingtime' ORDER by purchaseDate DESC";
		try
		{
			$result = $pdo3->prepare("$selectPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$newPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM shiftopendetails d, shiftopen o WHERE o.openingid = $openingid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
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
				$weightToday = $row['weight'];
			
			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
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
				$addedToday = $row['SUM(quantity)'];
				
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
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
				$takeoutsToday = $row['SUM(quantity)'];
					
			// Exception if there was no weight this morning, we use the new product weight
			if (($weightToday == 0)) {
				$addedToday = $newPurchaseWeight + $addedToday;
			}
				
			// Calculate estimated weight and weight Delta:
			$weight = $weightToday - $soldToday - $takeoutsToday + $addedToday;
			$fullWeight = $weight + $tupperWeight;
			$weightDelta = $weight - $estWeight;
			$estWeight = $weight;
					
			
			/****** NOW DAY ******/
			
			// Look up todays sales
			$selectSales = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE s.saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
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
				$daysoldToday = $row['SUM(d.realQuantity)'];
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$dayopeningtime' AND '$closingtime' ORDER by purchaseDate DESC";
		try
		{
			$result = $pdo3->prepare("$selectPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$daynewPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE o.openingid = $dayopeningid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
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
				$dayweightToday = $row['weight'];			

			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
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
				$dayaddedToday = $row['SUM(quantity)'];
				
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
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
				$daytakeoutsToday = $row['SUM(quantity)'];
					
			// Exception if there was no weight this morning, we use the new product weight
			if (($dayweightToday == 0)) {
				$dayaddedToday = $daynewPurchaseWeight + $dayaddedToday;
			}
				
			// Calculate estimated weight and weight Delta:
			$dayweight = $dayweightToday - $daysoldToday - $daytakeoutsToday + $dayaddedToday;
			$dayweightDelta = $weight - $dayestWeight;
			$dayestWeight = $dayweight;
			
			
			
			
			} else {
				
				$weight = $fullWeight - $tupperWeight;
				
			}			
			
			if ($closed == 'yes') {
				$disableOrNot = "disabled style='color: red'";
				$productStatus = $lang['closeday-main-closed'];
				$radioDisable = "disabled";
			} else if ($inMenu == 0) {
				$disableOrNot = "";
				$productStatus = $lang['not-in-menu'];
				$radioDisable = "";
			} else {
				$disableOrNot = "";
				$productStatus = $lang['pur-inmenu'];
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
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$openingtime' AND '$closingtime' ORDER by purchaseDate DESC";
		try
		{
			$result = $pdo3->prepare("$selectPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$newPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM shiftopendetails d, shiftopen o WHERE o.openingid = $openingid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
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
				$weightToday = $row['weight'];
			
			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
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
				$addedToday = $row['SUM(quantity)'];
				
		
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
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
				$daysoldToday = $row['SUM(d.realQuantity)'];
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$dayopeningtime' AND '$closingtime' ORDER by purchaseDate DESC";
		try
		{
			$result = $pdo3->prepare("$selectPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$daynewPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE o.openingid = $dayopeningid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
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
				$dayweightToday = $row['weight'];
			
			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
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
				$dayaddedToday = $row['SUM(quantity)'];
		
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
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
			echo "<h5>{$lang['global-flowerscaps']}</h5>";
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
		<div class='actionbox-np2'>
		 <div class='mainboxheader'>%s %s ($purchaseid)</div>
		 %s<br />
		 <div class='boxcontent'>
		 <table class='purchasetable'>
		  <tr>
		   <td>{$lang['closeday-openingweight']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-dispensed']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-takeouts']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		
		 <tr>
		  <td>{$lang['weightnow']}
		  	<input type='number' lang='nb' name='confirmedClose[%d][fullWeight]' id='fullWeight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' /></td>
		 </tr>
		 <tr>
		  <td class='red'>- {$lang['jar-weight']}
		  	<input type='number' lang='nb' name='confirmedClose[%d][tupperWeight]' id='tupperWeight%d' class='fourDigit purchaseNumber red' value='%0.02f' step='0.01' /></td>
		 </tr>
		  <tr>
		   <td>{$lang['add-realweight']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' %s readonly /></td>
		  </tr>
		  <tr>
		   <td><strong>{$lang['global-delta']}</strong>
		   	<strong><input type='number' lang='nb' name='confirmedClose[%d][weightDelta]' id='weightDelta%d' class='fourDigit%s purchaseNumber' value='%0.02f' step='0.01' readonly /></strong></td>
		  </tr>
		 </table>
		 </div>
		 <br />
		 {$lang['global-shake']}:<br />
    	<input type='radio' name='confirmedClose[%d][shake]' value='0' style='margin-left: 5px; width: 12px;' %s %s>0%%</input>
    	<input type='radio' name='confirmedClose[%d][shake]' value='25' style='margin-left: 5px; width: 12px;' %s %s>25%%</input><br />
    	<input type='radio' name='confirmedClose[%d][shake]' value='50' style='margin-left: 5px; width: 12px;' %s %s>50%%</input>
    <input type='radio' name='confirmedClose[%d][shake]' value='75' style='margin-left: 5px; width: 12px;' %s %s>75%%</input><br />

		 {$lang['global-comment']}?<br />
		 <textarea class='defaultinput' name='confirmedClose[%d][prodclosecomment]'></textarea>
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
			echo "<h5>{$lang['global-extractscaps']}</h5>";
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
		<div class='actionbox-np2'>
		 <div class='mainboxheader'>%s ($purchaseid)</div>
		 <div class='boxcontent'>
		 <table class='purchasetable'>
		  <tr>
		   <td>{$lang['closeday-openingweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-dispensed']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-takeouts']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		 <tr>
		  <td colspan='2'>&nbsp;</td>
		 </tr>
		 <tr>
		  <td>{$lang['weightnow']}
		  		<input type='number' lang='nb' name='confirmedClose[%d][fullWeight]' id='fullWeight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' /></td>
		 </tr>
		 <tr>
		  <td class='red'>- {$lang['jar-weight']}
		  		<input type='number' lang='nb' name='confirmedClose[%d][tupperWeight]' id='tupperWeight%d' class='fourDigit purchaseNumber red' value='%0.02f' step='0.01' /></td>
		 </tr>
		  <tr>
		   <td>{$lang['add-realweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' %s readonly /><br />
		  </tr>
		  <tr>
		   <td><strong>{$lang['global-delta']}:</strong>
		   	<strong><input type='number' lang='nb' name='confirmedClose[%d][weightDelta]' id='weightDelta%d' class='fourDigit%s purchaseNumber' value='%0.02f' step='0.01' readonly /></strong><br />
		  </tr>
		 </table>
		 </div>
		 <br />
		   {$lang['global-comment']}?<br />
		   <textarea class='defaultinput' name='confirmedClose[%d][prodclosecomment]'></textarea>
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
	  
	    } else {
		    
			if (${'divider'.$category} != 'set') {
				
				${'divider'.$category} = 'set';
				echo "<h5>{$name}</h5>";
				
			}
			
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
		<div class='actionbox-np2'>
		 <div class='mainboxheader'>%s ($purchaseid)</div>
		 <div class='boxcontent'>
		 <table class='purchasetable'>
		  <tr>
		   <td>{$lang['closeday-openingweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-dispensed']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-takeouts']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		 <tr>
		  <td>{$lang['weightnow']}
		  		<input type='number' lang='nb' name='confirmedClose[%d][fullWeight]' id='fullWeight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' /></td>
		 </tr>
		 <tr>
		  <td class='red'>- {$lang['jar-weight']}
		  		<input type='number' lang='nb' name='confirmedClose[%d][tupperWeight]' id='tupperWeight%d' class='fourDigit purchaseNumber red' value='%0.02f' step='0.01' /></td>
		 </tr>
		  <tr>
		   <td>{$lang['add-realweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' %s readonly /><br />
		  </tr>
		  <tr>
		   <td><strong>{$lang['global-delta']}:</strong>
		   		<strong><input type='number' lang='nb' name='confirmedClose[%d][weightDelta]' id='weightDelta%d' class='fourDigit%s purchaseNumber' value='%0.02f' step='0.01' readonly /></strong><br />
		  </tr>
		 </table>
		 </div>
		 <br />
		   {$lang['global-comment']}?<br />
		   <textarea class='defaultinput' name='confirmedClose[%d][prodclosecomment]'></textarea>
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
 		echo "<br><button class='cta1' name='oneClick' type='submit'>{$lang['global-confirm']}</button>";
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
		
			$catArray = $_POST['catArray'];

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
				$flowerDispensed = $flowerDispensed + $soldToday;
				$weightWithoutShake = ($weight - ($weight * ($shake / 100)));
				$flowerWeightWithoutShake = $flowerWeightWithoutShake + $weightWithoutShake;
				
				$dayprodOpeningFlower = $dayprodOpeningFlower + $dayweightToday;
				$dayprodAddedFlower = $dayprodAddedFlower + $dayaddedToday;
				$dayprodRemovedFlower = $dayprodRemovedFlower + $daytakeoutsToday;
				$dayprodEstStockFlower = $dayprodEstStockFlower + $dayestWeight;
				$dayflowerWeight = $dayflowerWeight + $weight;
				$dayflowerDelta = $dayflowerDelta + $dayweightDelta;
				$dayflowerDispensed = $dayflowerDispensed + $daysoldToday;

			} else if ($category == '2') {
				
				$prodOpeningExtract = $prodOpeningExtract + $weightToday;
				$prodAddedExtract = $prodAddedExtract + $addedToday;
				$prodRemovedExtract = $prodRemovedExtract + $takeoutsToday;
				$prodEstStockExtract = $prodEstStockExtract + $estWeight;
				$extractWeight = $extractWeight + $weight;
				$extractDelta = $extractDelta + $weightDelta;
				$extractDispensed = $extractDispensed + $soldToday;
				
				$dayprodOpeningExtract = $dayprodOpeningExtract + $dayweightToday;
				$dayprodAddedExtract = $dayprodAddedExtract + $dayaddedToday;
				$dayprodRemovedExtract = $dayprodRemovedExtract + $daytakeoutsToday;
				$dayprodEstStockExtract = $dayprodEstStockExtract + $dayestWeight;
				$dayextractWeight = $dayextractWeight + $weight;
				$dayextractDelta = $dayextractDelta + $dayweightDelta;
				$dayextractDispensed = $dayextractDispensed + $soldToday;
				
			} else {
				
				${'prodOpening'.$category} = ${'prodOpening'.$category} + $weightToday;
				${'prodAdded'.$category} = ${'prodAdded'.$category} + $addedToday;
				${'prodRemoved'.$category} = ${'prodRemoved'.$category} + $takeoutsToday;
				${'prodEstStock'.$category} = ${'prodEstStock'.$category} + $estWeight;
				${'totWeight'.$category} = ${'totWeight'.$category} + $weight;
				${'totDelta'.$category} = ${'totDelta'.$category} + $weightDelta;
				${'totDispensed'.$category} = ${'totDispensed'.$category} + $soldToday;
					
				$prodOpeningother = $prodOpeningother + $weightToday;
				$prodAddedother = $prodAddedother + $addedToday;
				$prodRemovedother = $prodRemovedother + $takeoutsToday;
				$prodEstStockother = $prodEstStockother + $estWeight;
				$otherWeight = $otherWeight + $weight;
				$otherDelta = $otherDelta + $weightDelta;
				$otherDispensed = $otherDispensed + $soldToday;
				
				${'dayprodOpening'.$category} = ${'dayprodOpening'.$category} + $dayweightToday;
				${'dayprodAdded'.$category} = ${'dayprodAdded'.$category} + $dayaddedToday;
				${'dayprodRemoved'.$category} = ${'dayprodRemoved'.$category} + $daytakeoutsToday;
				${'dayprodEstStock'.$category} = ${'dayprodEstStock'.$category} + $dayestWeight;
				${'daytotWeight'.$category} = ${'daytotWeight'.$category} + $weight;
				${'daytotDelta'.$category} = ${'daytotDelta'.$category} + $dayweightDelta;
				${'daytotDispensed'.$category} = ${'daytotDispensed'.$category} + $daysoldToday;
					
				$dayprodOpeningother = $dayprodOpeningother + $dayweightToday;
				$dayprodAddedother = $dayprodAddedother + $dayaddedToday;
				$dayprodRemovedother = $dayprodRemovedother + $daytakeoutsToday;
				$dayprodEstStockother = $dayprodEstStockother + $dayestWeight;
				$dayotherWeight = $dayotherWeight + $weight;
				$dayotherDelta = $dayotherDelta + $dayweightDelta;
				$dayotherDispensed = $dayotherDispensed + $daysoldToday;
				
			}

		$prodOpening = $prodOpeningFlower + $prodOpeningExtract + $prodOpeningother;
		$prodAdded = $prodAddedFlower + $prodAddedExtract + $prodAddedother;
		$prodRemoved = $prodRemovedFlower + $prodRemovedExtract + $prodRemovedother;
		$prodEstStock = $prodEstStockFlower + $prodEstStockExtract + $prodEstStockother;
		$prodStock = $flowerWeight + $extractWeight + $otherWeight;
		$stockDelta = $flowerDelta + $extractDelta + $otherDelta;
		
		$dayprodOpening = $dayprodOpeningFlower + $dayprodOpeningExtract + $dayprodOpeningother;
		$dayprodAdded = $dayprodAddedFlower + $dayprodAddedExtract + $dayprodAddedother;
		$dayprodRemoved = $dayprodRemovedFlower + $dayprodRemovedExtract + $dayprodRemovedother;
		$dayprodEstStock = $dayprodEstStockFlower + $dayprodEstStockExtract + $dayprodEstStockother;
		$dayprodStock = $dayflowerWeight + $dayextractWeight + $dayotherWeight;
		$daystockDelta = $dayflowerDelta + $dayextractDelta + $dayotherDelta;

}		

			
		/****** SHIFT FIRST ******/
		
		// Look up today's sales by category	
		$selectSalesFlowers = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = '1'";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlowers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$flowerSalesToday = $row['SUM(d.amount)'];
			$flowerDispensed = $row['SUM(d.quantity)'];

		$selectSalesExtracts = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = '2'";
		try
		{
			$result = $pdo3->prepare("$selectSalesExtracts");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$extractSalesToday = $row['SUM(d.amount)'];
			$extractDispensed = $row['SUM(d.quantity)'];
			
		// OTHER CATEGORIES			
		foreach($catArray as $cat) {
				
			$catID = $cat;
			
			$selectSalesOther = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = $cat";
		try
		{
			$result = $pdo3->prepare("$selectSalesOther");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$otherDispensed = $otherDispensed + $row['SUM(d.amount)'];
				$otherSalesToday = $otherSalesToday + $row['SUM(d.quantity)'];
				${'otherDispensed'.$cat} = ${'otherDispensed'.$cat} + $row['SUM(d.amount)'];
				${'otherSalesToday'.$cat} = ${'otherSalesToday'.$cat} + $row['SUM(d.quantity)'];
			
		}
			
		// Look up today's bar sales
		$selectBarSales = "SELECT SUM(d.amount), SUM(d.quantity) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime'";
		try
		{
			$result = $pdo3->prepare("$selectBarSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$barSales = $row['SUM(d.amount)'];
			$barUnits = $row['SUM(d.quantity)'];
			
		// FLOWERS STASH
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$stashedInt = $row['SUM(m.quantity)'];
				
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$unStashedInt = $row['SUM(m.quantity)'];
							
	
			$inStashInt = $stashedInt - $unStashedInt;
			$inStashIntFlower = $inStashInt;
			
					
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$stashedExt = $row['SUM(m.quantity)'];
						
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
			$unStashedExt = $row['SUM(m.quantity)'];
						

		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExtFlower = $inStashExt;
		
		$flowerTotal = $flowerWeight + $inStashIntFlower + $inStashExtFlower;
		$flowerTotalWithoutShake = $flowerWeightWithoutShake + $inStashInt + $inStashExt;
			
	
			
		// EXTRACTS
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$stashedInt = $row['SUM(m.quantity)'];
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
			$unStashedInt = $row['SUM(m.quantity)'];
						

		$inStashInt = $stashedInt - $unStashedInt;
		$inStashIntExtracts = $inStashInt;
			
		
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
			$stashedExt = $row['SUM(m.quantity)'];
				
	
						
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
			$unStashedExt = $row['SUM(m.quantity)'];
						

		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExtExtracts = $inStashExt;

		$extractTotal = $extractWeight + $inStashIntExtracts + $inStashExtExtracts;
		
		// OTHER CATEGORIES
		foreach($catArray as $cat) {
				
			$catID = $cat;
			
			// Calculate what's in internal stash
			$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$stashedInt = $row['SUM(m.quantity)'];
						
			$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$unStashedInt = $row['SUM(m.quantity)'];
							
			$inStashInt = $stashedInt - $unStashedInt;
			
			${'inStashInt'.$cat} = $inStashInt;
			
			// Calculate what's in external stash
			$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$stashedExt = $row['SUM(m.quantity)'];
						
			$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
				$unStashedExt = $row['SUM(m.quantity)'];
							
	
			$inStashExt = $stashedExt - $unStashedExt;
			${'inStashExt'.$cat} = $inStashExt;
			
			$inStashIntOther = $inStashIntOther + $inStashInt;
			$inStashExtOther = $inStashExtOther + $inStashExt;
			$stashTotal = $stashTotal + $inStashIntOther + $inStashExtOther;
			$weightOtherTotal = $otherWeight + $inStashInt + $inStashExt;
		}

		// Aggregates:
		$inStashIntTotal = $inStashIntFlower + $inStashIntExtracts + $inStashIntOther;
		$inStashExtTotal = $inStashExtFlower + $inStashExtExtracts + $inStashExtOther;
		$totalWithShake = $flowerTotal + $extractTotal + $inStashIntOther + $inStashExtOther + $otherWeight;
		$totalWithoutShake = $flowerTotalWithoutShake + $extractTotal + $inStashIntOther + $inStashExtOther + $otherWeight;		
		
		
		/****** THEN DAY ******/
		
		// Look up today's sales by category	
		$selectSalesFlowers = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.category = '1'";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlowers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$dayflowerSalesToday = $row['SUM(d.amount)'];
			$dayflowerDispensed = $row['SUM(d.quantity)'];

		$selectSalesExtracts = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.category = '2'";
		try
		{
			$result = $pdo3->prepare("$selectSalesExtracts");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$dayextractSalesToday = $row['SUM(d.amount)'];
			$dayextractDispensed = $row['SUM(d.quantity)'];
			
		// OTHER CATEGORIES			
		foreach($catArray as $cat) {
				
			$catID = $cat;
			
			$selectSalesOther = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$dayclosingtime' AND d.category = $cat";
		try
		{
			$result = $pdo3->prepare("$selectSalesOther");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$dayotherDispensed = $dayotherDispensed + $row['SUM(d.amount)'];
				$dayotherSalesToday = $dayotherSalesToday + $row['SUM(d.quantity)'];
				${'dayotherDispensed'.$cat} = ${'dayotherDispensed'.$cat} + $row['SUM(d.amount)'];
				${'dayotherSalesToday'.$cat} = ${'dayotherSalesToday'.$cat} + $row['SUM(d.quantity)'];
			
		}
			
		// Look up today's bar sales
		$selectBarSales = "SELECT SUM(d.amount), SUM(d.quantity) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime'";
		try
		{
			$result = $pdo3->prepare("$selectBarSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$daybarSales = $row['SUM(d.amount)'];
			$daybarUnits = $row['SUM(d.quantity)'];
			
		// FLOWERS STASH
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
				$daystashedInt = $row['SUM(m.quantity)'];
				
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
				$dayunStashedInt = $row['SUM(m.quantity)'];
							
	
			$dayinStashInt = $daystashedInt - $dayunStashedInt;
			$dayinStashIntFlower = $dayinStashInt;
			
					
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
			$daystashedExt = $row['SUM(m.quantity)'];
						
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
			$dayunStashedExt = $row['SUM(m.quantity)'];
						

		$dayinStashExt = $daystashedExt - $dayunStashedExt;
		$dayinStashExtFlower = $dayinStashExt;
		
		$dayflowerTotal = $dayflowerWeight + $dayinStashIntFlower + $dayinStashExtFlower;
		$dayflowerTotalWithoutShake = $dayflowerWeightWithoutShake + $dayinStashInt + $dayinStashExt;
			
	
			
		// EXTRACTS
		// Calculate what's in internal stash
		$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
			$daystashedInt = $row['SUM(m.quantity)'];
						
		$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
			$dayunStashedInt = $row['SUM(m.quantity)'];
						

		$dayinStashInt = $daystashedInt - $dayunStashedInt;
		$dayinStashIntExtracts = $dayinStashInt;
			
		
		// Calculate what's in external stash
		$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
			$daystashedExt = $row['SUM(m.quantity)'];
				
	
						
		$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
			$dayunStashedExt = $row['SUM(m.quantity)'];
						

		$dayinStashExt = $daystashedExt - $dayunStashedExt;
		$dayinStashExtExtracts = $dayinStashExt;

		$dayextractTotal = $dayextractWeight + $dayinStashIntExtracts + $dayinStashExtExtracts;
		
		// OTHER CATEGORIES
		foreach($catArray as $cat) {
				
			$catID = $cat;
			
			// Calculate what's in internal stash
			$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
				$daystashedInt = $row['SUM(m.quantity)'];
						
			$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
				$dayunStashedInt = $row['SUM(m.quantity)'];
							
			$dayinStashInt = $daystashedInt - $dayunStashedInt;
			
			${'dayinStashInt'.$cat} = $dayinStashInt;
			
			// Calculate what's in external stash
			$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
				$daystashedExt = $row['SUM(m.quantity)'];
						
			$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $cat AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
				$dayunStashedExt = $row['SUM(m.quantity)'];
							
	
			$dayinStashExt = $daystashedExt - $dayunStashedExt;
			${'dayinStashExt'.$cat} = $dayinStashExt;
			
			$dayinStashIntOther = $dayinStashIntOther + $dayinStashInt;
			$dayinStashExtOther = $dayinStashExtOther + $dayinStashExt;
			$daystashTotal = $daystashTotal + $dayinStashIntOther + $dayinStashExtOther;
			$dayweightOtherTotal = $dayotherWeight + $dayinStashInt + $dayinStashExt;
		}
		
		// Aggregates:
		$dayinStashIntTotal = $dayinStashIntFlower + $dayinStashIntExtracts + $dayinStashIntOther;
		$dayinStashExtTotal = $dayinStashExtFlower + $dayinStashExtExtracts + $dayinStashExtOther;
		$daytotalWithShake = $dayflowerTotal + $dayextractTotal + $dayinStashIntOther + $dayinStashExtOther + $dayotherWeight;
		$daytotalWithoutShake = $dayflowerTotalWithoutShake + $dayextractTotal + $dayinStashIntOther + $dayinStashExtOther + $dayotherWeight;		
		
		$openingLookup = "SELECT dayClosedNo FROM opening WHERE openingid = $dayopeningid";
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
			$dayClosedNo = $row['dayClosedNo'];
			
		$openingLookup = "SELECT shiftClosedNo FROM shiftopen WHERE openingid = $openingid";
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
			$shiftClosedNo = $row['shiftClosedNo'];
			
		if ($dayClosedNo > 0) {
		
			// Means part of the day has been closed already, so use UPDATE
			
			// Close shift first
			$realClosingtime = date('Y-m-d H:i:s');
			
			$closingid = $dayClosedNo;

		  	$query = sprintf("UPDATE shiftclose SET closingtime = '%s', shiftEnd = '%s', prodOpening = '%f', prodAdded = '%f', prodRemoved = '%f', prodEstStock = '%f', prodStock = '%f', stockDelta = '%f', prodStockFlower = '%f', prodStockExtract = '%f', prodOpeningFlower = '%f', prodOpeningExtract = '%f', prodAddedFlower = '%f', prodAddedExtract = '%f', prodRemovedFlower = '%f', prodRemovedExtract = '%f', prodEstStockFlower = '%f', prodEstStockExtract = '%f', stockDeltaFlower = '%f', stockDeltaExtract = '%f', closedby = '%d', intStash = '%f', extStash = '%f', totalWeight = '%f', totalNoShake = '%f', flowerintStash = '%f', flowerextStash = '%f', flowerweightNoShake = '%f', flowertotalWeight = '%f', flowertotalNoShake = '%f', extractintStash = '%f', extractextStash = '%f', extracttotalWeight = '%f', flowerDispensed = '%f', extractDispensed = '%f', soldTodayFlower = '%f', soldTodayExtract = '%f', soldtodayBar = '%f', unitsSoldBar = '%f', quantitySoldReal = '%f', soldTodayFlowerReal = '%f', soldTodayExtractReal = '%f' WHERE closingid = '%d';",
		  	$realClosingtime, $closingtime, $prodOpening, $prodAdded, $prodRemoved, $prodEstStock, $prodStock, $stockDelta, $flowerWeight, $extractWeight, $prodOpeningFlower, $prodOpeningExtract, $prodAddedFlower, $prodAddedExtract, $prodRemovedFlower, $prodRemovedExtract, $prodEstStockFlower, $prodEstStockExtract, $flowerDelta, $extractDelta, $_SESSION['user_id'], $inStashIntTotal, $inStashExtTotal, $totalWithShake, $totalWithoutShake, $inStashIntFlower, $inStashExtFlower, $flowerWeightWithoutShake, $flowerTotal, $flowerTotalWithoutShake, $inStashIntExtracts, $inStashExtExtracts, $extractTotal, $flowerDispensed, $extractDispensed, $flowerSalesToday, $extractSalesToday, $barSales, $barUnits, $flowerDispensed + $extractDispensed + $otherDispensed, $flowerDispensed, $extractDispensed, $shiftClosedNo);
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
	
			$updateOpening = sprintf("UPDATE shiftopen SET disClosed = 2, disClosedAt = '%s' WHERE openingid = '%d';",
				$realClosingtime,
				$openingid
				);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
			// Delete from closingother
			$deleteOpenOther = "DELETE from shiftcloseother WHERE categoryType = 0 AND closingid = '$shiftClosedNo'";
		try
		{
			$result = $pdo3->prepare("$deleteOpenOther")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			// Insert into closingother
			foreach($catArray as $cat) {
				
				$catID = $cat;			

				$query = sprintf("INSERT INTO shiftcloseother (closingid, category, categoryType, stockDelta, quantitySold, soldtoday, unitsSold, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, intStash, extStash, quantitySoldReal) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
				  $shiftClosedNo, $catID, 0, ${'totDelta'.$cat}, ${'otherSalesToday'.$cat}, ${'otherDispensed'.$cat}, 0, ${'prodOpening'.$cat}, ${'prodAdded'.$cat}, ${'prodRemoved'.$cat}, ${'prodEstStock'.$cat}, ${'totWeight'.$cat}, ${'inStashInt'.$cat}, ${'inStashExt'.$cat}, ${'totDispensed'.$cat});
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
							
			// Now close Day
			$realClosingtime2 = date('Y-m-d H:i:s', time() + 5);

		  	$query = sprintf("UPDATE closing SET closingtime = '%s', shiftEnd = '%s', prodOpening = '%f', prodAdded = '%f', prodRemoved = '%f', prodEstStock = '%f', prodStock = '%f', stockDelta = '%f', prodStockFlower = '%f', prodStockExtract = '%f', prodOpeningFlower = '%f', prodOpeningExtract = '%f', prodAddedFlower = '%f', prodAddedExtract = '%f', prodRemovedFlower = '%f', prodRemovedExtract = '%f', prodEstStockFlower = '%f', prodEstStockExtract = '%f', stockDeltaFlower = '%f', stockDeltaExtract = '%f', closedby = '%d', intStash = '%f', extStash = '%f', totalWeight = '%f', totalNoShake = '%f', flowerintStash = '%f', flowerextStash = '%f', flowerweightNoShake = '%f', flowertotalWeight = '%f', flowertotalNoShake = '%f', extractintStash = '%f', extractextStash = '%f', extracttotalWeight = '%f', flowerDispensed = '%f', extractDispensed = '%f', soldTodayFlower = '%f', soldTodayExtract = '%f', soldtodayBar = '%f', unitsSoldBar = '%f', quantitySoldReal = '%f', soldTodayFlowerReal = '%f', soldTodayExtractReal = '%f' WHERE closingid = '%d';",
		  	$realClosingtime2, $closingtime, $dayprodOpening, $dayprodAdded, $dayprodRemoved, $dayprodEstStock, $dayprodStock, $daystockDelta, $dayflowerWeight, $dayextractWeight, $dayprodOpeningFlower, $dayprodOpeningExtract, $dayprodAddedFlower, $dayprodAddedExtract, $dayprodRemovedFlower, $dayprodRemovedExtract, $dayprodEstStockFlower, $dayprodEstStockExtract, $dayflowerDelta, $dayextractDelta, $_SESSION['user_id'], $dayinStashIntTotal, $dayinStashExtTotal, $daytotalWithShake, $daytotalWithoutShake, $dayinStashIntFlower, $dayinStashExtFlower, $dayflowerWeightWithoutShake, $dayflowerTotal, $dayflowerTotalWithoutShake, $dayinStashIntExtracts, $dayinStashExtExtracts, $dayextractTotal, $dayflowerDispensed, $dayextractDispensed, $dayflowerSalesToday, $dayextractSalesToday, $daybarSales, $daybarUnits, $dayflowerDispensed + $dayextractDispensed + $dayotherDispensed, $dayflowerDispensed, $dayextractDispensed, $dayClosedNo);
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

			$updateOpening = sprintf("UPDATE opening SET disClosed = 2, disClosedAt = '%s' WHERE openingid = '%d';",
				$realClosingtime2,
				$dayopeningid
				);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

			// Delete from closingother
			$deleteOpenOther = "DELETE from closingother WHERE categoryType = 0 AND closingid = '$closingid'";
		try
		{
			$result = $pdo3->prepare("$deleteOpenOther")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			// Insert into closingother
			foreach($catArray as $cat) {
				
				$catID = $cat;
				
				$query = sprintf("INSERT INTO closingother (closingid, category, categoryType, stockDelta, quantitySold, soldtoday, unitsSold, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, intStash, extStash, quantitySoldReal) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
				  $closingid, $catID, 0, ${'daytotDelta'.$cat}, ${'daytotDispensed'.$cat}, ${'daysoldToday'.$cat}, 0, ${'dayprodOpening'.$cat}, ${'dayprodAdded'.$cat}, ${'dayprodRemoved'.$cat}, ${'dayprodEstStock'.$cat}, ${'daytotWeight'.$cat}, ${'dayinStashInt'.$cat}, ${'dayinStashExt'.$cat}, ${'daytotDispensed'.$cat});
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
				
						
		} else {	
			
			// Close shift first
			$realClosingtime = date('Y-m-d H:i:s');
			
			// Query to add Closing - 37 arguments
			$query = sprintf("INSERT INTO shiftclose (closingtime, shiftEnd, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, stockDelta, prodStockFlower, prodStockExtract, prodOpeningFlower, prodOpeningExtract, prodAddedFlower, prodAddedExtract, prodRemovedFlower, prodRemovedExtract, prodEstStockFlower, prodEstStockExtract, stockDeltaFlower, stockDeltaExtract, closedby, intStash, extStash, totalWeight, totalNoShake, flowerintStash, flowerextStash, flowerweightNoShake, flowertotalWeight, flowertotalNoShake, extractintStash, extractextStash, extracttotalWeight, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, soldtodayBar, unitsSoldBar, quantitySoldReal, soldTodayFlowerReal, soldTodayExtractReal) VALUES ('%s', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
			$realClosingtime, $closingtime, $prodOpening, $prodAdded, $prodRemoved, $prodEstStock, $prodStock, $stockDelta, $flowerWeight, $extractWeight, $prodOpeningFlower, $prodOpeningExtract, $prodAddedFlower, $prodAddedExtract, $prodRemovedFlower, $prodRemovedExtract, $prodEstStockFlower, $prodEstStockExtract, $flowerDelta, $extractDelta, $_SESSION['user_id'], $inStashIntTotal, $inStashExtTotal, $totalWithShake, $totalWithoutShake, $inStashIntFlower, $inStashExtFlower, $flowerWeightWithoutShake, $flowerTotal, $flowerTotalWithoutShake, $inStashIntExtracts, $inStashExtExtracts, $extractTotal, $flowerDispensed, $extractDispensed, $flowerSalesToday, $extractSalesToday, $barSales, $barUnits, $flowerDispensed + $extractDispensed + $otherDispensed, $flowerDispensed, $extractDispensed);
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
			
		$shiftClosedNo = $pdo3->lastInsertId();
		
		$updateOpening = sprintf("UPDATE shiftopen SET disClosed = 2, disClosedAt = '%s', shiftClosedNo = '%d' WHERE openingid = '%d';",
			$realClosingtime,
			$shiftClosedNo,
			$openingid
			);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// Insert into closingother
		foreach($catArray as $cat) {
			
			$catID = $cat;			

			$query = sprintf("INSERT INTO shiftcloseother (closingid, category, categoryType, stockDelta, quantitySold, soldtoday, unitsSold, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, intStash, extStash, quantitySoldReal) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
			  $shiftClosedNo, $catID, 0, ${'totDelta'.$cat}, ${'otherSalesToday'.$cat}, ${'otherDispensed'.$cat}, 0, ${'prodOpening'.$cat}, ${'prodAdded'.$cat}, ${'prodRemoved'.$cat}, ${'prodEstStock'.$cat}, ${'totWeight'.$cat}, ${'inStashInt'.$cat}, ${'inStashExt'.$cat}, ${'totDispensed'.$cat});
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
		

		// Now close Day
		$realClosingtime2 = date('Y-m-d H:i:s', time() + 5);
		
		$query = sprintf("INSERT INTO closing (closingtime, shiftEnd, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, stockDelta, prodStockFlower, prodStockExtract, prodOpeningFlower, prodOpeningExtract, prodAddedFlower, prodAddedExtract, prodRemovedFlower, prodRemovedExtract, prodEstStockFlower, prodEstStockExtract, stockDeltaFlower, stockDeltaExtract, closedby, intStash, extStash, totalWeight, totalNoShake, flowerintStash, flowerextStash, flowerweightNoShake, flowertotalWeight, flowertotalNoShake, extractintStash, extractextStash, extracttotalWeight, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, soldtodayBar, unitsSoldBar, quantitySoldReal, soldTodayFlowerReal, soldTodayExtractReal) VALUES ('%s', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
		$realClosingtime2, $closingtime, $dayprodOpening, $dayprodAdded, $dayprodRemoved, $dayprodEstStock, $dayprodStock, $daystockDelta, $dayflowerWeight, $dayextractWeight, $dayprodOpeningFlower, $dayprodOpeningExtract, $dayprodAddedFlower, $dayprodAddedExtract, $dayprodRemovedFlower, $dayprodRemovedExtract, $dayprodEstStockFlower, $dayprodEstStockExtract, $dayflowerDelta, $dayextractDelta, $_SESSION['user_id'], $dayinStashIntTotal, $dayinStashExtTotal, $daytotalWithShake, $daytotalWithoutShake, $dayinStashIntFlower, $dayinStashExtFlower, $dayflowerWeightWithoutShake, $dayflowerTotal, $dayflowerTotalWithoutShake, $dayinStashIntExtracts, $dayinStashExtExtracts, $dayextractTotal, $dayflowerDispensed, $dayextractDispensed, $dayflowerSalesToday, $dayextractSalesToday, $daybarSales, $daybarUnits, $dayflowerDispensed + $dayextractDispensed + $otherDispensed, $dayflowerDispensed, $dayextractDispensed);
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
			
		$dayClosedNo = $pdo3->lastInsertId();
		
		$updateOpening = sprintf("UPDATE opening SET disClosed = 2, disClosedAt = '%s', dayClosedNo = '%d' WHERE openingid = '%d';",
			$realClosingtime,
			$dayClosedNo,
			$dayopeningid
			);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			// Insert into closingother
			foreach($catArray as $cat) {
				
				$catID = $cat;
				
				$query = sprintf("INSERT INTO closingother (closingid, category, categoryType, stockDelta, quantitySold, soldtoday, unitsSold, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, intStash, extStash, quantitySoldReal) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
				  $dayClosedNo, $catID, 0, ${'daytotDelta'.$cat}, ${'daytotDispensed'.$cat}, ${'daysoldToday'.$cat}, 0, ${'dayprodOpening'.$cat}, ${'dayprodAdded'.$cat}, ${'dayprodRemoved'.$cat}, ${'dayprodEstStock'.$cat}, ${'daytotWeight'.$cat}, ${'dayinStashInt'.$cat}, ${'dayinStashExt'.$cat}, ${'daytotDispensed'.$cat});
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
						$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
						$unStashedInt = $row['SUM(m.quantity)'];
									
			
					$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$unStashedExt = $row['SUM(m.quantity)'];
									
			
				$inStashExt = $stashedExt - $unStashedExt;
				
				$prodTotal = $weight + $inStashInt + $inStashExt;
				$prodTotalWithoutShake = $weightWithoutShake + $inStashInt + $inStashExt;

				/****** THEN DAY ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
						$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
						$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
					$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$dayunStashedExt = $row['SUM(m.quantity)'];
									
			
				$dayinStashExt = $daystashedExt - $dayunStashedExt;
				
				$dayprodTotal = $weight + $dayinStashInt + $dayinStashExt;
				$dayprodTotalWithoutShake = $weightWithoutShake + $dayinStashInt + $dayinStashExt;
					
			
			
		    	// Query to add to shiftclosedetails table - 12 arguments
				$query = sprintf("INSERT INTO shiftclosedetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $shiftClosedNo, $category, $productid, $purchaseid, $weightToday, $addedToday, $soldToday, $takeoutsToday, $weight, $estWeight, $weightDelta, $prodclosecomment, $shake, $inStashInt, $inStashExt, $weightWithoutShake, $prodTotal, $prodTotalWithoutShake, $inMenu);
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
					
		    	// Query to add to closingdetails table - 12 arguments
				$query = sprintf("INSERT INTO closingdetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $dayClosedNo, $category, $productid, $purchaseid, $dayweightToday, $dayaddedToday, $daysoldToday, $daytakeoutsToday, $weight, $dayestWeight, $dayweightDelta, $prodclosecomment, $shake, $dayinStashInt, $dayinStashExt, $weightWithoutShake, $dayprodTotal, $dayprodTotalWithoutShake, $inMenu);
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
		
			} else if ($category == '2') {
	
				/****** SHIFT FIRST ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$unStashedInt = $row['SUM(m.quantity)'];
									
			
				$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$unStashedExt = $row['SUM(m.quantity)'];
									
			
				$inStashExt = $stashedExt - $unStashedExt;
					
				$prodTotal = $weight + $inStashInt + $inStashExt;

			
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$unStashedInt = $row['SUM(m.quantity)'];
									
			
				$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
					$unStashedExt = $row['SUM(m.quantity)'];
									
			
				$inStashExt = $stashedExt - $unStashedExt;
					
				$prodTotal = $weight + $inStashInt + $inStashExt;
				
				
				
				
				
				
				
				/****** THEN DAY ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
				$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
					$dayunStashedExt = $row['SUM(m.quantity)'];
									
			
				$dayinStashExt = $daystashedExt - $dayunStashedExt;
					
				$dayprodTotal = $weight + $dayinStashInt + $dayinStashExt;
			
		    	// Query to add to shiftclosedetails table
				$query = sprintf("INSERT INTO shiftclosedetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $shiftClosedNo, $category, $productid, $purchaseid, $weightToday, $addedToday, $soldToday, $takeoutsToday, $weight, $estWeight, $weightDelta, $prodclosecomment, $shake, $inStashInt, $inStashExt, $weightWithoutShake, $prodTotal, $prodTotal, $inMenu);
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
						
		    	// Query to add to closingdetails table
				$query = sprintf("INSERT INTO closingdetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $dayClosedNo, $category, $productid, $purchaseid, $dayweightToday, $dayaddedToday, $daysoldToday, $daytakeoutsToday, $weight, $dayestWeight, $dayweightDelta, $prodclosecomment, $shake, $dayinStashInt, $dayinStashExt, $weightWithoutShake, $dayprodTotal, $dayprodTotal, $inMenu);
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
				
			} else {
						
				// OTHER CATEGORIES
							
				/****** SHIFT FIRST ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
						$stashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
						$unStashedInt = $row['SUM(m.quantity)'];
									
			
					$inStashInt = $stashedInt - $unStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
						$stashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime')";
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
						$unStashedExt = $row['SUM(m.quantity)'];
									
			
				$inStashExt = $stashedExt - $unStashedExt;
				
				$prodTotal = $weight + $inStashInt + $inStashExt;
		
				
				/****** THEN DAY ******/
				
				// Calculate what's in internal stash
				$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
						$daystashedInt = $row['SUM(m.quantity)'];
								
				$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
						$dayunStashedInt = $row['SUM(m.quantity)'];
									
			
					$dayinStashInt = $daystashedInt - $dayunStashedInt;
							
				// Calculate what's in external stash
				$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
						$daystashedExt = $row['SUM(m.quantity)'];
								
				$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.purchaseid = $purchaseid AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime')";
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
						$dayunStashedExt = $row['SUM(m.quantity)'];
									
			
				$dayinStashExt = $daystashedExt - $dayunStashedExt;
				
				$dayprodTotal = $weight + $dayinStashInt + $dayinStashExt;
		
		    	// Query to add to closingdetails table - 12 arguments
				$query = sprintf("INSERT INTO shiftclosedetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $shiftClosedNo, $category, $productid, $purchaseid, $weightToday, $addedToday, $soldToday, $takeoutsToday, $weight, $estWeight, $weightDelta, $prodclosecomment, $shake, $inStashInt, $inStashExt, $weightWithoutShake, $prodTotal, $prodTotal, $inMenu);
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
							
							
		    	// Query to add to closingdetails table - 12 arguments
				$query = sprintf("INSERT INTO closingdetails (closingid, category, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, shakePercentage, intStash, extStash, weightNoShake, totalWeight, totalNoShake, inMenu) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%d');",
			  			 $dayClosedNo, $category, $productid, $purchaseid, $dayweightToday, $dayaddedToday, $daysoldToday, $daytakeoutsToday, $weight, $dayestWeight, $dayweightDelta, $prodclosecomment, $shake, $dayinStashInt, $dayinStashExt, $dayweightWithoutShake, $dayprodTotal, $dayprodTotal, $inMenu);
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
		
} // Product loop ends

		
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

	pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "step6 dev-align-center", $lang['closeday-dis-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
	$_SESSION['daycloseProduct'] = $_POST['daycloseProduct'];
	
	echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='?saveDispensary' method='POST'><br />";
	echo "<input type='hidden' name='productConfirm' value='yes'><br />";
	
	$i=0;
	
	$catArray = $_POST['catArray'];
	
	foreach($catArray as $value) {
		echo '<input type="hidden" name="catArray[]" value="'. $value. '">';
	}
	
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
				$productStatus = $lang['closeday-main-closed'];
				$radioDisable = "disabled";
			} else if ($inMenu == 0) {
				$disableOrNot = "";
				$productStatus = $lang['not-in-menu'];
				$radioDisable = "";
			} else {
				$disableOrNot = "";
				$productStatus = $lang['pur-inmenu'];
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
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$openingtime' AND '$closingtime' ORDER by purchaseDate DESC";
		try
		{
			$result = $pdo3->prepare("$selectPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$newPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM shiftopendetails d, shiftopen o WHERE o.openingid = $openingid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
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
				$weightToday = $row['weight'];
			
			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
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
				$addedToday = $row['SUM(quantity)'];
				
		
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
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
				$daysoldToday = $row['SUM(d.quantity)'];
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$dayopeningtime' AND '$closingtime' ORDER by purchaseDate DESC";
		try
		{
			$result = $pdo3->prepare("$selectPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$daynewPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE o.openingid = $dayopeningid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
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
				$dayweightToday = $row['weight'];
			
			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
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
				$dayaddedToday = $row['SUM(quantity)'];
				
		
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
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
					echo "<h5>{$lang['global-flowerscaps']}</h5>";
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
		<div class='actionbox-np2'>
		  <div class='mainboxheader'>%s %s ($purchaseid)</div>
		 %s<br />
		 <div class='boxcontent'>
		 <table class='purchasetable'>
		  <tr>
		   <td>{$lang['closeday-openingweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-dispensed']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-takeouts']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}
				<input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit purchaseNumber' value='%0.02f' readonly />
		    <input type='hidden' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' readonly />
		   </td>
		  </tr>
		 </table>
		 </div>
		 <br />
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
			echo "<h5>{$lang['global-extractscaps']}</h5>";
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
		<div class='actionbox-np2'>
		 <div class='mainboxheader'>%s ($purchaseid)</div>
		 <div class='boxcontent'>
		 <table class='purchasetable'>
		  <tr>
		   <td>{$lang['closeday-openingweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-dispensed']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-takeouts']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}
		  
				<input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit purchaseNumber' value='%0.02f' readonly />
		    <input type='hidden' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' readonly />
		   </td>
		  </tr>
		 </table>
		 </div>
		 <br />
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
	    } else {
		    
			if (${'divider'.$category} != 'set') {
				
				${'divider'.$category} = 'set';
				echo "<h5>{$name}</h5>";
				
			}
			
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
		<div class='actionbox-np2'>
		  <div class='mainboxheader'>%s ($purchaseid)</div>
		  <div class='boxcontent'>
		 <table class='purchasetable'>
		  <tr>
		   <td>{$lang['closeday-openingweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-dispensed']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-takeouts']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}
		  
				<input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit purchaseNumber' value='%0.02f' readonly />
		    <input type='hidden' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' readonly />
		   </td>
		  </tr>
		 </table>
		 </div>
		 <br />
		   {$lang['global-comment']}?<br />
		   <textarea class='defaultinput' name='confirmedClose[%d][prodclosecomment]'></textarea>
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
 		echo "<br><button name='oneClick' class='cta1' type='submit'>{$lang['global-confirm']}</button>";
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

	pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "step6 dev-align-center", $lang['closeday-dis-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
	$_SESSION['daycloseProduct'] = $_POST['daycloseProduct'];
	
	echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='?saveDispensary' method='POST'><br />";
	echo "<input type='hidden' name='productConfirm' value='yes'><br />";
	
		$i=0;
		
		$catArray = $_POST['catArray2'];
		
		foreach($catArray as $value) {
			echo '<input type="hidden" name="catArray[]" value="'. $value. '">';
		}
		
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
			
			$auto = $prodClose['auto'];
			
			// Determine if a product was set to auto -- and if so, calculate the weight!!
			
			if ($auto == 1) {
				
			/****** SHIFT FIRST ******/
			
			// Look up todays sales
			$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
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
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$openingtime' AND '$closingtime' ORDER by purchaseDate DESC";
		try
		{
			$result = $pdo3->prepare("$selectPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$newPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM shiftopendetails d, shiftopen o WHERE o.openingid = $openingid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
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
				$weightToday = $row['weight'];
			
			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
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
				$addedToday = $row['SUM(quantity)'];
				
		
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
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
				$takeoutsToday = $row['SUM(quantity)'];
			
			// Exception if there was no weight this morning, we use the new product weight
			if (($weightToday == 0)) {
				$addedToday = $newPurchaseWeight + $addedToday;
			}
				
			// Calculate estimated weight and weight Delta:
			$weight = $weightToday - $soldToday - $takeoutsToday + $addedToday;
			$fullWeight = $weight + $tupperWeight;
			$weightDelta = $weight - $estWeight;
			$estWeight = $weight;
			
			
			
			/****** THEN DAY ******/
			
			// Look up todays sales
			$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE s.saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
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
				$daysoldToday = $row['SUM(d.quantity)'];
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$dayopeningtime' AND '$closingtime' ORDER by purchaseDate DESC";
		try
		{
			$result = $pdo3->prepare("$selectPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$daynewPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE o.openingid = $dayopeningid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
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
				$dayweightToday = $row['weight'];
			
			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
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
				$dayaddedToday = $row['SUM(quantity)'];
				
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
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
				$daytakeoutsToday = $row['SUM(quantity)'];
					
			// Calculate estimated weight and weight Delta:
			$dayweight = $dayweightToday - $daysoldToday - $daytakeoutsToday + $dayaddedToday;
			$dayweightDelta = $weight - $dayestWeight;
			$dayestWeight = $dayweight;
			
			} else {
				
				$weight = $fullWeight - $tupperWeight;
				
			}			
			
			if ($closed == 'yes') {
				$disableOrNot = "disabled style='color: red'";
				$productStatus = $lang['closeday-main-closed'];
				$radioDisable = "disabled";
			} else if ($inMenu == 0) {
				$disableOrNot = "";
				$productStatus = $lang['not-in-menu'];
				$radioDisable = "";
			} else {
				$disableOrNot = "";
				$productStatus = $lang['pur-inmenu'];
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
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$openingtime' AND '$closingtime' ORDER by purchaseDate DESC";
		try
		{
			$result = $pdo3->prepare("$selectPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$newPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM shiftopendetails d, shiftopen o WHERE o.openingid = $openingid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
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
				$weightToday = $row['weight'];

			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
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
				$addedToday = $row['SUM(quantity)'];
				
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$openingtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
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
				$daysoldToday = $row['SUM(d.quantity)'];
				
			// Exception if purchase was added today, i.e. there is no opening balance
			$selectPurchase = "SELECT realQuantity FROM purchases WHERE purchaseid = $purchaseid AND purchaseDate BETWEEN '$dayopeningtime' AND '$closingtime' ORDER by purchaseDate DESC";
		try
		{
			$result = $pdo3->prepare("$selectPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$daynewPurchaseWeight = $row['realQuantity'];
			
			$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE o.openingid = $dayopeningid AND d.openingid = o.openingid AND purchaseid = $purchaseid";
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
				$dayweightToday = $row['weight'];

			// Query to look up movement totals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 17 AND movementTypeid <> 19";
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
				$dayaddedToday = $row['SUM(quantity)'];
		
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime BETWEEN '$dayopeningtime' AND '$closingtime' AND movementTypeid <> 18 AND movementTypeid <> 20";
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
					echo "<h5>{$lang['global-flowerscaps']}</h5>";
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
		<div class='actionbox-np2'>
		 <div class='mainboxheader'>%s %s ($purchaseid)</div>
		 %s<br />
		 <div class='boxcontent'>
		 <table class='purchasetable'>
		  <tr>
		   <td>{$lang['closeday-openingweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-dispensed']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-takeouts']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit purchaseNumber' value='%0.02f' readonly /></td>
		  </tr>
		 <tr>
		  <td>{$lang['weightnow']}
		  		<input type='number' lang='nb' name='confirmedClose[%d][fullWeight]' id='fullWeight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' /></td>
		 </tr>
		 <tr>
		  <td class='red'>- {$lang['jar-weight']}
		  		<input type='number' lang='nb' name='confirmedClose[%d][tupperWeight]' id='tupperWeight%d' class='fourDigit purchaseNumber red' value='%0.02f' step='0.01' /></td>
		 </tr>
		  <tr>
		   <td>{$lang['add-realweight']}
		   		<input type='number' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' %s readonly /></td>
		  </tr>
		  <tr>
		   <td><strong>{$lang['global-delta']}:</strong>
		   	<strong><input type='number' lang='nb' name='confirmedClose[%d][weightDelta]' id='weightDelta%d' class='fourDigit%s purchaseNumber' value='%0.02f' step='0.01' readonly /></strong></td>
		  </tr>
		 </table>
		 </div>
		 <br />
		 {$lang['global-shake']}:<br />
    	<input type='radio' name='confirmedClose[%d][shake]' value='0' style='margin-left: 5px; width: 12px;' %s %s>0%%</input><br />
    	<input type='radio' name='confirmedClose[%d][shake]' value='25' style='margin-left: 5px; width: 12px;' %s %s>25%%</input><br />
    	<input type='radio' name='confirmedClose[%d][shake]' value='50' style='margin-left: 5px; width: 12px;' %s %s>50%%</input><br />
    <input type='radio' name='confirmedClose[%d][shake]' value='75' style='margin-left: 5px; width: 12px;' %s %s>75%%</input><br /><br />
		  {$lang['global-comment']}?<br />
		 <textarea class='defaultinput' name='confirmedClose[%d][prodclosecomment]'></textarea>
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
			echo "<h5>{$lang['global-extractscaps']}</h5>";
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
		<div class='actionbox-np2'>
		 <div class='mainboxheader'>%s ($purchaseid)</div>
		 <div class='boxcontent'>
		 <table clas='purchasetable'>
		  <tr>
		   <td>{$lang['closeday-openingweight']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-dispensed']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-takeouts']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		 <tr>
		  <td>{$lang['weightnow']}
		  	<input type='number' lang='nb' name='confirmedClose[%d][fullWeight]' id='fullWeight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' /></td>
		 </tr>
		 <tr>
		  <td class='red'>- {$lang['jar-weight']}
		  	<input type='number' lang='nb' name='confirmedClose[%d][tupperWeight]' id='tupperWeight%d' class='fourDigit purchaseNumber red' value='%0.02f' step='0.01' /></td>
		 </tr>
		  <tr>
		   <td>{$lang['add-realweight']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' %s readonly /><br />
		  </tr>
		  <tr>
		   <td><strong>{$lang['global-delta']}:</strong>
		   	<strong><input type='number' lang='nb' name='confirmedClose[%d][weightDelta]' id='weightDelta%d' class='fourDigit%s purchaseNumber' value='%0.02f' step='0.01' readonly /></strong><br />
		  </tr>
		 </table>
		 </div>
		 <br />
		  {$lang['global-comment']}?<br />
		   <textarea  class='defaultinput' name='confirmedClose[%d][prodclosecomment]'></textarea>
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
	  
	    } else {
		    
			if (${'divider'.$category} != 'set') {
				
				${'divider'.$category} = 'set';
				echo "<h5>{$name}</h5>";
				
			}
			
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
		<div class='actionbox-np2'>
		 <div class='mainboxheader'>>%s ($purchaseid)</div>
		 <div class='boxcontent'>
		 <table class='purchasetable'>
		  <tr>
		   <td>{$lang['closeday-openingweight']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][weightToday]' id='weightToday' class='fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>+ {$lang['closeday-added']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][addedToday]' id='addedToday' class='green fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-dispensed']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][soldToday]' id='soldToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td class='red'>- {$lang['closeday-takeouts']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][takeoutsToday]' id='takeoutsToday' class='red fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		  <tr>
		   <td>{$lang['closeday-estweight']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit purchaseNumber' value='%0.02f' readonly /><br />
		  </tr>
		 <tr>
		  <td>{$lang['weightnow']}
		  	<input type='number' lang='nb' name='confirmedClose[%d][fullWeight]' id='fullWeight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' /></td>
		 </tr>
		 <tr>
		  <td class='red'>- {$lang['jar-weight']}
		  	<input type='number' lang='nb' name='confirmedClose[%d][tupperWeight]' id='tupperWeight%d' class='fourDigit purchaseNumber red' value='%0.02f' step='0.01' /></td>
		 </tr>
		  <tr>
		   <td>{$lang['add-realweight']}
		   	<input type='number' lang='nb' name='confirmedClose[%d][weight]' id='weight%d' class='fourDigit purchaseNumber' value='%0.02f' step='0.01' %s readonly /><br />
		  </tr>
		  <tr>
		   <td><strong>{$lang['global-delta']}:</strong>
		   	<strong><input type='number' lang='nb' name='confirmedClose[%d][weightDelta]' id='weightDelta%d' class='fourDigit%s purchaseNumber' value='%0.02f' step='0.01' readonly /></strong><br />
		  </tr>
		 </table>
		 </div>
		 <br />
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
 		echo "<br><button class='cta1' name='oneClick' type='submit'>{$lang['global-confirm']}</button>";
		echo "</form>";
		displayFooter();
		exit();
		
		## FORM INPUT END ##
		
	} else if ($_POST['step3'] != 'complete') {
		handleError($lang['global-fivenotcomplete'],"");
	}
	
} displayFooter();
