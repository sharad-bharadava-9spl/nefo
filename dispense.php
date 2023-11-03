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
	
if ($_SESSION['realWeight'] == 0) {
	
	// Query to look up sale
	$selectSale = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount, direct, discounteur FROM sales WHERE saleid = $saleid";

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
		
		$userLookup = "SELECT first_name, memberno, userGroup, photoExt FROM users WHERE user_id = {$userid}";
		
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
		$photoExt = $row['photoExt'];
		$userGroup = $row['userGroup'];
				
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
			   
		echo "
		
<center>
  <a href='print-dispense.php?saleid=$saleid' target='_blank' class='cta1'>{$lang['print']}</a>";
  
		if ($_SESSION['puestosOrNot'] == 1) {
		
			if ($workStation == 1 || $workStation == 6 || $workStation == 11 || $workStation == 16) {
				echo "<img src='images/profile-reception.png' />&nbsp;";
			}
			if ($_SESSION['userGroup'] == 1 || $workStation == 5 || $workStation == 6 || $workStation == 15 || $workStation == 16) {
				echo "<a href='bar-new-sale-2.php?user_id=$userid' class='cta1'>{$lang['bar']}</a>";
			}
			if ($_SESSION['userGroup'] == 1 || $workStation == 10 || $workStation == 11 || $workStation == 15 || $workStation == 16) {
				echo "<a href='new-dispense-2.php?user_id=$userid' class='cta1'>{$lang['global-dispense']}</a>";
			}

		} else {
			
				echo "<a href='bar-new-sale-2.php?user_id=$userid' class='cta1'>{$lang['bar']}</a>";
				echo "<a href='new-dispense-2.php?user_id=$userid' class='cta1'>{$lang['global-dispense']}</a>";
		}
  
  
	$topimg = $google_root."images/_$domain/members/$userid.$photoext";

	$object_exist = object_exist($google_bucket, $google_root_folder.$topimg);

	if ($object_exist === false) {
		$topimg = $google_root.'images/silhouette-new-big.png';
	}
	
		$query = "SELECT groupName FROM usergroups WHERE userGroup = $userGroup";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$userGroupName = $row['groupName'];
			
	if ($userGroup == 7) {
		$groupName = "<span class='usergrouptextbanned'>$userGroupName</span>";		
	} else {
		$groupName = "<span class='usergrouptext'>$userGroupName</span>";
		
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
   <td><strong></td>
  </tr>
 </table>
 </center>
</div></center>
EOD;

		echo "
</center>
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
		      <td class='centered'><span class='relativeitem'>$commentRead</span></td>
		</tr></table></span>
		


  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->
 

 ";
}



 echo "<center><img src='images/dispensesigs/$saleid.png' /></center>";






} else {
	
	
	
	
	
	
	
	
	// Query to look up sale
	$selectSale = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, realQuantity, units, adminComment, creditBefore, creditAfter, discount, direct, discounteur FROM sales WHERE saleid = $saleid";

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
	
	while ($sale = $result->fetch()) {
	
		$formattedDate = date("d M H:i", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$quantity = $sale['quantity'];
		$realQuantity = $sale['realQuantity'];
		$units = $sale['units'];
		$discount = $sale['discount'];
		$direct = $sale['direct'];
		$discounteur = $sale['discounteur'];
		
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
		
		$userLookup = "SELECT first_name, memberno, photoExt, userGroup FROM users WHERE user_id = {$userid}";
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
			$photoExt = $row['photoExt'];
			$userGroup = $row['userGroup'];
				
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.realQuantity, d.amount FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
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
			$onesaleResult5 = $pdo3->prepare("$selectoneSale");
			$onesaleResult5->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

			   
		echo "
		
<center>
  <a href='print-dispense.php?saleid=$saleid' target='_blank' class='cta1'>{$lang['print']}</a>";
  
		if ($_SESSION['puestosOrNot'] == 1) {
		
			if ($workStation == 1 || $workStation == 6 || $workStation == 11 || $workStation == 16) {
				echo "<img src='images/profile-reception.png' />&nbsp;";
			}
			if ($_SESSION['userGroup'] == 1 || $workStation == 5 || $workStation == 6 || $workStation == 15 || $workStation == 16) {
				echo "<a href='bar-new-sale-2.php?user_id=$userid' class='cta1'>{$lang['bar']}</a>";
			}
			if ($_SESSION['userGroup'] == 1 || $workStation == 10 || $workStation == 11 || $workStation == 15 || $workStation == 16) {
				echo "<a href='new-dispense-2.php?user_id=$userid' class='cta1'>{$lang['global-dispense']}</a>";
			}

		} else {
			
				echo "<a href='bar-new-sale-2.php?user_id=$userid' class='cta1'>{$lang['bar']}</a>";
				echo "<a href='new-dispense-2.php?user_id=$userid' class='cta1'>{$lang['global-dispense']}</a>";
		}
		
	$topimg = $google_root."images/_$domain/members/$userid.$photoext";

	$object_exist = object_exist($google_bucket, $google_root_folder.$topimg);

	if ($object_exist === false) {
		$topimg = $google_root.'images/silhouette-new-big.png';
	}
	
		$query = "SELECT groupName FROM usergroups WHERE userGroup = $userGroup";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$userGroupName = $row['groupName'];
			
	if ($userGroup == 7) {
		$groupName = "<span class='usergrouptextbanned'>$userGroupName</span>";		
	} else {
		$groupName = "<span class='usergrouptext'>$userGroupName</span>";
		
	}
		
  	echo <<<EOD
<center><div class='topaval' style='background-color: white; margin-top: 0;'>
  <center> <span class="profilepicholder" style="float: left; margin-right: 15px;" ><img class="profilepic" src="$topimg" width="143" />$highroller</span>


 <table style="display: inline-block; vertical-align: top; text-align: left;">
  <tr>
   <td class='biggerfont'><span class='firsttext'>#$memberno</span><br />
   <span class='nametext'>$first_name $last_name</span><br /> $groupName<br /><strong>$formattedDate</strong></td>
  </tr>
  <tr>
   <td><strong></td>
  </tr>
 </table>
 </center>
</div></center>
EOD;

		echo "
<br />
	 <table id='detailedsale' class='default'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['global-quantity']}</th>
	    <th>Real {$lang['global-quantity']}</th>
	    <th>{$_SESSION['currencyoperator']}</th>
	    <th>Total g</th>
	    <th>Real g</th>
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
				$categoryDetails = "SELECT name, type FROM categories WHERE id = {$onesale['category']}";
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
					$type = $row['type'];
			}

			if ($onesale['category'] < 3 || $type == 1) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
			}
		}
		echo "</td><td class='right'>";
		while ($onesale = $onesaleResult4->fetch()) {
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
				echo number_format($onesale['realQuantity'],2) . " g<br />";
			} else {
				echo "<br />";
			}
		}
		echo "</td><td class='right'>";
		while ($onesale = $onesaleResult5->fetch()) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>{$_SESSION['currencyoperator']}</span><br />";
		}
		echo "</td><td class='right'>";
		echo number_format($quantity,2) . " g</td>";
		echo "</td><td class='right'>";
		echo number_format($realQuantity,2) . " g</td>";
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
		<td class='centered'><span class='relativeitem'>$commentRead</span></td>
		</tr></table></span>
		


  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->
 

 ";
 

}
 echo "<center><img src='images/dispensesigs/$saleid.png' /></center>";
 
 
}
   
   
   displayFooter();
?>
<script type="text/javascript">
		$(document).ready(function() {
			$('#detailedsale').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					},
					7: {
						sorter: "dates"
					}
				}
			}); 

		});
</script>
