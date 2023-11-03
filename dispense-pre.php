<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Retrieve System settings
	getSettings();
	
	$selectSale = "SELECT workstation FROM users WHERE user_id = {$_SESSION['user_id']}";
	
	try
	{
		$result = $pdo3->prepare("$selectSale");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$workStation = $row['workstation'];

	
	// Get the sale ID
	if (isset($_GET['saleid'])) {
		$saleid = $_GET['saleid'];
	} else {
		handleError($lang['error-nosaleid'],"");
	}
	
	
	// Query to look up sale
	$selectSale = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount, direct, discounteur, puesto FROM sales WHERE saleid = $saleid";

	try
	{
		$result = $pdo3->prepare("$selectSale");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	
	
	pageStart($lang['title-dispense'], NULL, NULL, "psales", "Sale", $lang['global-dispense'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
	  
	  <?php
while ($sale = $result->fetch()) {	
	
		$formattedDate = date("d M H:i", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		$discount = $sale['discount'];
		$direct = $sale['direct'];
		$discounteur = $sale['discounteur'];
		$puesto = $sale['puesto'];
		
		if ($puesto == '11') {
			if ($_SESSION['lang'] == 'es') {
				$saletype = "Entrega";
			} else {
				$saletype = "Delivery";
			}
 	 	 	 	 	 	
		$selectSale = "SELECT street, streetnumber, flat, postcode, city, telephone, time FROM delivery WHERE saleid = $saleid";
		try
		{
			$resultw = $pdo3->prepare("$selectSale");
			$resultw->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$roww = $resultw->fetch();
			$street = $roww['street'];
			$streetnumber = $roww['streetnumber'];
			$flat = $roww['flat'];
			$postcode = $roww['postcode'];
			$city = $roww['city'];
			$telephone = $roww['telephone'];
			$time = date("d-m-Y H:i", strtotime($roww['time']));
			
		
		} else if ($puesto == '22') {
			if ($_SESSION['lang'] == 'es') {
				$saletype = "Recogida";
			} else {
				$saletype = "Collection";
			}
		} else {
			$saletype = "";
		}
		
		
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

		
		
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		
		$userLookup = "SELECT first_name, memberno, email FROM users WHERE user_id = {$userid}";
		
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
		$memberno = $row['memberno'];
		$email = $row['email'];
				
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		try
		{
			$onesaleResult = $pdo3->prepare("$selectoneSale");
			$onesaleResult->execute();
			$onesaleResult2 = $pdo3->prepare("$selectoneSale");
			$onesaleResult2->execute();
			$onesaleResult3 = $pdo3->prepare("$selectoneSale");
			$onesaleResult3->execute();
			$onesaleResult4 = $pdo3->prepare("$selectoneSale");
			$onesaleResult4->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			   
 	$topimg = $google_root."images/_$domain/members/$userid.$photoExt";

 	$object_exist = object_exist($google_bucket, $google_root_folder."images/_$domain/members/$userid.$photoExt");
	
	if (!$object_exist) {
		$topimg = $google_root.'images/silhouette-new.png';
	}
 
  
  	echo <<<EOD
<center><div class='topaval' style='background-color: white; margin-top: 0;'>
  <center> <span class="profilepicholder" style="float: left; margin-right: 15px;" ><img class="profilepic" src="$topimg" width="143" />$highroller</span>


 <table style="display: inline-block; vertical-align: top; text-align: left;">
  <tr>
   <td class='biggerfont'><a href='profile.php?user_id=$userid'><span class='firsttext'>#$memberno</span><br />
   <span class='nametext'>$first_name $last_name</span></a><br /> $groupName<br /><strong>$formattedDate</strong></td>
  </tr>
  <tr>
   <td><br /><strong>{$lang['address']}:</strong><br />$street $streetnumber $flat<br />
$postcode $city<br /><br />
<strong>{$lang['member-contactdetails']}:</strong><br />$telephone<br />
$email<br /><br />
   </td>
  </tr>
 </table>
 </center>
</div></center>
EOD;
		echo "<center>
<br />
	 <table id='detailedsale' class='default'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['global-quantity']}</th>
	    <th>{$_SESSION['currencyoperator']}</th>
	    <th>Total g</th>
	    <th>Total u</th>
	    <th>Total {$_SESSION['currencyoperator']}</th>
	    <th>{$lang['member-discount']} %</th>
	    <th>{$lang['member-discount']} {$_SESSION['currencyoperator']}</th>";
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

		while ($onesale = $onesaleResult->fetch()) {
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
		while ($onesale = $onesaleResult2->fetch()) {
			
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
		while ($onesale = $onesaleResult3->fetch()) {
			if ($onesale['category'] > 2) {
				
				// Query to look for category
				$categoryDetailsC = "SELECT name, type FROM categories WHERE id = {$onesale['category']}";
				try
				{
					$result = $pdo3->prepare("$categoryDetailsC");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$rowC = $result->fetch();
					$category = $rowC['name'];
					$type = $rowC['type'];
			}

			if ($onesale['category'] < 3 || $type == 1) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
			}
		}
		echo "</td><td class='right'>";
		while ($onesale = $onesaleResult4->fetch()) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>{$_SESSION['currencyoperator']}</span><br />";
		}
		echo "</td><td class='right'>";
		echo number_format($quantity,2) . " g</td>";
		echo "</td><td class='right'>";
		echo number_format($units,2) . " u</td>";
		echo "<td class='right'>" . number_format($amount,2) . "<span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>";
		echo "<td class='centered'>" . number_format($discount,0) . "<span class='smallerfont'>%</span></td>";
		echo "<td class='centered'>" . number_format($discounteur,2) . "<span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>";
		
if ($direct == 3) {
		echo "<td class='right'>" . number_format($credit,2) . "<span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>";
		echo "<td class='right'>" . number_format($newcredit,2) . "<span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>";
}
		echo "<td class='left'>$paymentMethod</td>
		      <td class='centered relative'>$commentRead</td>
		</tr></table></span>
		


  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->
 

 ";
}








   displayFooter();
