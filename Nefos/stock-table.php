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
	
if ($_SESSION['realWeight'] == 1) {
	
	// Query to look up today's opening details + till + bank balance
	if ($_SESSION['openAndClose'] > 2) {
		
		$openingLookup = "SELECT openingid, openingtime, tillBalance, bankBalance FROM opening ORDER BY openingtime DESC LIMIT 1";
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$tillBalance = $row['tillBalance'];	
			$bankBalance = $row['bankBalance'];
			$openingid = $row['openingid'];
			$openingtime = $row['openingtime'];
		
	} else if ($_SESSION['openAndClose'] == 2) {
		
		$openingLookup = "SELECT closingid, closingtime, cashintill, bankBalance FROM closing WHERE currentClosing = 0 ORDER BY closingtime DESC LIMIT 1";	
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$tillBalance = $row['cashintill'];	
			$bankBalance = $row['bankBalance'];
			$openingid = $row['closingid'];
			$openingtime = $row['closingtime'];
			
	}
	
	// Determine if club has not done opening/closing in several days, and if so use variable later down to decide how to calculate stock, dispenses etc.		
	if ($_SESSION['openAndClose'] > 1) {
				
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
		
		
	}
	
	// Query to look up categories
	$selectCats = "SELECT id, name from categories ORDER by id ASC";

	$resultCats = mysql_query($selectCats)
		or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());

		$i = 0;
		
	while ($category = mysql_fetch_array($resultCats)) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		
		// Create more product queries for each category - to be used further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.productid AS productid, pr.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
		
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = $categoryid";
	
		$resultOthers = mysql_query($selectSalesOthers)
			or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($resultOthers);
			$salesTodayOthers = $row['SUM(d.amount)'];
			$quantitySoldOthers = $row['SUM(d.realQuantity)'];
			
		$othersSalesPercentageToday = ($salesTodayOthers / $salesToday) * 100;
		$othersGramsPercentageToday = ($quantitySoldOthers / $unitsSold) * 100;
		
	}
	
	
	$selectProducts = "SELECT '1' AS category, g.flowerid AS productid, g.name AS name, p.purchaseid AS purchaseid, p.growType AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW())) UNION ALL SELECT '2' AS category, h.extractid AS productid, h.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
	
	$selectProducts .= $customProducts;
			
	$resultProducts = mysql_query($selectProducts)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
		

		$x = 0;
		while ($product = mysql_fetch_array($resultProducts)) {
			
			$category = $product['category'];
			$productid = $product['productid'];
			$name = $product['name'];
			$purchaseid = $product['purchaseid'];
			$growtype = $product['growtype'];
		
			
			if ($category == 1) {
				// Look up growtype
				$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = '$growtype'";
				
				
				$growResult = mysql_query($growDetails)
					or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
					
				if(mysql_num_rows($growResult) == 0) {
					$growtype = '';
				} else {
				
				$rowGrow = mysql_fetch_array($growResult);
					$growtype = "(" . $rowGrow['growtype'] . ")";
					
				}
			}
			// Determine how to calculate weight and sales:
			if ($_SESSION['openAndClose'] == 0 || $noActiveOpening == 'true') {
				
				// Calculate Stock
				
				// Original purchase
   				$purchaseLookup = "SELECT realQuantity from purchases where purchaseid = $purchaseid";
   				
				$result = mysql_query($purchaseLookup)
					or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
					
				
				$row = mysql_fetch_array($result);
					$openingWeight = $row['realQuantity'];

				
				// Sales
				$selectSales = "SELECT SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid";
			
				$sale = mysql_query($selectSales)
					or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
				
				$row = mysql_fetch_array($sale);
					$sales = $row['SUM(realQuantity)'];
		
					
				// Additions and Removals (not permanent, just wrong variable name)
				$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
				$permAdditions = mysql_query($selectPermAdditions)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
					
					$row = mysql_fetch_array($permAdditions);
						$permAdditions = $row['SUM(quantity)'];
				
				$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
				
				$permRemovals = mysql_query($selectPermRemovals)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
					
					$row = mysql_fetch_array($permRemovals);
						$permRemovals = $row['SUM(quantity)'];
						
				// Calculate what's in Internal stash
				$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$stashedInt = $row['SUM(quantity)'];
					
				$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$unStashedInt = $row['SUM(quantity)'];
			
						
					$inStashInt = $stashedInt - $unStashedInt;
					$inStashInt = $inStashInt;
			
			
				// Calculate what's in External stash
				$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedExt);
						$stashedExt = $row['SUM(quantity)'];
					
				$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedExt);
						$unStashedExt = $row['SUM(quantity)'];
		
					
				$inStashExt = $stashedExt - $unStashedExt;
				$inStashExt = $inStashExt;
				
				
				$inStash = $inStashInt + $inStashExt;
				$jarWeight = $product['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
				
				$weightTotal = $jarWeight + $inStashInt + $inStashExt;
				
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
	
				$result = mysql_query($selectSales)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
				$row = mysql_fetch_array($result);
					$soldToday = $row['SUM(d.realQuantity)'];


			} else {

				// Query to look up today's opening weight
				if ($_SESSION['openAndClose'] > 2) {
					
					$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE d.openingid = $openingid AND d.openingid = o.openingid AND category = $category AND purchaseid = $purchaseid";
					
				} else if ($_SESSION['openAndClose'] == 2) {
			
					$openingLookup = "SELECT d.weight FROM closingdetails d, closing o WHERE d.closingid = $openingid AND d.closingid = o.closingid AND category = $category AND purchaseid = $purchaseid";
					
				}
				
				$result = mysql_query($openingLookup)
					or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
			
			
				// If opening weight doesn't exist, find purchaseweight!
				if(mysql_num_rows($result) == 0) {
	   				$purchaseLookup = "SELECT realQuantity from purchases where purchaseid = $purchaseid";
	   				
					$result = mysql_query($purchaseLookup)
						or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
					
				}
				
				$row = mysql_fetch_array($result);
					$openingWeight = $row['0'];
					
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE s.saletime > '$openingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
	
				$result = mysql_query($selectSales)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
				$row = mysql_fetch_array($result);
					$sales = $row['SUM(d.realQuantity)'];
	
				// Look up additions and removals
				$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime > '$openingtime'";
				$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime > '$openingtime'";
			
				$additions = mysql_query($selectAdditions)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
				$row = mysql_fetch_array($additions);
					$permAdditions = $row['SUM(quantity)'];
					
				$removals = mysql_query($selectRemovals)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
				$row = mysql_fetch_array($removals);
					$permRemovals = $row['SUM(quantity)'];
					
					
				// Calculate jar weight:
				$jarWeight = $openingWeight + $permAdditions - $permRemovals - $sales;	
				
				
				// Calculate what's in Internal stash
				$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$stashedInt = $row['SUM(quantity)'];
					
				$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$unStashedInt = $row['SUM(quantity)'];
			
						
				$inStashInt = $stashedInt - $unStashedInt;
			
			
				// Calculate what's in External stash
				$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedExt);
						$stashedExt = $row['SUM(quantity)'];
					
				$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedExt);
						$unStashedExt = $row['SUM(quantity)'];
			
						
				$inStashExt = $stashedExt - $unStashedExt;
				
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
				$flowerTotPermRem = $flowerTotPermRem + $permRemovals;
				$flowerTotJar = $flowerTotJar + $jarWeight;
				$flowerTotIntSt = $flowerTotIntSt + $inStashInt;
				$flowerTotExtSt = $flowerTotExtSt + $inStashExt;
			} else if ($category == 2) {
				$extractTotOpening = $extractTotOpening + $openingWeight;
				$extractTotPermAdd = $extractTotPermAdd + $permAdditions;
				$extractTotSales = $extractTotSales + $sales;
				$extractTotPermRem = $extractTotPermRem + $permRemovals;
				$extractTotJar = $extractTotJar + $jarWeight;
				$extractTotIntSt = $extractTotIntSt + $inStashInt;
				$extractTotExtSt = $extractTotExtSt + $inStashExt;
				
				// Add Extract header
				if ($extractHeader != 'set') {
					$productDetails .= <<<EOD
	   <tr>
	    <td colspan="8" class="tableHeader">
	    <h3 class='title'>{$lang['global-extractscaps']}</h3>
	    </td>
	   </tr>
	   <tr class="thstyle">
	    <td class='left'>{$lang['global-name']}</td>
	    <td>{$lang['closeday-openingweight']}</td>
	    <td>{$lang['closeday-added']}</td>
	    <td>{$lang['global-dispenses']}</td>
	    <td>{$lang['closeproduct-takeouts']}</td>
	    <td>{$lang['weight']}</td>
	    <td>{$lang['intstash']}</td>
	    <td>{$lang['extstash']}</td>
	   </tr>
EOD;
				$extractHeader = 'set';
				}
				
			} else {
				
				// Query to look up categories
				$selectCats = "SELECT id, name from categories WHERE id = $category";
			
				$resultCats = mysql_query($selectCats)
					or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
					
				$row = mysql_fetch_array($resultCats);
		  	    	$catName = $row['name'];
		  	    	$catID = $row['id'];
		  	    	
				if (${'otherHeader' . $catID} != 'set') {
					$productDetails .= <<<EOD
	   <tr>
	    <td colspan="8" class="tableHeader">
	    <h3 class='title'>$catName</h3>
	    </td>
	   </tr>
	   <tr class="thstyle">
	    <td class='left'>{$lang['global-name']}</td>
	    <td>{$lang['closeday-openingweight']}</td>
	    <td>{$lang['closeday-added']}</td>
	    <td>{$lang['global-dispenses']}</td>
	    <td>{$lang['closeproduct-takeouts']}</td>
	    <td>{$lang['weight']}</td>
	    <td>{$lang['intstash']}</td>
	    <td>{$lang['extstash']}</td>
	   </tr>
EOD;
				${'otherHeader' . $catID} = 'set';
				}
					
					
					$otherTotals[$catID]['catName'] = $catName;
					$otherTotals[$catID]['otherTotJar'] = $otherTotals[$catID]['otherTotJar'] + $jarWeight;
					$otherTotals[$catID]['otherTotIntSt'] = $otherTotals[$catID]['otherTotIntSt'] + $inStashInt;
					$otherTotals[$catID]['otherTotExtSt'] = $otherTotals[$catID]['otherTotExtSt'] + $inStashExt;
					$otherTotals[$catID]['otherTot'] = $otherTotals[$catID]['otherTotJar'] + $otherTotals[$catID]['otherTotIntSt'] + $otherTotals[$catID]['otherTotExtSt'];
					$otherTotals[$catID]['otherSoldToday'] = $otherTotals[$catID]['otherSoldToday'] + $soldToday;

				}
			


				$productDetails .= <<<EOD
				
	   <tr>
	    <td class='left'>$name <span class='smallerfont2'>$growtype</span></td>
	    <td class='right'>$openingWeight</td>
	    <td class='right'>$permAdditions</td>
	    <td class='right'>$sales</td>
	    <td class='right'>$permRemovals</td>
	    <td class='right'><strong>$jarWeight</strong></td>
	    <td class='right'>$inStashInt</td>
	    <td class='right'>$inStashExt</td>
	   </tr>
				
EOD;

		} // End product loop
		
		
		
	pageStart($lang['title-stock'], NULL, NULL, "pstock", "table admin", $lang['global-stockcaps'] . "<br /><a href='stock.php' class='headerlink'>(" . $lang['changeview'] . ")</a>", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
echo <<<EOD
	  <table class="default nonhover">
	   <tr>
	    <td colspan="8" class="tableHeader">
	    <h3 class='title'>{$lang['summarycaps']}</h3>
	    </td>
	   </tr>
	   <tr class="thstyle">
	    <td class='left'>{$lang['global-name']}</td>
	    <td>{$lang['closeday-openingweight']}</td>
	    <td>{$lang['closeday-added']}</td>
	    <td>{$lang['global-dispenses']}</td>
	    <td>{$lang['closeproduct-takeouts']}</td>
	    <td>{$lang['weight']}</td>
	    <td>{$lang['intstash']}</td>
	    <td>{$lang['extstash']}</td>
	   </tr>
	   <tr>
	    <td class='left'>{$lang['global-flowers']}</td>
	    <td class='right'>$flowerTotOpening g</td>
	    <td class='right'>$flowerTotPermAdd g</td>
	    <td class='right'>$flowerTotSales g</td>
	    <td class='right'>$flowerTotPermRem g</td>
	    <td class='right'><strong>$flowerTotJar g</strong></td>
	    <td class='right'>$flowerTotIntSt g</td>
	    <td class='right'>$flowerTotExtSt g</td>
	   </tr>
	   <tr>
	    <td class='left'>{$lang['global-extracts']}</td>
	    <td class='right'>$extractTotOpening g</td>
	    <td class='right'>$extractTotPermAdd g</td>
	    <td class='right'>$extractTotSales g</td>
	    <td class='right'>$extractTotPermRem g</td>
	    <td class='right'><strong>$extractTotJar g</strong></td>
	    <td class='right'>$extractTotIntSt g</td>
	    <td class='right'>$extractTotExtSt g</td>
	   </tr>
	   <tr>
	    <td colspan="8" class="tableHeader">
	    <h3 class='title'>{$lang['global-flowerscaps']}</h3>
	    </td>
	   </tr>
	   <tr class="thstyle">
	    <td class='left'>{$lang['global-name']}</td>
	    <td>{$lang['closeday-openingweight']}</td>
	    <td>{$lang['closeday-added']}</td>
	    <td>{$lang['global-dispenses']}</td>
	    <td>{$lang['closeproduct-takeouts']}</td>
	    <td>{$lang['weight']}</td>
	    <td>{$lang['intstash']}</td>
	    <td>{$lang['extstash']}</td>
	   </tr>
EOD;
	   
		echo $productDetails;
   		echo "</table>";

   		
   		
   		
   		
   		
   		
   		
   		
   		
   		
} else {
	
	
	
	
	
	
	
	
	
	
	// Query to look up today's opening details + till + bank balance
	if ($_SESSION['openAndClose'] > 2) {
		
		$openingLookup = "SELECT openingid, openingtime, tillBalance, bankBalance FROM opening ORDER BY openingtime DESC LIMIT 1";
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$tillBalance = $row['tillBalance'];	
			$bankBalance = $row['bankBalance'];
			$openingid = $row['openingid'];
			$openingtime = $row['openingtime'];
		
	} else if ($_SESSION['openAndClose'] == 2) {
		
		$openingLookup = "SELECT closingid, closingtime, cashintill, bankBalance FROM closing WHERE currentClosing = 0 ORDER BY closingtime DESC LIMIT 1";	
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$tillBalance = $row['cashintill'];	
			$bankBalance = $row['bankBalance'];
			$openingid = $row['closingid'];
			$openingtime = $row['closingtime'];
			
	}
	
	// Determine if club has not done opening/closing in several days, and if so use variable later down to decide how to calculate stock, dispenses etc.		
	if ($_SESSION['openAndClose'] > 1) {
				
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
		
		
	}
	
	// Query to look up categories
	$selectCats = "SELECT id, name from categories ORDER by id ASC";

	$resultCats = mysql_query($selectCats)
		or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());

		$i = 0;
		
	while ($category = mysql_fetch_array($resultCats)) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		
		// Create more product queries for each category - to be used further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.productid AS productid, pr.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
		
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = $categoryid";
	
		$resultOthers = mysql_query($selectSalesOthers)
			or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($resultOthers);
			$salesTodayOthers = $row['SUM(d.amount)'];
			$quantitySoldOthers = $row['SUM(d.quantity)'];
			
		$othersSalesPercentageToday = ($salesTodayOthers / $salesToday) * 100;
		$othersGramsPercentageToday = ($quantitySoldOthers / $unitsSold) * 100;
		
	}
	
	
	$selectProducts = "SELECT '1' AS category, g.flowerid AS productid, g.name AS name, p.purchaseid AS purchaseid, p.growType AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW())) UNION ALL SELECT '2' AS category, h.extractid AS productid, h.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
	
	$selectProducts .= $customProducts;
			
	$resultProducts = mysql_query($selectProducts)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
		

		$x = 0;
		while ($product = mysql_fetch_array($resultProducts)) {
			
			$category = $product['category'];
			$productid = $product['productid'];
			$name = $product['name'];
			$purchaseid = $product['purchaseid'];
			$growtype = $product['growtype'];
		
			
			if ($category == 1) {
				// Look up growtype
				$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = '$growtype'";
				
				
				$growResult = mysql_query($growDetails)
					or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
					
				if(mysql_num_rows($growResult) == 0) {
					$growtype = '';
				} else {
				
				$rowGrow = mysql_fetch_array($growResult);
					$growtype = "(" . $rowGrow['growtype'] . ")";
					
				}
			}
			// Determine how to calculate weight and sales:
			if ($_SESSION['openAndClose'] == 0 || $noActiveOpening == 'true') {
				
				// Calculate Stock
				
				// Original purchase
   				$purchaseLookup = "SELECT realQuantity from purchases where purchaseid = $purchaseid";
   				
				$result = mysql_query($purchaseLookup)
					or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
					
				
				$row = mysql_fetch_array($result);
					$openingWeight = $row['realQuantity'];

				
				// Sales
				$selectSales = "SELECT SUM(quantity) FROM salesdetails WHERE purchaseid = $purchaseid";
			
				$sale = mysql_query($selectSales)
					or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
				
				$row = mysql_fetch_array($sale);
					$sales = $row['SUM(quantity)'];
		
					
				// Additions and Removals (not permanent, just wrong variable name)
				$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
				$permAdditions = mysql_query($selectPermAdditions)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
					
					$row = mysql_fetch_array($permAdditions);
						$permAdditions = $row['SUM(quantity)'];
				
				$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
				
				$permRemovals = mysql_query($selectPermRemovals)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
					
					$row = mysql_fetch_array($permRemovals);
						$permRemovals = $row['SUM(quantity)'];
						
				// Calculate what's in Internal stash
				$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$stashedInt = $row['SUM(quantity)'];
					
				$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$unStashedInt = $row['SUM(quantity)'];
			
						
					$inStashInt = $stashedInt - $unStashedInt;
					$inStashInt = $inStashInt;
			
			
				// Calculate what's in External stash
				$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedExt);
						$stashedExt = $row['SUM(quantity)'];
					
				$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedExt);
						$unStashedExt = $row['SUM(quantity)'];
		
					
				$inStashExt = $stashedExt - $unStashedExt;
				$inStashExt = $inStashExt;
				
				
				$inStash = $inStashInt + $inStashExt;
				$jarWeight = $product['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
				
				$weightTotal = $jarWeight + $inStashInt + $inStashExt;
				
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
	
				$result = mysql_query($selectSales)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
				$row = mysql_fetch_array($result);
					$soldToday = $row['SUM(d.quantity)'];


			} else {

				// Query to look up today's opening weight
				if ($_SESSION['openAndClose'] > 2) {
					
					$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE d.openingid = $openingid AND d.openingid = o.openingid AND category = $category AND purchaseid = $purchaseid";
					
				} else if ($_SESSION['openAndClose'] == 2) {
			
					$openingLookup = "SELECT d.weight FROM closingdetails d, closing o WHERE d.closingid = $openingid AND d.closingid = o.closingid AND category = $category AND purchaseid = $purchaseid";
					
				}
				
				$result = mysql_query($openingLookup)
					or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
			
			
				// If opening weight doesn't exist, find purchaseweight!
				if(mysql_num_rows($result) == 0) {
	   				$purchaseLookup = "SELECT realQuantity from purchases where purchaseid = $purchaseid";
	   				
					$result = mysql_query($purchaseLookup)
						or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
					
				}
				
				$row = mysql_fetch_array($result);
					$openingWeight = $row['0'];
					
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE s.saletime > '$openingtime' AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
	
				$result = mysql_query($selectSales)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
				$row = mysql_fetch_array($result);
					$sales = $row['SUM(d.quantity)'];
	
				// Look up additions and removals
				$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime > '$openingtime'";
				$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime > '$openingtime'";
			
				$additions = mysql_query($selectAdditions)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
				$row = mysql_fetch_array($additions);
					$permAdditions = $row['SUM(quantity)'];
					
				$removals = mysql_query($selectRemovals)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
				$row = mysql_fetch_array($removals);
					$permRemovals = $row['SUM(quantity)'];
					
					
				// Calculate jar weight:
				$jarWeight = $openingWeight + $permAdditions - $permRemovals - $sales;	
				
				
				// Calculate what's in Internal stash
				$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$stashedInt = $row['SUM(quantity)'];
					
				$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$unStashedInt = $row['SUM(quantity)'];
			
						
				$inStashInt = $stashedInt - $unStashedInt;
			
			
				// Calculate what's in External stash
				$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedExt);
						$stashedExt = $row['SUM(quantity)'];
					
				$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedExt);
						$unStashedExt = $row['SUM(quantity)'];
			
						
				$inStashExt = $stashedExt - $unStashedExt;
				
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
				$flowerTotPermRem = $flowerTotPermRem + $permRemovals;
				$flowerTotJar = $flowerTotJar + $jarWeight;
				$flowerTotIntSt = $flowerTotIntSt + $inStashInt;
				$flowerTotExtSt = $flowerTotExtSt + $inStashExt;
			} else if ($category == 2) {
				$extractTotOpening = $extractTotOpening + $openingWeight;
				$extractTotPermAdd = $extractTotPermAdd + $permAdditions;
				$extractTotSales = $extractTotSales + $sales;
				$extractTotPermRem = $extractTotPermRem + $permRemovals;
				$extractTotJar = $extractTotJar + $jarWeight;
				$extractTotIntSt = $extractTotIntSt + $inStashInt;
				$extractTotExtSt = $extractTotExtSt + $inStashExt;
				
				// Add Extract header
				if ($extractHeader != 'set') {
					$productDetails .= <<<EOD
	   <tr>
	    <td colspan="8" class="tableHeader">
	    <h3 class='title'>{$lang['global-extractscaps']}</h3>
	    </td>
	   </tr>
	   <tr class="thstyle">
	    <td class='left'>{$lang['global-name']}</td>
	    <td>{$lang['closeday-openingweight']}</td>
	    <td>{$lang['closeday-added']}</td>
	    <td>{$lang['global-dispenses']}</td>
	    <td>{$lang['closeproduct-takeouts']}</td>
	    <td>{$lang['weight']}</td>
	    <td>{$lang['intstash']}</td>
	    <td>{$lang['extstash']}</td>
	   </tr>
EOD;
				$extractHeader = 'set';
				}
				
			} else {
				
				// Query to look up categories
				$selectCats = "SELECT id, name from categories WHERE id = $category";
			
				$resultCats = mysql_query($selectCats)
					or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
					
				$row = mysql_fetch_array($resultCats);
		  	    	$catName = $row['name'];
		  	    	$catID = $row['id'];
		  	    	
				if (${'otherHeader' . $catID} != 'set') {
					$productDetails .= <<<EOD
	   <tr>
	    <td colspan="8" class="tableHeader">
	    <h3 class='title'>$catName</h3>
	    </td>
	   </tr>
	   <tr class="thstyle">
	    <td class='left'>{$lang['global-name']}</td>
	    <td>{$lang['closeday-openingweight']}</td>
	    <td>{$lang['closeday-added']}</td>
	    <td>{$lang['global-dispenses']}</td>
	    <td>{$lang['closeproduct-takeouts']}</td>
	    <td>{$lang['weight']}</td>
	    <td>{$lang['intstash']}</td>
	    <td>{$lang['extstash']}</td>
	   </tr>
EOD;
				${'otherHeader' . $catID} = 'set';
				}
					
					
					$otherTotals[$catID]['catName'] = $catName;
					$otherTotals[$catID]['otherTotJar'] = $otherTotals[$catID]['otherTotJar'] + $jarWeight;
					$otherTotals[$catID]['otherTotIntSt'] = $otherTotals[$catID]['otherTotIntSt'] + $inStashInt;
					$otherTotals[$catID]['otherTotExtSt'] = $otherTotals[$catID]['otherTotExtSt'] + $inStashExt;
					$otherTotals[$catID]['otherTot'] = $otherTotals[$catID]['otherTotJar'] + $otherTotals[$catID]['otherTotIntSt'] + $otherTotals[$catID]['otherTotExtSt'];
					$otherTotals[$catID]['otherSoldToday'] = $otherTotals[$catID]['otherSoldToday'] + $soldToday;

				}
			


				$productDetails .= <<<EOD
				
	   <tr>
	    <td class='left'>$name <span class='smallerfont2'>$growtype</span></td>
	    <td class='right'>$openingWeight</td>
	    <td class='right'>$permAdditions</td>
	    <td class='right'>$sales</td>
	    <td class='right'>$permRemovals</td>
	    <td class='right'><strong>$jarWeight</strong></td>
	    <td class='right'>$inStashInt</td>
	    <td class='right'>$inStashExt</td>
	   </tr>
				
EOD;

		} // End product loop
		
		
		
	pageStart($lang['title-stock'], NULL, NULL, "pstock", "table admin", $lang['global-stockcaps'] . "<br /><a href='stock.php' class='headerlink'>(" . $lang['changeview'] . ")</a>", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
echo <<<EOD
	  <table class="default nonhover">
	   <tr>
	    <td colspan="8" class="tableHeader">
	    <h3 class='title'>{$lang['summarycaps']}</h3>
	    </td>
	   </tr>
	   <tr class="thstyle">
	    <td class='left'>{$lang['global-name']}</td>
	    <td>{$lang['closeday-openingweight']}</td>
	    <td>{$lang['closeday-added']}</td>
	    <td>{$lang['global-dispenses']}</td>
	    <td>{$lang['closeproduct-takeouts']}</td>
	    <td>{$lang['weight']}</td>
	    <td>{$lang['intstash']}</td>
	    <td>{$lang['extstash']}</td>
	   </tr>
	   <tr>
	    <td class='left'>{$lang['global-flowers']}</td>
	    <td class='right'>$flowerTotOpening g</td>
	    <td class='right'>$flowerTotPermAdd g</td>
	    <td class='right'>$flowerTotSales g</td>
	    <td class='right'>$flowerTotPermRem g</td>
	    <td class='right'><strong>$flowerTotJar g</strong></td>
	    <td class='right'>$flowerTotIntSt g</td>
	    <td class='right'>$flowerTotExtSt g</td>
	   </tr>
	   <tr>
	    <td class='left'>{$lang['global-extracts']}</td>
	    <td class='right'>$extractTotOpening g</td>
	    <td class='right'>$extractTotPermAdd g</td>
	    <td class='right'>$extractTotSales g</td>
	    <td class='right'>$extractTotPermRem g</td>
	    <td class='right'><strong>$extractTotJar g</strong></td>
	    <td class='right'>$extractTotIntSt g</td>
	    <td class='right'>$extractTotExtSt g</td>
	   </tr>
	   <tr>
	    <td colspan="8" class="tableHeader">
	    <h3 class='title'>{$lang['global-flowerscaps']}</h3>
	    </td>
	   </tr>
	   <tr class="thstyle">
	    <td class='left'>{$lang['global-name']}</td>
	    <td>{$lang['closeday-openingweight']}</td>
	    <td>{$lang['closeday-added']}</td>
	    <td>{$lang['global-dispenses']}</td>
	    <td>{$lang['closeproduct-takeouts']}</td>
	    <td>{$lang['weight']}</td>
	    <td>{$lang['intstash']}</td>
	    <td>{$lang['extstash']}</td>
	   </tr>
EOD;
	   
		echo $productDetails;
   		echo "</table>";
  
   		
}

	displayFooter();