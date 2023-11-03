<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
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
	$selectSale = "SELECT saleid, saletime, userid, amount, unitsTot, adminComment, creditBefore, creditAfter, direct, discount, discounteur FROM b_sales WHERE saleid = $saleid";
	
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
	
	pageStart("SALE", NULL, NULL, "psales", "Sale", "SALE", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

	  
	  <?php
	  
while ($sale = $result->fetch()) {	
	
		$formattedDate = date("d M H:i", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
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
		$quantity = $sale['unitsTot'];
		
		
		$amount = $sale['amount'];
		
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
				
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM b_salesdetails d, b_sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
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
  <a href='print-bar-sale.php?saleid=$saleid' target='_blank' class='cta1'>{$lang['print']}</a>";
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
		
	$topimg = "images/_$domain/members/$userid.$photoExt";
	
	if (!file_exists($topimg)) {
		$topimg = 'images/silhouette-new.png';
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
<br />
	 <table id='detailedsale' class='default'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['global-quantity']}</th>
	    <th>&euro;</th>
	    <th>Total u</th>
	    <th>Total &euro;</th>
	    <th>{$lang['member-discount']} %</th>
	    <th>{$lang['member-discount']} &euro;</th>";
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

		while ($onesale = $onesaleResult2->fetch()) {
			
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
		while ($onesale = $onesaleResult3->fetch()) {
			if ($onesale['quantity'] == 0) {
				$fullQuantity = '';
			} else {
				$fullQuantity = $onesale['quantity'];
			}
			echo number_format($fullQuantity,1) . " u<br />";
		}
		echo "</td><td class='right'>";
		while ($onesale = $onesaleResult4->fetch()) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td><td class='right'>";
		echo number_format($quantity,1) . " u</td>";
		echo "<td class='right'>" . number_format($amount,2) . "<span class='smallerfont'>&euro;</span></td>";
		echo "<td class='centered'>" . number_format($discount,0) . "<span class='smallerfont'>%</span></td>";
		echo "<td class='centered'>" . number_format($discounteur,2) . "<span class='smallerfont'>&euro;</span></td>";
		
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
   
   
