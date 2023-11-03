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
	
	// If the page re-submitted, let's save Closing values for Reception! Also save Opening to 2.
	
	if (isset($_GET['saveDispensary'])) {
		
		// Retrieve variables for CLOSING insert
		$_SESSION['confirmedClose'] = $_POST['confirmedClose'];
		
		$catArray = $_POST['catArray'];

		foreach($_SESSION['confirmedClose'] as $confirmedCloseCalc) {

			// Common
			$weight = $confirmedCloseCalc['weight'];
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

				
			${'prodOpening'.$category} = ${'prodOpening'.$category} + $weightToday;
			${'prodAdded'.$category} = ${'prodAdded'.$category} + $addedToday;
			${'prodRemoved'.$category} = ${'prodRemoved'.$category} + $takeoutsToday;
			${'prodEstStock'.$category} = ${'prodEstStock'.$category} + $estWeight;
			${'totWeight'.$category} = ${'totWeight'.$category} + $weight;
			${'totDelta'.$category} = ${'totDelta'.$category} + $weightDelta;
			${'totDispensed'.$category} = ${'totDispensed'.$category} + $soldToday;
			
			${'dayprodOpening'.$category} = ${'dayprodOpening'.$category} + $dayweightToday;
			${'dayprodAdded'.$category} = ${'dayprodAdded'.$category} + $dayaddedToday;
			${'dayprodRemoved'.$category} = ${'dayprodRemoved'.$category} + $daytakeoutsToday;
			${'dayprodEstStock'.$category} = ${'dayprodEstStock'.$category} + $dayestWeight;
			${'dayTotWeight'.$category} = ${'dayTotWeight'.$category} + $weight;
			${'dayTotDelta'.$category} = ${'dayTotDelta'.$category} + $dayweightDelta;
			${'dayTotDispensed'.$category} = ${'dayTotDispensed'.$category} + $daysoldToday;
			
		}
		
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

		$realClosingtime = date('Y-m-d H:i:s');
		$realClosingtime2 = date('Y-m-d H:i:s', time() + 5);
		
		if ($dayClosedNo > 0) {

			// Means part of the day has been closed already, so use UPDATE
			
			$closingid = $dayClosedNo;
			
			// Close shift first
		  	$query = sprintf("UPDATE shiftclose SET closingtime = '%s' WHERE closingid = '%d';",
		  	$realClosingtime, $shiftClosedNo);
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
				
			// Closing exists. Let's delete the closing values from closingother
			$deleteCloseOther = "DELETE from shiftcloseother WHERE categoryType = 1 AND closingid = '$shiftClosedNo'";
		try
		{
			$result = $pdo3->prepare("$deleteCloseOther")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
					
					
			$updateOpening = sprintf("UPDATE shiftopen SET dis2Closed = 2, dis2ClosedAt = '%s' WHERE openingid = '%d';",
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
				
				
			// Then close day
		  	$query = sprintf("UPDATE closing SET closingtime = '%s' WHERE closingid = '%d';",
		  	$realClosingtime2, $dayClosedNo);
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
				
			// Closing exists. Let's delete the closing values from closingother
			$deleteCloseOther = "DELETE from closingother WHERE categoryType = 1 AND closingid = '$dayClosedNo'";
		try
		{
			$result = $pdo3->prepare("$deleteCloseOther")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			

			$updateOpening = sprintf("UPDATE opening SET dis2Closed = 2, dis2ClosedAt = '%s' WHERE openingid = '%d';",
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
						
		} else {
			
			// Close shift first
			$query = sprintf("INSERT INTO shiftclose (closingtime) VALUES ('%s');",
			$realClosingtime);
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
			
			$updateOpening = sprintf("UPDATE shiftopen SET dis2Closed = 2, dis2ClosedAt = '%s', shiftClosedNo = '%d' WHERE openingid = '%d';",
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
			
			// Now close Day
			$query = sprintf("INSERT INTO closing (closingtime) VALUES ('%s');",
			$realClosingtime2);
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
		
			$updateOpening = sprintf("UPDATE opening SET dis2Closed = 2, dis2ClosedAt = '%s', dayClosedNo = '%d' WHERE openingid = '%d';",
				$realClosingtime2,
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
				
		}
		
		// Now shiftcloseother and closeother
		
		// Shift first
		foreach($catArray as $cat) {
				
			$catID = $cat;
			
			// Look up today's sales by category
			$selectSales = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = $cat";
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
				$salesToday = $row['SUM(d.amount)'];
					
			// Calculate what's in internal stash
			$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime') AND p.category = $cat";
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
					
							
			$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime') AND p.category = $cat";
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
			$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime') AND p.category = $cat";
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
							
			$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$openingtime' AND '$closingtime') AND p.category = $cat";
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
			
			$catTotal = ${'totWeight'.$cat} + $inStashInt + $inStashExt;
			
			// Here do DB insert for each cat, echo first to see if it works properly
			$query = sprintf("INSERT INTO shiftcloseother (closingid, category, categoryType, stockDelta, soldtoday, unitsSold, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, intStash, extStash) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
			  $shiftClosedNo, $catID, 1, ${'totDelta'.$cat}, $salesToday, ${'totDispensed'.$cat}, ${'prodOpening'.$cat}, ${'prodAdded'.$cat}, ${'prodRemoved'.$cat}, ${'prodEstStock'.$cat}, ${'totWeight'.$cat}, $inStashInt, $inStashExt);
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
		
		// Then day
		foreach($catArray as $cat) {
				
			$catID = $cat;
			
			// Look up today's sales by category
			$selectSales = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND saletime BETWEEN '$dayopeningtime' AND '$closingtime' AND d.category = $cat";
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
				$salesToday = $row['SUM(d.amount)'];
					
			// Calculate what's in internal stash
			$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime') AND p.category = $cat";
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
					
							
			$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime') AND p.category = $cat";
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
			$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime') AND p.category = $cat";
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
							
			$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR p.closedAt BETWEEN '$dayopeningtime' AND '$closingtime') AND p.category = $cat";
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
			
			$daycatTotal = ${'daytotWeight'.$cat} + $inStashInt + $inStashExt;
			
			// Here do DB insert for each cat, echo first to see if it works properly
			$query = sprintf("INSERT INTO closingother (closingid, category, categoryType, stockDelta, soldtoday, unitsSold, prodOpening, prodAdded, prodRemoved, prodEstStock, prodStock, intStash, extStash) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f');",
			  $dayClosedNo, $catID, 1, ${'daytotDelta'.$cat}, $salesToday, ${'daytotDispensed'.$cat}, ${'dayprodOpening'.$cat}, ${'dayprodAdded'.$cat}, ${'dayprodRemoved'.$cat}, ${'dayprodEstStock'.$cat}, ${'totWeight'.$cat}, $inStashInt, $inStashExt);
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
		
		// Now individual products.
		foreach($_SESSION['confirmedClose'] as $confirmedClose) {
			
			// Common
			$category = $confirmedClose['category'];
			$productid = $confirmedClose['productid'];
			$purchaseid = $confirmedClose['purchaseid'];
			$prodclosecomment = $confirmedClose['prodclosecomment'];
			$productStatus = $confirmedClose['productStatus'];
			$inMenu = $confirmedClose['inMenu'];
			$weight = $confirmedClose['weight'];

			// Shift
			$weightToday = $confirmedClose['weightToday'];
			$addedToday = $confirmedClose['addedToday'];
			$soldToday = $confirmedClose['soldToday'];
			$takeoutsToday = $confirmedClose['takeoutsToday'];
			$estWeight = $confirmedClose['estWeight'];
			
			$weightDelta = 0;
			$weightDelta = $weight - $estWeight;

			// Day
			$dayweightToday = $confirmedClose['dayweightToday'];
			$dayaddedToday = $confirmedClose['dayaddedToday'];
			$daysoldToday = $confirmedClose['daysoldToday'];
			$daytakeoutsToday = $confirmedClose['daytakeoutsToday'];
			$dayestWeight = $confirmedClose['dayestWeight'];
			
			$dayweightDelta = 0;
			$dayweightDelta = $weight - $dayestWeight;

			
			// Shift first
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
				
			// Then day
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
					$stashedInt = $row['SUM(m.quantity)'];
							
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
					$unStashedInt = $row['SUM(m.quantity)'];
								
		
				$dayinStashInt = $stashedInt - $unStashedInt;
						
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
					$stashedExt = $row['SUM(m.quantity)'];
							
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
					$unStashedExt = $row['SUM(m.quantity)'];
								
		
				$dayinStashExt = $stashedExt - $unStashedExt;
							  
	    	// Query to add to shiftclosedetails table - 12 arguments
			$query = sprintf("INSERT INTO shiftclosedetails (closingid, category, categoryType, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, intStash, extStash, totalWeight, inMenu) VALUES ('%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%d');",
		  			 $shiftClosedNo, $category, 1, $productid, $purchaseid, $weightToday, $addedToday, $soldToday, $takeoutsToday, $weight, $estWeight, $weightDelta, $prodclosecomment, $inStashInt, $inStashExt, $weight + $inStashInt + $inStashExt, $inMenu);
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
			$query = sprintf("INSERT INTO closingdetails (closingid, category, categoryType, productid, purchaseid, weightToday, addedToday, soldToday, takeoutsToday, weight, weightEst, weightDelta, specificComment, intStash, extStash, totalWeight, inMenu) VALUES ('%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%d');",
		  			 $dayClosedNo, $category, 1, $productid, $purchaseid, $dayweightToday, $dayaddedToday, $daysoldToday, $daytakeoutsToday, $weight, $dayestWeight, $dayweightDelta, $prodclosecomment, $dayinStashInt, $dayinStashExt, $weight + $dayinStashInt + $dayinStashExt, $inMenu);
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

	pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "step6", $lang['closeday-dis-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
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
			$weight = $prodClose['weight'];
			$closed = $prodClose['closed'];
			$inMenu = $prodClose['inMenu'];
			$catName = $prodClose['categoryName'];
			
			if (${'header'.$category} != 'set') {
				echo "<br /><h3 class='title'>$catName</h3>";
				${'header'.$category} = 'set';
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
			// What if there was no opening balance, and product was ADDED today, but not as a new purchase?
			// Exception if purchase was added today, i.e. there is no opening balance
			if (($weightToday == 0)) {
				
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
			// What if there was no opening balance, and product was ADDED today, but not as a new purchase?
			// Exception if purchase was added today, i.e. there is no opening balance
			if (($weightToday == 0)) {
				
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
					$daynewPurchaseWeight = $row['realQuantity'];
					$dayaddedToday = $newPurchaseWeight + $addedToday;
					
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
			
			$i++;
			
			$product_row = sprintf("
	
<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#fullWeight%d').val();
          var b = $('#estWeight%d').val();
          var total = a - b;
          var roundedtotal = total.toFixed(2);
          $('#weightDelta%d').val(roundedtotal);
          
          var wdelta%d = $('#weightDelta%d').val();
          
          if (wdelta%d < '0.00') {
          	$('#weightDelta%d').css('color', 'red');
      	  }
      	  if (wdelta%d > '0.00') {
          	$('#weightDelta%d').css('color', 'green');
      	  }
    }

        $('#fullWeight%d').bind('keypress keyup blur', compute);
        

  }); // end ready
</script>
		<div class='productbox'>
		 <h3>%s ($purchaseid)</h3>
		 <table>
		  <tr>
		   <td>{$lang['opening-count']}:</td>
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
		   <td>{$lang['est-count']}:</td>
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
  	   <input type='hidden' name='confirmedClose[%d][dayweightDelta]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayweightToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayaddedToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daysoldToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][daytakeoutsToday]' value='%f' />
  	   <input type='hidden' name='confirmedClose[%d][dayestWeight]' value='%f' />",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $prodClose['name'], $i, $weightToday, $i, $addedToday, $i, $soldToday, $i, $takeoutsToday, $i, $i, $estWeight, $i, $i, $estWeight, $i, $i, $name, $i, $category, $i, $productid, $i, $purchaseid, $i, $productStatus, $i, $inMenu, $i, $dayweightDelta, $i, $dayweightToday, $i, $dayaddedToday, $i, $daysoldToday, $i, $daytakeoutsToday, $i, $dayestWeight
	  );
	  
	  		echo $product_row;
	  
		// End loop for each product
		}
		
		echo "<input type='hidden' name='step3' value='complete' />";
 		echo "<button name='oneClick' type='submit'>{$lang['global-confirm']}</button>";
		echo "</form>";
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
			
			$catArray = $_POST['catArray2'];
			
			foreach($catArray as $value) {
				echo '<input type="hidden" name="catArray[]" value="'. $value. '">';
			}
			
		foreach($_POST['daycloseProduct'] as $prodClose) {
			
			$name = $prodClose['name'];
			$category = $prodClose['category'];
			$productid = $prodClose['productid'];
			$purchaseid = $prodClose['purchaseid'];
			$closed = $prodClose['closed'];
			$inMenu = $prodClose['inMenu'];
			$catName = $prodClose['categoryName'];
		
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
			// What if there was no opening balance, and product was ADDED today, but not as a new purchase?
			// Exception if purchase was added today, i.e. there is no opening balance
			if (($weightToday == 0)) {
				
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
					$addedToday = $newPurchaseWeight + $addedToday;
					
			}
				
			// Calculate estimated weight and weight Delta:
			$estWeight = $weightToday - $soldToday - $takeoutsToday + $addedToday;
			$weight = $weightToday - $soldToday - $takeoutsToday + $addedToday;
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
			// What if there was no opening balance, and product was ADDED today, but not as a new purchase?
			// Exception if purchase was added today, i.e. there is no opening balance
			if (($weightToday == 0)) {
				
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
					$daynewPurchaseWeight = $row['realQuantity'];
					$dayaddedToday = $newPurchaseWeight + $addedToday;
					
			}
			
			// Calculate estimated weight and weight Delta:
			$dayestWeight = $dayweightToday - $daysoldToday - $daytakeoutsToday + $dayaddedToday;
			$dayweight = $dayweightToday - $daysoldToday - $daytakeoutsToday + $dayaddedToday;
			$dayweightDelta = $weight - $dayestWeight;
					
			
			} else {
				
				$weight = $prodClose['weight'];
				
			}		
			
			if (${'header'.$category} != 'set') {
				echo "<br /><h3 class='title'>$catName</h3>";
				${'header'.$category} = 'set';
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
						
				$product_row = sprintf("

<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#fullWeight%d').val();
          var b = $('#estWeight%d').val();
          var total = a - b;
          var roundedtotal = total.toFixed(2);
          $('#weightDelta%d').val(roundedtotal);
          
          var wdelta%d = $('#weightDelta%d').val();
          
          if (wdelta%d < '0.00') {
          	$('#weightDelta%d').css('color', 'red');
      	  }
      	  if (wdelta%d > '0.00') {
          	$('#weightDelta%d').css('color', 'green');
      	  }
    }

        $('#fullWeight%d').bind('keypress keyup blur', compute);
        

  }); // end ready
</script>
		<div class='productbox'>
		 <h3>%s ($purchaseid)</h3>
		 <table>
		  <tr>
		   <td>{$lang['opening-count']}:</td>
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
		   <td>{$lang['est-count']}:</td>
		   <td><input type='number' lang='nb' name='confirmedClose[%d][estWeight]' id='estWeight%d' class='fourDigit' value='%0.02f' readonly /></td>
		  </tr>
		 <tr>
		  <td colspan='2'>&nbsp;</td>
		 </tr>
		 <tr>
		  <td>{$lang['countnow']}:</td>
		  <td><input type='number' lang='nb' name='confirmedClose[%d][weight]' id='fullWeight%d' class='fourDigit' value='%0.02f' %s step='0.01' /></td>
		 </tr>
		  <tr>
		   <td><strong>{$lang['global-delta']}:</strong></td>
		   <td><strong><input type='number' lang='nb' name='confirmedClose[%d][weightDelta]' id='weightDelta%d' class='fourDigit%s' value='%0.02f' step='0.01' readonly /></strong></td>
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
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $prodClose['name'], $i, $weightToday, $i, $addedToday, $i, $soldToday, $i, $takeoutsToday, $i, $i, $estWeight, $i, $i, $weight, $disableOrNot, $i, $i, $deltaColour, $weightDelta, $i, $i, $name, $i, $category, $i, $productid, $i, $purchaseid, $i, $productStatus, $i, $inMenu, $i, $dayweightDelta, $i, $dayweightToday, $i, $dayaddedToday, $i, $daysoldToday, $i, $daytakeoutsToday, $i, $dayestWeight
	  );
	  
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
	
 displayFooter();