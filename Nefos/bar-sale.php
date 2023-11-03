<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the sale ID
	if (isset($_GET['saleid'])) {
		$saleid = $_GET['saleid'];
	} else {
		handleError($lang['error-nosaleid'],"");
	}
	
	// Query to look up sale
	$selectSale = "SELECT saleid, saletime, userid, amount, unitsTot, adminComment, creditBefore, creditAfter, direct FROM b_sales WHERE saleid = $saleid";
		try
		{
			$results = $pdo3->prepare("$selectSale");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
	pageStart("SALE", NULL, NULL, "psales", "Sale", "SALE", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

	  
	  <?php
	  
while ($sale = $results->fetch()) {
	
		$formattedDate = date("d M Y H:i", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$direct = $sale['direct'];
		
		if ($direct == 3) {
			$paymentMethod = $lang['global-credit'];
		} else if ($direct == 2) {
			$paymentMethod = $lang['card'];
		} else if ($direct == 1) {
			$paymentMethod = $lang['cash'];
		} else {
			$paymentMethod = '';
		}

		if ($sale['adminComment'] != '') {
			
			$commentRead = "
			                <img src='images/comments.png' id='comment$saleid' /><div id='helpBox$saleid' class='helpBox'>{$sale['adminComment']}</div>
			                <script>
			                  	$('#comment$saleid').on({
							 		'mouseover' : function() {
									 	$('#helpBox$saleid').css('display', 'block');
							  		},
							  		'mouseout' : function() {
									 	$('#helpBox$saleid').css('display', 'none');
								  	}
							  	});
							</script>
			                ";
			
		} else {
			
			$commentRead = "";
			
		}
		
		$quantity = $sale['unitsTot'];
		$amount = $sale['amount'];
		
		$userDetails = "SELECT number, shortName FROM customers WHERE id = '$userid'";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$first_name = $row['shortName'];
		$memberno = $row['number'];
				
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM b_salesdetails d, b_sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		try
		{
			$onesaleResult = $pdo3->prepare("$selectoneSale");
			$onesaleResult->execute();
			$totResult = $onesaleResult->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			   
		echo "
		
<center>
  <a href='bar-new-sale-2.php?user_id=$userid' class='cta'>New sale</a>
  <a href='bar-edit-sale.php?saleid=$saleid' class='cta'>Edit</a>
<div id='profilearea'>
<span class='profilefirst'><a href='customer.php?user_id=$userid'>#$memberno - $first_name</a><br />
$formattedDate</span><br />
</div>
</center>
<br />
	 <table id='detailedsale' class='default'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['global-quantity']}</th>
	    <th>&euro;</th>
	    <th>Total u</th>
	    <th>Total &euro;</th>";
if ($direct == 3) {
	    echo "<th>{$lang['dispense-oldcredit']}</th>
	    <th>{$lang['dispense-newcredit']}</th>";
}
		echo "
	    <th>{$lang['paid-by']}</th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
<tr><td>";

	  	   	foreach ($totResult as $onesale) {	
			
			$category = $onesale['category'];
			
			// Look up category name
			$selectCatName = "SELECT name from b_categories where id = $category";
		try
		{
			$result = $pdo3->prepare("$selectCatName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$catName = $row['name'] ;
				
				echo $catName . "<br />";


			
			
			
		}
		echo "</td><td>
";

	  	   	foreach ($totResult as $onesale) {	
			
			$productid = $onesale['productid'];
			
			// Look up service name
			$selectServName = "SELECT name from b_products where productid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectServName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$servName = $row['name'] ;
				
				echo $servName . "<br />";


			
			
			
		}

		echo "</td><td class='right'>";
	  	   	foreach ($totResult as $onesale) {	
			if ($onesale['quantity'] == 0) {
				$fullQuantity = '';
			} else {
				$fullQuantity = $onesale['quantity'];
			}
			echo number_format($fullQuantity,1) . " u<br />";
		}
		echo "</td><td class='right'>";
	  	   	foreach ($totResult as $onesale) {	
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td><td class='right'>";
		echo number_format($quantity,1) . " u</td>";
		echo "<td class='right'>" . number_format($amount,2) . "<span class='smallerfont'>&euro;</span></td>";
		
if ($direct == 3) {
		echo "<td class='right'>" . number_format($credit,2) . "<span class='smallerfont'>&euro;</span></td>";
		echo "<td class='right'>" . number_format($newcredit,2) . "<span class='smallerfont'>&euro;</span></td>";
}
		echo "<td class='left'>$paymentMethod</td>
		      <td class='centered relative'>$commentRead</td>
		</tr></table></span>
		


  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->
 

 ";
}
   displayFooter(); ?>
   
   
