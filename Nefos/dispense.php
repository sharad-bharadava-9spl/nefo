<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Retrieve System settings
	getSettings();
	
	// <input type='hidden' name='confirmReject' value='confirm' />
	
	if ($_POST['addcomment'] == 'yes') {
		
		$saleid = $_POST['saleid'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['intText'])));
		
		// Update order number to confirm
		$updateOrder = "UPDATE sales SET intcomment = '$comment' WHERE saleid = $saleid";
		try
		{
			$result = $pdo3->prepare("$updateOrder")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$_SESSION['successMessage'] = "Comment added succesfully!";
		header("Location: orders.php");
		exit();
		
	}
	if ($_POST['confirmReject'] == 'confirm') {
		
		$saleid = $_POST['saleid'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['confText'])));
		
		// Update order number to confirm
		$updateOrder = "UPDATE sales SET fulfilled = 1, comments = '$comment' WHERE saleid = $saleid";
		try
		{
			$result = $pdo3->prepare("$updateOrder")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$_SESSION['successMessage'] = "Order fulfilled!";
		header("Location: orders.php");
		exit();
		
	}
	
	// Get the sale ID
	if (isset($_GET['saleid'])) {
		$saleid = $_GET['saleid'];
	} else {
		handleError($lang['error-nosaleid'],"");
	}

	
	// Query to look up sale
	$selectSale = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, realQuantity, units, adminComment, creditBefore, creditAfter, userConfirmed, fulfilled, delivered, drivernumber, customer, comments, intcomment FROM sales WHERE saleid = $saleid";
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
	
	
	pageStart("Order", NULL, NULL, "psales", "Sale", "ORDER", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

	  
	  <?php
		while ($sale = $results->fetch()) {
	
		$formattedDate = date("Y M d H:i", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$quantity = $sale['quantity'];
		$realQuantity = $sale['realQuantity'];
		$units = $sale['units'];
		$clubConfirmed = $sale['clubConfirmed'];
		$delivered = $sale['delivered'];
		$drivernumber = $sale['drivernumber'];
		$fulfilled = $sale['fulfilled'];
		$customer = $sale['customer'];
		$adminComment = $sale['adminComment'];
		$comments = $sale['comments'];
		$intcomment = $sale['intcomment'];
		
/*	if ($sale['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/truck.png' id='comment$saleid' /><div id='helpBox$saleid' class='helpBox'>{$sale['adminComment']}</div>
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
		
	}*/
	
	if ($fulfilled == 0) {
		
		$clubApproved = "<img src='images/awaiting.png' width='16' alt='Awaiting action' />";
		
	} else if ($fulfilled == 1) {
		
		$clubApproved = "<img src='images/complete.png' width='16' alt='Approved' />";
		
	} else {
		
		$clubApproved = "<img src='images/delete.png' width='16' alt='Rejected!' />";
				
	}
	

	
	if ($delivered == 0) {
		
		$deliveredImg = "<img src='images/awaiting.png' width='16' alt='Awaiting action' />";
		
	} else if ($delivered == 1) {
		
		$deliveredImg = "<img src='images/complete.png' width='16' alt='Approved' />";
				
	} else {
		
		$deliveredImg = "<img src='images/delete.png' width='16' alt='Rejected!' />";
		
	}
		
		
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		$userLookup = "SELECT first_name, last_name, memberno, telephone, email, street, streetnumber, flat, postcode, city FROM users WHERE user_id = {$userid}";
		try
		{
			$result = $pdo3->prepare("$userLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$first_name = $row['first_name'];
			$last_name = $row['last_name'];
			$memberno = $row['memberno'];
			$telephone = $row['telephone'];
			$email = $row['email'];
			$address = $row['address'];
			$city = $row['city'];
			$state = $row['state'];
			$zip = $row['zip'];
				
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.realQuantity, d.amount FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
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
			   
		// Lookup club info
		$query = "SELECT customer FROM db_access WHERE domain = '$customer'";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$custnumber = $row['customer'];
		
	$query = "SELECT shortName, street, streetnumber, flat, postcode, city, email, phone FROM customers WHERE number = '$custnumber'";
	try
	{
		$result = $pdo2->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$shortName = $row['shortName'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$email = $row['email'];
		$phone = $row['phone'];
			   
		echo "
		
<center>
<div id='orderdetails'>
<center><strong><u>$formattedDate</u></strong></center><br />

  <h4>$shortName</h4>
  $street $streetnumber $flat<br />
  $postcode $city<br /><br />
  Contact person: $adminComment<br />
  Comment from client: $comments


</div>
</center>
<br />
	 <table id='detailedsale' class='default'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th style='text-align: center;'>&euro; (w/o IVA)</th>
	    <th>{$lang['global-quantity']}</th>
	    <th>Total &euro; (w IVA)</th>
	    <th>Enviado?</th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
<tr><td>";

	  	   	foreach ($totResult as $onesale) {	
			if ($onesale['category'] == 1) {
				$category = $lang['global-flower'];
			} else if ($onesale['category'] == 2) {
				$category = $lang['global-extract'];
			} else {
				// Query to look for category
				$categoryDetails = "SELECT name FROM categories WHERE id = {$onesale['category']}";
		try
		{
			$result = $pdo3->prepare("$categoryDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$category = $row['name'];
			}
			echo $category . '<br />';
		}
		echo "</td><td>
";
//while loop goes here
	  	   	foreach ($totResult as $onesale) {	
			
   			$productid = $onesale['productid'];
   			
			
	// Determine product type, and assign query variables accordingly
	if ($onesale['category'] == 1) {
		$purchaseCategory = 'Flower';
		$queryVar = ', breed2';
		$prodSelect = 'flower';
		$prodJoin = 'flowerid';
	} else if ($onesale['category'] == 2) {
		$purchaseCategory = 'Extract';
		$queryVar = '';
		$prodSelect = 'extract';
		$prodJoin = 'extractid';
	} else {
		$purchaseCategory = $category;
		$queryVar = '';
		$prodSelect = 'products';
		$prodJoin = "productid";
	}
	
		$selectProduct = "SELECT name{$queryVar} FROM {$prodSelect} WHERE ({$prodJoin} = {$productid})";
		try
		{
			$result = $pdo3->prepare("$selectProduct");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
	    
		if ($row['breed2'] != '') {
			$name = $row['name'] . " x " . $row['breed2'];
		} else { 
			$name = $row['name'];
		}
		

			echo $name . "<br />";

}
		echo "</td><td class='right'>";
	  	   	foreach ($totResult as $onesale) {	
			echo number_format($onesale['amount'] / $onesale['quantity'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td><td class='right'>";
	  	   	foreach ($totResult as $onesale) {	
			if ($onesale['category'] < 3) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
			}
		}
		echo "</td><td class='right'>" . number_format($amount,2) . " <span class='smallerfont'>&euro;</span></td>";
		echo "<td class='centered'>$clubApproved</td>";
		echo "<td class='centered relative'>$commentRead</td>
		</tr></table></span>
		
<br />
<center>
";
 if ($fulfilled == 0) {

	 echo "
<div class='approveOrReject'>
 <form id='registerForm' action='' method='POST'>
  <input type='hidden' name='addcomment' value='yes' />
  <input type='hidden' name='saleid' value='$saleid' />
 <strong style='color: #478034;'>Internal comment</strong><br /><br />
<br />
  <textarea class='inBox' name='intText' placeholder=''>$intcomment</textarea><br /><br />
  <button type='submit'>Save comment</button>
 </form>
<br /><br />
 <form id='registerForm' action='' method='POST'>
  <input type='hidden' name='confirmReject' value='confirm' />
  <input type='hidden' name='saleid' value='$saleid' />
 <strong style='color: #478034;'>Completear pedido</strong><br /><br />
<br />
  <textarea class='inBox' name='confText' placeholder='Comentarios para el cliente?'>$comments</textarea><br /><br />
  <button type='submit'>Completear</button>
 </form>
</div>
<!--<div class='approveOrReject'>
 <form id='registerForm' action='' method='POST'>
  <input type='hidden' name='confirmReject' value='reject' />
  <input type='hidden' name='saleid' value='$saleid' />
  <strong style='color: red;'>Reject order</strong><br /><br />
 
  <textarea class='inBox' name='confText' placeholder='Please type the reason for rejecting the order' style='margin-top: 57px;'></textarea><br /><br />
  <button type='submit'>REJECT</button>
 </form>
</div>-->
</center>
<br />

  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->
 

 ";
 
}
}	
   
   displayFooter();
