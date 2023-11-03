<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Is Workstations enabled?
	if ($_SESSION['puestosOrNot'] == 1) {
		
		if ($_SESSION['workstation'] != 'dispensary' && $_SESSION['userGroup'] > 1) {
			
   			handleError("No tienes permiso para ver esta pagina! / You are not authorized to see that page.","User has too low access level.");
			exit();
			
		}
		
	}
	
	getSettings();
	
	// Check if new Filter value was submitted, and assign query variable accordingly
	if (isset($_POST['filter'])) {
				
		$filterVar = $_POST['filter'];
		
		if ($filterVar == 100) {
			
			$limitVar = "LIMIT 100";
			$optionList = "<option value='$filterVar'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 250) {
			
			$limitVar = "LIMIT 250";
			$optionList = "<option value='100'>{$lang['last']} 100</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 500) {
			
			$limitVar = "LIMIT 500";
			$optionList = "<option value='$filterVar'>{$lang['last']} 500</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>";
			
		} else {
			
			// Grab month and year number
			$month = substr($filterVar, 0, strrpos($filterVar, '-'));	
			$year = substr($filterVar, strrpos($filterVar, '-') + 1);
			
			$timeLimit = "AND MONTH(saletime) = $month AND YEAR(saletime) = $year";
			
			$optionList = "<option value='filterVar'>$filterVar</option>
				<option value='100'>{$lang['last']} 100</option>
				<option value='250'>{$lang['last']} 250</option>
				<option value='500'>{$lang['last']} 500</option>";	
					
		}
			
	} else {
		
		$limitVar = "LIMIT 100";
		
		$optionList = "<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
	
	// Check if 'entre fechas' was utilised
	if (!empty($_POST['untilDate'])) {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "AND DATE(saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$limitVar = "";
			
	}else{
		$timeLimit = '';
	}
	// usergroup filter
	$selectedUsergroup = "1,2,3,4,5";
		if(isset($_POST['submitted'])){
			$firstSelect = 'false';
			//  code to filter the sales from usergroups
			if(isset($_POST['cashBox'])){
				$selectedUserArr = $_POST['cashBox'];
				$selectedUsergroup = implode(",", $selectedUserArr);
				if(in_array('all', $selectedUserArr)){
					$selectedUsergroup = "1,2,3,4,5";
				}
				$getUsers = "SELECT user_id FROM users WHERE userGroup IN ($selectedUsergroup)";
				$result = $pdo3->prepare("$getUsers");
				$result->execute();
				while($user_ids = $result->fetch()){
					$userArr[] = $user_ids['user_id'];
				}
				if(!empty($userArr)){
					array_push($userArr, 999999);
				}
				$selectedUsers = implode(',',$userArr);
				if(empty($selectedUsers) || $selectedUsers ==''){
					$selectedUsers = -1;
				}
				if(in_array('all', $selectedUserArr)){
					$selectedUsers = $selectedUsers . ', 0';
				}
				$user_limit = "AND operatorid IN ($selectedUsers)";
			}else{
				$user_limit = 'AND operatorid IN (0)';
			}
		}else{
			 $firstSelect = 'true';
			  $getUsers = "SELECT user_id FROM users WHERE userGroup IN ($selectedUsergroup)";
				$result = $pdo3->prepare("$getUsers");
				$result->execute();
				while($user_ids = $result->fetch()){
					$userArr[] = $user_ids['user_id'];
				}
				array_push($userArr, 999999);
			    $selectedUsers = implode(',',$userArr);
			    if(empty($selectedUsers) || $selectedUsers == ''){
					$selectedUsers = -1;		    	
			    }else{
			    	$selectedUsers .= ',0';
			    }

			   $user_limit = "AND operatorid IN ($selectedUsers)";
		}
	
if ($_SESSION['realWeight'] == 0) {
	
	if ($_SESSION['userGroup'] == 1 || ($_SESSION['userGroup'] == 2 && $_SESSION['domain'] == 'amagi')) {
	
		// Query to look up sales
		$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount, direct, discounteur, operatorid FROM sales WHERE 1 $timeLimit $user_limit ORDER by saletime DESC $limitVar";
	
	} else {
		
		// Query to look up sales
		$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount, direct, discounteur, operatorid FROM sales WHERE DATE(saletime) = DATE(NOW()) $user_limit ORDER by saletime DESC $limitVar";
		
	}

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
		
		
		
	// Create month-by-month split
	$findStartDate = "SELECT saletime FROM sales ORDER BY saletime ASC LIMIT 1";
	
	try
	{
		$startResult = $pdo3->prepare("$findStartDate");
		$startResult->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $startResult->fetch();
		$startDate = date('01-m-Y', strtotime($row['saletime']));
		$endDate = date('01-m-Y');
		$endDateShort = date('m-Y', strtotime($endDate));
		
		
		
	if ($endDateShort != $filterVar) {
		$optionList .= "<option value='$endDateShort'>$endDateShort</option>";
	}
	
	$genDateFull = date('01-m-Y', strtotime($endDate));
	$genDate = date('m-Y', strtotime($genDateFull));
	
		
		
	while (strtotime($genDateFull) > strtotime($startDate)) {
	
		
		$genDateFull = date('01-m-Y', strtotime("$genDateFull - 1 month"));
		$genDate = date('m-Y', strtotime($genDateFull));
		
		// Exclude option if already selected
		if ($genDate != $filterVar) {
			$optionList .= "<option value='$genDate'>$genDate</option>";
		}

	}

		
	
	$deleteSaleScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });	    
	    $( "#exceldatepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });	    
	    $( "#exceldatepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });	    
	  
	    $(document).ready(function() {
		    
			//$('#cloneTable').width($('#mainTable').width());
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					0: {
						sorter: "dates"
					},
					4: {
						sorter: "currency"
					},
					5: {
						sorter: "currency"
					},
					6: {
						sorter: "currency"
					},
					7: {
						sorter: "currency"
					},
					8: {
						sorter: "currency"
					},
					9: {
						sorter: "currency"
					},
					10: {
						sorter: "currency"
					}
				}
			}); 
			
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});

	
		function delete_sale(saleid) {
			if (confirm("{$lang['confirm-deletedispense']}")) {
						window.location = "uTil/delete-dispense.php?saleid=" + saleid;
						}
		}
EOD;
	pageStart($lang['title-dispenses'], NULL, $deleteSaleScript, "psales", "sales admin", $lang['global-dispensescaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
		
?>
<center>
<div id='filterbox'>
 <div id='mainboxheader'>
 <?php echo $lang['filter']; ?>
 </div>
 <div class='boxcontent'>
        <form action='' method='POST' style='margin-top: 3px; display: inline-block;'>
	     <select id='filter' name='filter' class='defaultinput' onchange='this.form.submit()' style='display: inline-block; width: 100px; height: 38px;'>
	      <?php echo $optionList; ?>
		 </select>
        </form>
         <span style="margin-top: 27px; font-weight: bold;">OR</span>
        <form action='' method='POST' style='display: inline-block;'>
		<?php
			if (isset($_POST['fromDate'])) {
				
				echo <<<EOD
				 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['fromDate']}" />
				 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		EOD;
				
			} else {
				
				echo <<<EOD
				 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="{$lang['from-date']}" />
				 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="{$lang['to-date']}" onchange='this.form.submit()' />
		EOD;

			}
		?>
		       <br>
     
		<div style='display: inline-block; text-align: left; float: left; padding-right: 32px;'>
			&nbsp;<strong><?php echo $lang['member-usergroup']; ?>:</strong><br /> <br /> 
			<?php  
				if($firstSelect == 'true'){
			 ?>
			<div class='fakeboxholder firstbox'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php if ($_SESSION['lang'] == 'en' || $_SESSION['lang'] == 'nl' || $_SESSION['lang'] == 'fr') { echo 'Administrator'; } else { echo 'Administrador'; } ?>
			  <input type="checkbox" name="cashBox[]" id="accept1" value='1' checked />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php if ($_SESSION['lang'] == 'en' || $_SESSION['lang'] == 'nl' || $_SESSION['lang'] == 'fr') { echo 'Staff'; } else { echo 'Trabajador'; } ?>
			  <input type="checkbox" name="cashBox[]" id="accept2" value='2' checked />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php if ($_SESSION['lang'] == 'en' || $_SESSION['lang'] == 'nl' || $_SESSION['lang'] == 'fr') { echo 'Volunteer'; } else { echo 'Voluntario'; } ?>
			  <input type="checkbox" name="cashBox[]" id="accept3" value='3' checked />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php echo $lang['all']; ?>
			  <input type="checkbox" name="cashBox[]" id="accept5" value='all' checked />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<?php }else{ ?>
			<div class='fakeboxholder firstbox'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php if ($_SESSION['lang'] == 'en' || $_SESSION['lang'] == 'nl' || $_SESSION['lang'] == 'fr') { echo 'Administrator'; } else { echo 'Administrador'; } ?>
			  <input type="checkbox" name="cashBox[]" id="accept1" value='1' <?php if(in_array(1, $selectedUserArr)){ echo "checked"; } ?> />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php if ($_SESSION['lang'] == 'en' || $_SESSION['lang'] == 'nl' || $_SESSION['lang'] == 'fr') { echo 'Staff'; } else { echo 'Trabajador'; } ?>
			  <input type="checkbox" name="cashBox[]" id="accept2" value='2' <?php if(in_array(2, $selectedUserArr)){ echo "checked"; } ?> />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php if ($_SESSION['lang'] == 'en' || $_SESSION['lang'] == 'nl' || $_SESSION['lang'] == 'fr') { echo 'Volunteer'; } else { echo 'Voluntario'; } ?>
			  <input type="checkbox" name="cashBox[]" id="accept3" value='3' <?php if(in_array(3, $selectedUserArr)){ echo "checked"; } ?> />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php echo $lang['all']; ?>
			  <input type="checkbox" name="cashBox[]" id="accept5" value='all' <?php if(in_array('all', $selectedUserArr)){ echo "checked"; } ?> />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<?php } ?>	
			 <input type="hidden" name="submitted" value="1">
			<button type="submit"  class='cta2' style='display: inline-block; width: 50px;'>OK</button>
	      
	        </div>
        </form>
        </div>
       </td>
      </tr>
     </table>
</div></center>
<br />

<br />
	
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th style='position: relative;'><a href="#"  id="openCOnfirm"   style='position: absolute; top: 0; left: 10px; margin-top: -66px;'><img src="images/excel-new.png" /></a><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th class='right'>Full price<?php echo $_SESSION['currencyoperator'] ?></th>
		<th class='right'>Discount price<?php echo $_SESSION['currencyoperator'] ?></th>
	    <th>Discount applied</th>
	    <th>Total g</th>
	    <th>Total u</th>
	    <th>Total full price <?php echo $_SESSION['currencyoperator'] ?></th>
	    <th>Checkout discount<?php //echo $lang['member-discount']; ?> %</th>
	    <th>Total discounted price <?php echo $_SESSION['currencyoperator'] ?></th>
	    <!-- <th><?php echo $lang['member-discount']; ?> �</th> -->
	    <th><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th><?php echo $lang['dispense-newcredit']; ?></th>
<?php if ($_SESSION['creditOrDirect'] != 1) { ?>
	    <th><?php echo $lang['paid-by']; ?></th>
<?php } ?>
	    <th><?php echo $lang['operator']; ?></th>
	    <th></th>
	    <th class='noExl'><?php echo $lang['global-delete']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	   $discount_type_name=array('1'=>'Individual','2'=>'General Medical','3'=>'Happy Hour','4'=>'Usergroup','5'=>'5','6'=>'Volume Discounts','7'=>'Gift');
	    $startIndex = 1;
		while ($sale = $result->fetch()) {

		$formattedDate = date("d-m-Y H:i:s", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$discount = number_format($sale['discount'],0);
		$direct = $sale['direct'];
		$discounteur = $sale['discounteur'];
		$operatorID = $sale['operatorid'];
		
		if ($operatorID == 0) {
			$operator = '';
		} else {
			$operator = getOperator($operatorID);
		}

		
		if ($direct == 3) {
			$paymentMethod = $lang['global-credit'];
		} else if ($direct == 2) {
			$paymentMethod = "<a href='uTil/paymentchange.php?type=card&saleid=$saleid''>{$lang['card']}</a>";
		} else if ($direct == 1) {
			$paymentMethod = "<a href='uTil/paymentchange.php?type=cash&saleid=$saleid''>{$lang['cash']}</a>";
		} else {
			$paymentMethod = '';
		}
		
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		try
		{
			$userResult = $pdo3->prepare("$userLookup");
			$userResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $userResult->fetch();
			$first_name = $row['first_name'];
			$memberno = $row['memberno'];
		
	if ($sale['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$saleid' /><div id='helpBox$saleid' class='helpBox'>{$sale['adminComment']}</div>
		                <script>
		                  	$('#comment$saleid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$saleid').css('display', 'inline-block');
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

			
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount, d.discountType, d.purchaseid FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
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
		
			// Make unpaid rows red:
			if ($amountpaid < $amount) {
				echo "<tr class='negative'>";
			} else {
				echo "<tr>";
			}
	   
		echo "
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
  	   
  	   		$o = 0;
	  	   	foreach ($totResult as $onesale) {	
	  	   		if ($o == 0) {
					echo "$formattedDate<br/>";
				} else {
					echo "<span class='white'>$formattedDate</span><br/>";
				}
				
				$o++;
			}
			echo "</td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
  	   
  	   		$p = 0;
	  	   	foreach ($totResult as $onesale) {	
	  	   		if ($p == 0) {
					echo "#$memberno - $first_name<br/>";
				} else {
					echo "<span class='white'>#$memberno - $first_name</span><br/>";
				}
				
				$p++;
			}
echo "
  	   </td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
  	   $ca =0 ;
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
					$resultCat = $pdo3->prepare("$categoryDetails");
					$resultCat->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $resultCat->fetch();
					$category = $row['name'];
			}
			$catex .= $category."\n";	
			$newIndex = $startIndex + $ca;
			echo  $category . "<br />";
			$ca++;
		}

            //echo $ca;
			//$startIndex =   $ca  + 1;
		echo "</td><td class='clickableRow' href='dispense.php?saleid={$saleid}'>";

		$na =0;
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
			$productResult = $pdo3->prepare("$selectProduct");
			$productResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $productResult->fetch();
		
		if ($row['breed2'] != '') {
			$name = $row['name'] . " x " . $row['breed2'];
		} else {
			$name = $row['name'];
		}
			$namIndex =  $startIndex + $na;
			echo $name . "<br />";

			$na++;
		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {	
			if ($onesale['category'] > 2) {
				
				// Query to look for category
				$categoryDetailsC = "SELECT name, type FROM categories WHERE id = {$onesale['category']}";
				
				try
				{
					$resultC = $pdo3->prepare("$categoryDetailsC");
					$resultC->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$rowC = $resultC->fetch();
					$category = $rowC['name'];
					$type = $rowC['type'];
			}

			if ($onesale['category'] < 3 || $type == 1) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
			}
		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		
		$totalFullPrice = 0;
	  	foreach ($totResult as $onesale) {	
			$prodJoins = "SELECT salesPrice FROM purchases WHERE purchaseid = {$onesale['purchaseid']}";
			try
			{
				$salePrice_result = $pdo3->prepare("$prodJoins");
				$salePrice_result->execute();
				$salePrice_row = $salePrice_result->fetch();
			}
			catch (PDOException $e)
			{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
			}

			$totalamt = $onesale['quantity'] * $salePrice_row['salesPrice'];
			$totalFullPrice = $totalFullPrice + $totalamt;
			echo number_format($totalamt,2) . " <span class='smallerfont'>{$_SESSION['currencyoperator']}</span><br />";	
		}
		$totalFullPrice = number_format($totalFullPrice,2);
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {	
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>{$_SESSION['currencyoperator']}</span><br />";
		}
		echo "</td><td class='clickableRow ' href='dispense.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {
				echo $discount_type_name[$onesale['discountType']].'<br />';
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
			
	if ($_SESSION['domain'] == 'headbanger') {
		if ($_SESSION['userGroup'] < 3) {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
		} else {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
		}
	} else if ($_SESSION['domain'] == 'drjoe') {
		
		if ($_SESSION['userGroup'] < 3) {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
		} else {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
		}
	
	} else if ($_SESSION['domain'] == 'crtfd' || $_SESSION['domain'] == 'skylounge' || $_SESSION['domain'] == 'naturalflo' || $_SESSION['domain'] == 'thegarden' || $_SESSION['domain'] == 'xcc') {
		
		if ($_SESSION['userGroup'] == 1) {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
		} else {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
		}
	
	} else {
		$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
	}
	
			if ($credit == NULL && $oldcredit == NULL) {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$totalFullPrice} {$_SESSION['currencyoperator']}</strong></td>";
				if($discount == '0.00' && $discounteur !='0.00'){
				echo "<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discounteur} {$_SESSION['currencyoperator']}</strong></td>";
				}else{
				echo "<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discount}%</strong></td>";
				}
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} {$_SESSION['currencyoperator']}</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>";
if ($_SESSION['creditOrDirect'] != 1) {
				echo "<td class='left'>$paymentMethod</td>";
}
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>$operator</td>
				<td class='centered'><span class='relativeitem'>$commentRead</span></td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
				
			} else {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$totalFullPrice} {$_SESSION['currencyoperator']}</strong></td>";
				if($discount == '0.00' && $discounteur !='0.00'){
				echo "<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discounteur} {$_SESSION['currencyoperator']}</strong></td>";
				}else{
				echo "<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discount}%</strong></td>";
				}
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} {$_SESSION['currencyoperator']}</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$credit} {$_SESSION['currencyoperator']}</td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$newcredit} {$_SESSION['currencyoperator']}</td>";
if ($_SESSION['creditOrDirect'] != 1) {
				echo "<td class='left'>$paymentMethod</td>";
}
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>$operator</td>
				<td class='centered'><span class='relativeitem'>$commentRead</span></td>
				$deleteOrNot</tr>
				";
			
			}
		if($ca > 1){
			$startIndex = $startIndex + $ca - 1;
		}
			$startIndex++;
	}
?>

	 </tbody>
	 </table>
	 

	 
	    <div  class="actionbox-npr" id = "dialog-3" title = "<?php echo $lang['title-dispenses']; ?>">
			
			<div class='boxcomtemt'>
				<p>Export excel between time ranges</p><br>
				<input type="text" id="exceldatepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="<?php echo $lang['from-date'] ?>" />
				 <input type="text" id="exceldatepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="<?php echo $lang['to-date'] ?>"/>
 				<button class='cta1' id="fullList">Ok</button>
 			
			</div>
		</div> 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
<?php

} else {
	
	if ($_SESSION['userGroup'] == 1 || ($_SESSION['userGroup'] == 2 && $_SESSION['domain'] == 'amagi')) {
			
		// Query to look up sales
		 $selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, realQuantity, units, adminComment, creditBefore, creditAfter, discount, direct, discounteur, operatorid FROM sales  WHERE 1 $timeLimit $user_limit ORDER by saletime DESC $limitVar";

	} else {
		
		// Query to look up sales
		$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, realQuantity, units, adminComment, creditBefore, creditAfter, discount, direct, discounteur, operatorid FROM sales WHERE DATE(saletime) = DATE(NOW()) $user_limit ORDER by saletime DESC";
		
	}
		try
		{
			$results = $pdo3->prepare("$selectSales");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
		
	// Create month-by-month split
	$findStartDate = "SELECT saletime FROM sales ORDER BY saletime ASC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$findStartDate");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$startDate = date('01-m-Y', strtotime($row['saletime']));
		$endDate = date('01-m-Y');
		$endDateShort = date('m-Y', strtotime($endDate));
		
		
		
	if ($endDateShort != $filterVar) {
		$optionList .= "<option value='$endDateShort'>$endDateShort</option>";
	}
	
	$genDateFull = date('01-m-Y', strtotime($endDate));
	$genDate = date('m-Y', strtotime($genDateFull));
	
		
		
	while (strtotime($genDateFull) > strtotime($startDate)) {
	
		
		$genDateFull = date('01-m-Y', strtotime("$genDateFull - 1 month"));
		$genDate = date('m-Y', strtotime($genDateFull));
		
		// Exclude option if already selected
		if ($genDate != $filterVar) {
			$optionList .= "<option value='$genDate'>$genDate</option>";
		}

	}

		
	
	$deleteSaleScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });	    
	    $( "#exceldatepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });	    
	    $( "#exceldatepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  
	    $(document).ready(function() {
		   
			$('#cloneTable').width($('#mainTable').width());
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					0: {
						sorter: "dates"
					},
					4: {
						sorter: "currency"
					},
					5: {
						sorter: "currency"
					},
					6: {
						sorter: "currency"
					},
					7: {
						sorter: "currency"
					},
					8: {
						sorter: "currency"
					},
					9: {
						sorter: "currency"
					},
					10: {
						sorter: "currency"
					}
				}
			}); 
			
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});

	
		function delete_sale(saleid) {
			if (confirm("{$lang['confirm-deletedispense']}")) {
						window.location = "uTil/delete-dispense.php?saleid=" + saleid;
						}
		}
EOD;
	pageStart($lang['title-dispenses'], NULL, $deleteSaleScript, "psales", "sales admin", $lang['global-dispensescaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
		
?>
<center>
<div id='filterbox'>
 <div id='mainboxheader'>
 <?php echo $lang['filter']; ?>
 </div>
 <div class='boxcontent'>
        <form action='' method='POST' style='margin-top: 3px; display: inline-block;'>
	     <select id='filter' name='filter' class='defaultinput' onchange='this.form.submit()' style='display: inline-block; width: 100px; height: 38px;'>
	      <?php echo $optionList; ?>
		 </select>
        </form>
         <span style="margin-top: 27px; font-weight: bold;">OR</span>
        <form action='' method='POST' style='display: inline-block;'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="{$lang['from-date']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="{$lang['to-date']}" onchange='this.form.submit()' />
EOD;

	}
?>
       <br>
     
		<div style='display: inline-block; text-align: left; float: left; padding-right: 32px;'>
			&nbsp;<strong><?php echo $lang['member-usergroup']; ?>:</strong><br /> <br /> 
			<?php  
				if($firstSelect == 'true'){
			 ?>
			<div class='fakeboxholder firstbox'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php if ($_SESSION['lang'] == 'en' || $_SESSION['lang'] == 'nl' || $_SESSION['lang'] == 'fr') { echo 'Administrator'; } else { echo 'Administrador'; } ?>
			  <input type="checkbox" name="cashBox[]" id="accept1" value='1' checked />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php if ($_SESSION['lang'] == 'en' || $_SESSION['lang'] == 'nl' || $_SESSION['lang'] == 'fr') { echo 'Staff'; } else { echo 'Trabajador'; } ?>
			  <input type="checkbox" name="cashBox[]" id="accept2" value='2' checked />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php if ($_SESSION['lang'] == 'en' || $_SESSION['lang'] == 'nl' || $_SESSION['lang'] == 'fr') { echo 'Volunteer'; } else { echo 'Voluntario'; } ?>
			  <input type="checkbox" name="cashBox[]" id="accept3" value='3' checked />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php echo $lang['all']; ?>
			  <input type="checkbox" name="cashBox[]" id="accept5" value='all' checked />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<?php }else{ ?>
			<div class='fakeboxholder firstbox'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php if ($_SESSION['lang'] == 'en' || $_SESSION['lang'] == 'nl' || $_SESSION['lang'] == 'fr') { echo 'Administrator'; } else { echo 'Administrador'; } ?>
			  <input type="checkbox" name="cashBox[]" id="accept1" value='1' <?php if(in_array(1, $selectedUserArr)){ echo "checked"; } ?> />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php if ($_SESSION['lang'] == 'en' || $_SESSION['lang'] == 'nl' || $_SESSION['lang'] == 'fr') { echo 'Staff'; } else { echo 'Trabajador'; } ?>
			  <input type="checkbox" name="cashBox[]" id="accept2" value='2' <?php if(in_array(2, $selectedUserArr)){ echo "checked"; } ?> />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php if ($_SESSION['lang'] == 'en' || $_SESSION['lang'] == 'nl' || $_SESSION['lang'] == 'fr') { echo 'Volunteer'; } else { echo 'Voluntario'; } ?>
			  <input type="checkbox" name="cashBox[]" id="accept3" value='3' <?php if(in_array(3, $selectedUserArr)){ echo "checked"; } ?> />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php echo $lang['all']; ?>
			  <input type="checkbox" name="cashBox[]" id="accept5" value='all' <?php if(in_array('all', $selectedUserArr)){ echo "checked"; } ?> />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<?php } ?>	
			
		<!-- <button type="submit">Submit</button> -->
		 <input type="hidden" name="submitted" value="1">
		 <button type="submit"  class='cta2' style='display: inline-block; width: 50px;'>OK</button>
	      
	        </div>
        </form>
        </div>
       </td>
      </tr>
     </table>
</div></center>
<br />
<br />
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th style='position: relative;'><a href="#" id="openCOnfirm"   style='position: absolute; top: 0; left: 10px; margin-top: -66px;'><img src="images/excel-new.png" /></a><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?> real</th>
	    <th class='right'>Full price<?php echo $_SESSION['currencyoperator'] ?></th>
		<th class='right'>Discount price<?php echo $_SESSION['currencyoperator'] ?></th>
	    <th>Discount applied</th>
	    <th>Total g</th>
	    <th>Real g</th>
	    <th>Total u</th>
	    <th>Total full price <?php echo $_SESSION['currencyoperator'] ?></th>
	    <th>Checkout discount<?php //echo $lang['member-discount']; ?> %</th>
	    <th>Total discounted price <?php echo $_SESSION['currencyoperator'] ?></th>
	    <!-- <th><?php echo $lang['member-discount']; ?> �</th> -->
	    <th><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th><?php echo $lang['dispense-newcredit']; ?></th>
<?php if ($_SESSION['creditOrDirect'] != 1) { ?>
	    <th><?php echo $lang['paid-by']; ?></th>
<?php } ?>
	    <th><?php echo $lang['operator']; ?></th>
	    <th></th>
	    <th class='noExl'><?php echo $lang['global-delete']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	   $discount_type_name=array('1'=>'Individual','2'=>'General Medical','3'=>'Happy Hour','4'=>'Usergroup','5'=>'5','6'=>'Volume Discounts','7'=>'Gift');
	   $disp = 0;
		while ($sale = $results->fetch()) {

		$formattedDate = date("d-m-Y H:i:s", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$realQuantity = $sale['realQuantity'];
		$units = $sale['units'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$discount = number_format($sale['discount'],0);
		$direct = $sale['direct'];
		$discounteur = $sale['discounteur'];
		$operatorID = $sale['operatorid'];
		
		if ($operatorID == 0) {
			$operator = '';
		} else {
			$operator = getOperator($operatorID);
		}
		
		if ($direct == 3) {
			$paymentMethod = $lang['global-credit'];
		} else if ($direct == 2) {
			$paymentMethod = "<a href='uTil/paymentchange.php?type=card&saleid=$saleid'>{$lang['card']}</a>";
		} else if ($direct == 1) {
			$paymentMethod = "<a href='uTil/paymentchange.php?type=cash&saleid=$saleid''>{$lang['cash']}</a>";
		} else {
			$paymentMethod = '';
		}
		
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
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
		
	if ($sale['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$saleid' /><div id='helpBox$saleid' class='helpBox'>{$sale['adminComment']}</div>
		                <script>
		                  	$('#comment$saleid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$saleid').css('display', 'inline-block');
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

			$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.realQuantity, d.amount, d.purchaseid ,d.discountType FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
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
	
		
			// Make unpaid rows red:
			if ($amountpaid < $amount) {
				echo "<tr class='negative'>";
			} else {
				echo "<tr>";
			}
	   
		echo "
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
  	   
  	   		$o = 0;
	  	   	foreach ($totResult as $onesale) {	
	  	   		
	  	   		if ($o == 0) {
					echo "$formattedDate<br/>";
				} else {
					echo "<span class='white'>$formattedDate</span><br/>";
				}
				
				$o++;
			}
			echo "</td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
  	   
  	   		$p = 0;
	  	   	foreach ($totResult as $onesale) {	
	  	   		if ($p == 0) {
					echo "#$memberno - $first_name<br/>";
				} else {
					echo "<span class='white'>#$memberno - $first_name</span><br/>";
				}
				
				$p++;
			}
echo "
  	   </td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
  	    $a = 0;
	  	   	foreach ($totResult as $onesale) {	
			if ($onesale['category'] == 1) {
				$category = $lang['global-flower'];
				$catarr[$disp][$a] = $category; 

			} else if ($onesale['category'] == 2) {
				$category = $lang['global-extract'];
				$catarr[$disp][$a] = $category;
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
				
			echo $category . "<br />";
			$catarr[$disp][$a] = $category;
			$a++;
		}
		$catex = implode(",", $catarr[$disp]);
		echo "</td><td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
		$b = 0;
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
			$namearr[$disp][$b] = $name;
			$b++;
		}
		$nameex = implode(",", $namearr[$disp]);

		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		$c = 0;
	  	   	foreach ($totResult as $onesale) {	
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
				$quantarr[$disp][$c] =  number_format($onesale['quantity'],2) . " g";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
				$quantarr[$disp][$p] =  number_format($onesale['quantity'],2) . " u";
			}
			$c++;
		}
		$quantex = implode(',', $quantarr[$disp]);
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		$d = 0;
	  	   	foreach ($totResult as $onesale) {	
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
				$realquantarr[$disp][$d] = number_format($onesale['realQuantity'],2) . " g";
			} else {
				echo number_format($onesale['realQuantity'],2) . " u<br />";
				$realquantarr[$disp][$d] = number_format($onesale['realQuantity'],2) . " u";
			}
			$d++;
		}
		$realquantex = implode(",", $realquantarr[$disp]);

		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		
		$totalFullPrice = 0;
	  	foreach ($totResult as $onesale) {	
			$prodJoins = "SELECT salesPrice FROM purchases WHERE purchaseid = {$onesale['purchaseid']}";
			try
			{
				$result = $pdo3->prepare("$prodJoins");
				$result->execute();
				$row = $result->fetch();
			}
			catch (PDOException $e)
			{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
			}
			$totalamt = $onesale['quantity'] * $row['salesPrice'];
			$totalFullPrice = $totalFullPrice + $totalamt;
			echo number_format($totalamt,2) . " <span class='smallerfont'>{$_SESSION['currencyoperator']}</span><br />";	
		}
		$totalFullPrice = number_format($totalFullPrice,2);
		// echo "</td>";

		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		$e=0;
	  	   	foreach ($totResult as $onesale) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>{$_SESSION['currencyoperator']}</span><br />";
			$amountarr[$disp][$e] = number_format($onesale['amount'],2) . " {$_SESSION['currencyoperator']}";
			$e++;
		}
		$amountex = implode(',', $amountarr[$disp]);
		// echo "</td>";

		echo "</td><td class='clickableRow ' href='dispense.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {
				echo $discount_type_name[$onesale['discountType']].'<br />';
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$realQuantity = number_format($realQuantity,2);
		$amount = number_format($amount,2);
		
	if ($_SESSION['domain'] == 'headbanger') {
		if ($_SESSION['userGroup'] < 3) {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
		} else {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
		}
	} else if ($_SESSION['domain'] == 'drjoe') {
		
		if ($_SESSION['userGroup'] < 3) {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
		} else {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
		}
	
	} else if ($_SESSION['domain'] == 'crtfd' || $_SESSION['domain'] == 'paradisebilbo' || $_SESSION['domain'] == 'thegarden') {
		
		if ($_SESSION['userGroup'] == 1) {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
		} else {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
		}
	
	} else {
		$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
	}

		
		
			if ($credit == NULL && $oldcredit == NULL) {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$realQuantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$totalFullPrice} {$_SESSION['currencyoperator']}</strong></td>";
				if($discount == '0.00' && $discounteur !='0.00'){
				echo "<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discounteur} {$_SESSION['currencyoperator']}</strong></td>";
				}else{
				echo "<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discount}%</strong></td>";
				}
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} {$_SESSION['currencyoperator']}</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>";
if ($_SESSION['creditOrDirect'] != 1) {
				echo "<td class='left'>$paymentMethod</td>";
}
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>$operator</td>
				<td class='noExl centered'><span class='relativeitem'>$commentRead</span></td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
				
			} else {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$realQuantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$totalFullPrice} {$_SESSION['currencyoperator']}</strong></td>";
				if($discount == '0.00' && $discounteur !='0.00'){
				echo "<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discounteur} {$_SESSION['currencyoperator']}</strong></td>";
				}else{
				echo "<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discount}%</strong></td>";
				}
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} {$_SESSION['currencyoperator']}</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$credit} {$_SESSION['currencyoperator']}</td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$newcredit} {$_SESSION['currencyoperator']}</td>";
if ($_SESSION['creditOrDirect'] != 1) {
				echo "<td class='left'>$paymentMethod</td>";
}
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>$operator</td>
				<td class='noExl centered'><span class='relativeitem'>$commentRead</span></td>
				$deleteOrNot</tr>
				";
			
			}

		

	}
?>

	 </tbody>
	 </table>
	    <div  class="actionbox-npr" id = "dialog-3" title = "<?php echo $lang['title-dispenses']; ?>">
			
			<div class='boxcomtemt'>
				<p>Export excel between time ranges</p><br>
				<input type="text" id="exceldatepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="<?php echo $lang['from-date'] ?>" />
				 <input type="text" id="exceldatepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="<?php echo $lang['to-date'] ?>"/>
 				<button class='cta1' id="fullList">Ok</button>
 			
			</div>
		</div> 
	 
<?php
}

displayFooter();
?>
<script type="text/javascript">
	var  realWeight ="<?php echo $_SESSION['realWeight'] ?>";
	$( "#dialog-3" ).dialog({
	    autoOpen: false, 
	    hide: "puff",
	    show : "slide",
	     position: {
	       my: "top top",
	       at: "top top"
	    }      
	 });
	 $( "#openCOnfirm" ).click(function() {
	    $( "#dialog-3" ).dialog( "open" );
	 });

	 $("#fullList").click(function(){
	    $("#load").show();
	    $( "#dialog-3" ).dialog( "close" );

	    var fromDate = $("#exceldatepicker").val();
	    var untilDate = $("#exceldatepicker2").val();
	    var url = 'dispenses-report.php?fromDate='+fromDate+'&untilDate='+untilDate+'&count=0&totalCount=0&realWeight='+realWeight;
	    window.open(url, "Dispenses Report","height=300,width=300,modal=yes,alwaysRaised=yes");
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);    
	 });

	/*function loadExcel(){
	    $("#load").show();
	    var filter = "<?php echo $_POST['filter'] ?>";
	    var fromDate = "<?php echo $_POST['fromDate'] ?>";
	    var untilDate = "<?php echo $_POST['untilDate'] ?>";
	    var submitted = "<?php echo $_POST['submitted'] ?>";
	    var cashBox = "<?php echo implode(',', $_POST['cashBox']); ?>";
	    window.location.href = "dispenses-report.php?filter="+filter+'&fromDate='+fromDate+'&untilDate='+untilDate+'&submitted='+submitted+'&cashBox='+cashBox; 
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);     
	 }*/
</script>	