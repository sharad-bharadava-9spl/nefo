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
			$optionList = "<option value='$filterVar'>{$lang['last']} 250</option>
			<option value='100'>{$lang['last']} 100</option>
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
		
		$optionList = "<option value=''>{$lang['filter']}</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
	
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "AND DATE(saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$limitVar = "";
			
	}
	
	if ($_SESSION['domain'] == 'thebulldog' || $_SESSION['domain'] == 'cloud' || $_SESSION['domain'] == 'ccstest') {
	
		
			// Query to look up sales
			$selectSales = "SELECT s.saleid, s.saletime, s.userid, s.amount, s.amountpaid, s.quantity, s.units, s.adminComment, s.creditBefore, s.creditAfter, s.discount, s.direct, s.puesto, s.dispensedfrom, s.paypal, s.fulfilled, d.time FROM sales s, delivery d WHERE 1 AND d.saleid = s.saleid AND (s.puesto = 11 OR s.puesto = 22) $timeLimit ORDER by d.time DESC $limitVar";
		
		
	} else {
	
		
			// Query to look up sales
			$selectSales = "SELECT s.saleid, s.saletime, s.userid, s.amount, s.amountpaid, s.quantity, s.units, s.adminComment, s.creditBefore, s.creditAfter, s.discount, s.direct, s.puesto, s.dispensedfrom, s.fulfilled, d.time FROM sales s, delivery d WHERE 1 AND d.saleid = s.saleid AND (s.puesto = 11 OR s.puesto = 22) $timeLimit ORDER by d.time DESC $limitVar";
		
		
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
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });	    
	  
	    $(document).ready(function() {
		    
		    
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			 //    var dateArray = s.split('-');
			 //    var timeArray = dateArray[2].split(':');
			 //    var lastField = (timeArray[0].replace(/\s/g, '')+timeArray[1].replace(/\s/g, ''));
			 //    //alert(dateArray[0] + dateArray[1] + lastField);
			 //    var newDate = myDate[1]+","+myDate[0]+","+lastField;
				// console.log(new Date(newDate).getTime());​
			 //    return ($.tablesorter.formatFloat(dateArray[0] + dateArray[1] + lastField));

			  	 var date,
				dateTimeParts = s.split(' '),
			    timeParts = dateTimeParts[1].split(':'),
			    dateParts = dateTimeParts[0].split('-');
				date = new Date(dateParts[2], parseInt(dateParts[1], 10) - 1, dateParts[0], timeParts[0], timeParts[1]);
				//return dateParts[2], parseInt(dateParts[1], 10) - 1, dateParts[0], timeParts[0], timeParts[1];
				return date.getTime();

			  },
			  type: 'numeric'

			});

			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				dateFormat: 'uk',
				headers: {
					/*0: {
						sorter: "dates2"
					},*/
					/*1: {
						sorter: "dates"
					},*/
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
		
		function delete_sale(saleid) {
			if (confirm("{$lang['confirm-deletedispense']}")) {
						window.location = "uTil/delete-dispense.php?preorder&saleid=" + saleid;
						}
		}
EOD;
	pageStart($lang['pre-orders'], NULL, $deleteSaleScript, "psales", "sales admin", $lang['pre-orders'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
		
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
        <form action='' method='POST' style='display: inline-block;'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <button type="submit"  class='cta2' style='display: inline-block; width: 50px;'>OK</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="{$lang['from-date']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="{$lang['to-date']}" onchange='this.form.submit()' />
		 <button type="submit"  class='cta2' style='display: inline-block; width: 50px;'>OK</button>
EOD;

	}
?>
        </form>
        </div>
       </td>
      </tr>
     </table>
</div></center>
<br />
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th class=""><?php if ($_SESSION['lang'] == 'es') { echo "Hora pedido"; } else { echo "Order time"; } ?></th>
	    <th><?php if ($_SESSION['lang'] == 'es') { echo "Hora deseada"; } else { echo "Pickup time"; } ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th class='right'><?php echo $_SESSION['currencyoperator'] ?></th>
	    <th>Total g</th>
	    <th>Total u</th>
	    <th>Total <?php echo $_SESSION['currencyoperator'] ?></th>
	    <th><?php echo $lang['member-discount']; ?>?</th>
	    <th><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th><?php echo $lang['dispense-newcredit']; ?></th>
<?php if ($_SESSION['creditOrDirect'] != 1) { ?>
	    <th><?php echo $lang['paid-by']; ?></th>
<?php } ?>
	    <th><?php if ($_SESSION['lang'] == 'es') { echo "Aceptado?"; } else { echo "Accepted?"; } ?></th>
	    <th><?php if ($_SESSION['lang'] == 'es') { echo "Cumplidos"; } else { echo "Fulfilled"; } ?></th>
<?php if ($_SESSION['domain'] == 'thebulldog' || $_SESSION['domain'] == 'cloud' || $_SESSION['domain'] == 'ccstest') { ?>
	    <th>Paypal?</th>
<?php } ?>
	    <th></th>
	    <th class='noExl'><?php echo $lang['global-delete']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($sale = $result->fetch()) {

		$formattedDate = date("d-m-Y H:i", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$discount = number_format($sale['discount'],0);
		$direct = $sale['direct'];
		$puesto = $sale['puesto'];
		$dispensedfrom = $sale['dispensedfrom'];
		$paypal = $sale['paypal'];
		$fulfilled = $sale['fulfilled'];
		
		if ($fulfilled == 0) {
			$accepted = "<a href='accept.php?saleid=$saleid&menu=no'>{$lang['global-no']}</a>";
		} else if ($fulfilled == 1) {
			$accepted = "<a href='accept.php?saleid=$saleid&menu=yes'><img src='images/complete.png' width='16' /></a>";
		}
		
		if ($paypal == 1) {
			$paypal = "<strong style='color: orange;'>{$lang['global-yes']}</strong>";
			$discount = "25";
		} else if ($paypal == 2) {
			$paypal = "<strong style='color: green;'>{$lang['global-yes']}</strong>";
			$discount = "25";
		} else if ($paypal == 9) {
			$paypal = "<strong style='color: red;'>{$lang['global-yes']}</strong>";
			$discount = "25";
		} else {
			$paypal = $lang['global-no'];
			$discount = "0";
		}
		
		$queryD = "SELECT time FROM delivery WHERE saleid = $saleid";
		try
		{
			$resultD = $pdo3->prepare("$queryD");
			$resultD->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowD = $resultD->fetch();
			$pickuptime = date("d-m-Y H:i", strtotime($rowD['time']));
			
		if ($dispensedfrom == 0) {
			$fulfilled = "<a href='fulfill.php?saleid=$saleid&menu=no'>{$lang['global-no']}</a>";
		} else if ($dispensedfrom == 1) {
			$fulfilled = "<a href='fulfill.php?saleid=$saleid&menu=yes'><img src='images/complete.png' width='16' /></a>";
		}
		
		if ($puesto == '11') {
			if ($_SESSION['lang'] == 'es') {
				$saletype = "Entrega";
			} else {
				$saletype = "Delivery";
			}
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

			
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
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
  	   <td class='clickableRow' href='accept.php?saleid=$saleid&menu=no'>";
  	   
  	   		$o = 0;
	  	   	foreach ($totResult as $onesale) {	
	  	   		if ($o == 0) {
					echo "$formattedDate";
				} /*else {
					echo "<span class='white'>$formattedDate</span><br/>";
				}*/
				
				$o++;
			}
		echo "
  	   </td><td class='clickableRow' href='accept.php?saleid=$saleid&menu=no'>";
  	   
  	   		$o = 0;
	  	   	foreach ($totResult as $onesale) {	
	  	   		if ($o == 0) {
					echo "$pickuptime";
				} /*else {
					echo "<span class='white'>$pickuptime</span><br/>";
				}*/
				
				$o++;
			}
			echo "</td><td class='clickableRow' href='accept.php?saleid=$saleid&menu=no'>$saletype</td>
  	   <td class='clickableRow' href='accept.php?saleid=$saleid&menu=no'>";
  	   
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
  	   <td class='clickableRow' href='accept.php?saleid=$saleid&menu=no'>";
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
				
			echo $category . "<br />";
		}
		echo "</td><td class='clickableRow' href='accept.php?saleid=$saleid&menu=no'>";
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


			echo $name . "<br />";
		}
		echo "</td><td class='clickableRow right' href='accept.php?saleid=$saleid&menu=no'>";
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
		echo "</td><td class='clickableRow right' href='accept.php?saleid=$saleid&menu=no'>";
	  	   	foreach ($totResult as $onesale) {	
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>{$_SESSION['currencyoperator']}</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
				
			if ($credit == NULL && $oldcredit == NULL) {
				
				echo "
				<td class='clickableRow right' href='accept.php?saleid=$saleid&menu=no'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='accept.php?saleid=$saleid&menu=no'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='accept.php?saleid=$saleid&menu=no'><strong>{$amount} {$_SESSION['currencyoperator']}</strong></td>
				<td class='clickableRow centered' href='accept.php?saleid=$saleid&menu=no'><strong>{$discount}%</strong></td>
				<td class='clickableRow right' href='accept.php?saleid=$saleid&menu=no'></td>
				<td class='clickableRow right' href='accept.php?saleid=$saleid&menu=no'></td>";
if ($_SESSION['creditOrDirect'] != 1) {
				echo "<td class='left'>$paymentMethod</td>";
}
				echo "
				<td class='noExl centered'>$accepted</td>
				<td class='noExl centered'>$fulfilled</td>
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
				
			} else {
				
				echo "
				<td class='clickableRow right' href='accept.php?saleid=$saleid&menu=no'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='accept.php?saleid=$saleid&menu=no'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='accept.php?saleid=$saleid&menu=no'><strong>{$amount} {$_SESSION['currencyoperator']}</strong></td>
				<td class='clickableRow centered' href='accept.php?saleid=$saleid&menu=no'><strong>{$discount}%</strong></td>
				<td class='clickableRow right' href='accept.php?saleid=$saleid&menu=no'>{$credit} {$_SESSION['currencyoperator']}</td>
				<td class='clickableRow right' href='accept.php?saleid=$saleid&menu=no'>{$newcredit} {$_SESSION['currencyoperator']}</td>";
if ($_SESSION['creditOrDirect'] != 1) {
				echo "<td class='left'>$paymentMethod</td>";
}

				echo "
				<td class='noExl centered'>$accepted</td>
				<td class='noExl centered'>$fulfilled</td>";
				
if ($_SESSION['domain'] == 'thebulldog' || $_SESSION['domain'] == 'cloud' || $_SESSION['domain'] == 'ccstest') {
	echo "<td class='noExl centered'>$paypal</td>";
}
				echo "
				<td class='centered'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
			
			}
		


	}
?>

	 </tbody>
	 </table>
	 

	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 

	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 