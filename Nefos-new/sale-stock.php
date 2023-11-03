<?php
	//Created by Konstant for Task-14943362 on 27/09/2021
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	// Query to look up categories
	$selectCats = "SELECT id, name from b_categories WHERE id > 48 OR (id = 35 OR id = 34 OR id = 30 OR id = 19) ORDER by id ASC";
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
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.productid AS productid, pr.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice FROM b_products pr, b_purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
		
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = $categoryid";
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
	
	
	$selectProducts = "SELECT '1' AS category, g.flowerid AS productid, g.name AS name, p.purchaseid AS purchaseid, p.growType AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice FROM flower g, purchases p WHERE p.category = 9999 AND p.productid = g.flowerid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW())) UNION ALL SELECT '2' AS category, h.extractid AS productid, h.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice FROM extract h, purchases p WHERE p.category = 9999 AND p.productid = h.extractid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
	
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

				// Original purchase
   				$purchaseLookup = "SELECT purchaseQuantity from b_purchases where purchaseid = $purchaseid";
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
					$openingWeight = $row['purchaseQuantity'];

			if(!empty($_POST['untilDate'])){
						$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
						$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
						
						$timeLimit = "AND DATE(s.saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
			}elsE{
						$timeLimit = "AND DATE(s.saletime) > '2020-07-01'";
			}
				
				// Sales
				$selectSales = "SELECT SUM(d.quantity) FROM b_salesdetails d, b_sales s WHERE d.purchaseid = $purchaseid AND d.saleid = s.saleid AND d.amount <> 0 $timeLimit";
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
		
				// Gifts
		$selectSales = "SELECT SUM(d.quantity) FROM b_salesdetails d, b_sales s WHERE d.purchaseid = $purchaseid AND d.saleid = s.saleid AND d.amount = 0 $timeLimit"; 
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
		
					
				// Additions and Removals (not permanent, just wrong variable name)
				$selectPermAdditions = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1";
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
				
				$selectPermRemovals = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2";
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
				$jarWeight = $openingWeight + $permAdditions - $permRemovals - $sales - $gifts;	
				
				$weightTotal = $jarWeight + $inStashInt + $inStashExt;
				
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.quantity) FROM b_salesdetails d, b_sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
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
				$selectSales = "SELECT SUM(d.quantity) FROM b_salesdetails d, b_sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid AND d.amount = 0";
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
					
		$totStock = $jarWeight + $inStashInt + $inStashExt;

			
				// Query to look up categories
				$selectCats = "SELECT id, name from b_categories WHERE id = $category";
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
		  	    	
		$madrid = $jarWeight + $inStashInt;

		// calculate last month sale

		 $selectAvgsale = "SELECT AVG(d.quantity) FROM b_salesdetails d, b_sales s WHERE  d.saleid = s.saleid AND d.purchaseid = $purchaseid $timeLimit";
			try
			{
				$result_avg = $pdo3->prepare("$selectAvgsale");
				$result_avg->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$avg_row = $result_avg->fetch();
				$avgSale = $avg_row['AVG(d.quantity)'];
				if(!empty($avgSale)){
					$salePerDay = ceil($avgSale);
				}else{
					$salePerDay = 0;
				}
				// days to run out of stock
				$stockEndDays = 0;
				if(!empty($salePerDay)){
					$stockEndDays = ceil($totStock / $salePerDay);
				}
				$stockEndDate = "N/A";
				if($totStock < 0){
					$stockEndDate = "Out of stock";
				}
				//$stockEndDate = date("d-m-Y", strtotime(' - 5 days'));
				if(!empty($stockEndDays)){
						if($stockEndDays > 0){
							$stockEndDate = date("d-m-Y", strtotime(' + '.$stockEndDays.' days'));
						}else{
/*							$stockEndDays2 = 0 - $stockEndDays;
							$stockEndDate = date("d-m-Y", strtotime(' - '.$stockEndDays2.' days'));*/
							$stockEndDate = "Out of stock";
						}
				}

		  	    	
		if ($madrid < 1) {
			$madrid = "<span style='color: red;'>$madrid</span>";
		} else {
			$madrid = $madrid;
		}
		if ($totStock < 1) {
			$totStock = "<span style='color: red;'>$totStock</span>";
		} else {
			$totStock = $totStock;
		}
		  	    	
				if (${'otherHeader' . $catID} != 'set') {
					
		if ($x > 0) {
			$productDetails .=  "
	  </table>
	  </div>
	  </div>
	  <br />";
	
		}

					$productDetails .= <<<EOD
<div id="mainbox-no-width">
  <div id='mainboxheader'>
   <center>$catName</center>
  </div>

 <div class='boxcontent'>
	  <table class="default nonhover">
	   <tr class="thstyle">
	    <th class='left'>{$lang['global-name']}</th>
	    <th>Madrid</th>
	    <th>Ireland</th>
	    <th><strong>TOTAL</strong></th>
	    <th><strong>Stock End (Date)</strong></th>
	   </tr>
EOD;
				${'otherHeader' . $catID} = 'set';
				}
					

			


				$productDetails .= <<<EOD
				
	   <tr class='clickableRow' href='bar-purchase.php?purchaseid=$purchaseid'>
	    <td class='left'>$name <span class='smallerfont2'>$growtype</span> ($purchaseid)</td>
	    <td class='centered'>$madrid</td>
	    <td class='centered'>$inStashExt</td>
	    <td class='centered'><strong>$totStock</strong></td>
	    <td class='centered'>$stockEndDate</td>
	   </tr>
				
EOD;
	$x++;
		} // End product loop
			$deleteSaleScript = <<<EOD
	    $(document).ready(function() {

	    	$( function() {
			    $( "#datepicker" ).datepicker({
					dateFormat: "dd-mm-yy"
			    });	    
			  
			    $( "#datepicker2" ).datepicker({
					dateFormat: "dd-mm-yy"
			    });	    
			 });


		});
		

EOD;
		
	pageStart($lang['title-stock'], NULL, $deleteSaleScript, "pstock", "table admin", $lang['global-stockcaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	?>
 <center>
	 <div id="filterbox">
	  <div id="mainboxheader">
	   Filter
	  </div>
	  <div class="boxcontent">
	   <form action="" method="POST" style="margin-top: 3px; display: inline-block;">
	    	<?php
					if (isset($_POST['fromDate'])) {
						
						echo <<<EOD
						 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['fromDate']}" autocomplete="off"/>
						 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['untilDate']}" autocomplete="off" required=""/>
					EOD;
							
						} else {
							
							echo <<<EOD
							 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="{$lang['from-date']}" value= "01-07-2020" autocomplete="off" />
							 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="{$lang['to-date']}" required="" autocomplete="off"/>
					EOD;

						}
					?>
				   <br>
				   <button type="submit"  class='cta1' style='display: inline-block; width: 50px;'>OK</button>
	   </form>
	  </div>
	 </div>  
</center> 
	<?php
	
echo <<<EOD
	  <center>
EOD;
	   
		echo $productDetails;
  		echo "</table></div></div>";
   		

	displayFooter();