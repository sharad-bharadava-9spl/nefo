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
	
	
	
	// Query to look up sales
	$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, userConfirmed, fulfilled, paymentoption, customer, paid, discount, invoiced, delivered, comments, intcomment FROM sales WHERE userConfirmed = 1 $timeLimit ORDER by saletime DESC $limitVar";
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
	
	// Query to look up sales
	$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, userConfirmed, fulfilled, paymentoption, customer, paid, discount, invoiced, delivered, comments, intcomment FROM sales WHERE userConfirmed = 1 $timeLimit ORDER by saletime ASC $limitVar";
		try
		{
			$resultsy = $pdo3->prepare("$selectSales");
			$resultsy->execute();
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
	    $(document).ready(function() {
		    
		    
$("#xllink").click(function(){

	  $("#mainTable2").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Retiradas",
	    filename: "Retiradas" //do not include extension

	  });

	});
		    
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
			
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
	pageStart("ORDERS", NULL, $deleteSaleScript, "psales", "sales admin", "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
        <form action='' method='POST'>
	     <select id='filter' name='filter' onchange='this.form.submit()'>
	      <?php echo $optionList; ?>
		 </select>
         <a href="#" id="xllink" onClick="$('#mainTable2').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
        </form>
       </td>
      </tr>
     </table>
<br />
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th>#</th>
	    <th>Club</th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th class='centered'>Unit price (w/o IVA)</th>
	    <th>Units</th>
	    <th class='centered'>Discount</th>
	    <th>Total u</th>
	    <th>Total &euro; (w/o IVA)</th>
	    <th>Payment</th>
	    <th>Paid?</th>
	    <th>Shipped?</th>
	    <th>Delivered?</th>
	    <th>Invoiced?</th>
	    <th></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
		while ($sale = $results->fetch()) {
			
			$totAmt = 0;
	
		$formattedDate = date("d-m-Y H:i", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$fulfilled = $sale['fulfilled'];
		$delivered = $sale['delivered'];
		$drivernumber = $sale['drivernumber'];
		$paymentoption = $sale['paymentoption'];
		$customer = $sale['customer'];
		$paid = $sale['paid'];
		$discount = $sale['discount'];
		$invoiced = $sale['invoiced'];
		$delivered = $sale['delivered'];
		$comments = $sale['comments'];
		
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
		
		$query = "SELECT shortName FROM customers WHERE number = '$custnumber'";
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

			if ($paid == 1) {
				$inMenu = "<img src='images/complete.png' width='16' />";
				$inMenuOpr = "SI";
			} else {
				$inMenu = $lang['global-no'];
				$inMenuOpr = $lang['global-no'];
			}
			if ($invoiced == 1) {
				$inInvoiced = "<img src='images/complete.png' width='16' />";
				$inInvoicedOpr = "SI";
			} else {
				$inInvoiced = $lang['global-no'];
				$inInvoicedOpr = $lang['global-no'];
			}
			if ($delivered == 1) {
				$indelivered = "<img src='images/complete.png' width='16' />";
				$indeliveredOpr = "SI";
			} else {
				$indelivered = $lang['global-no'];
				$indeliveredOpr = $lang['global-no'];
			}
	
		if ($paymentoption == 1) {
			$pay = 'Tarjeta';
		} else if ($paymentoption == 2) {
			$pay = 'PayPal';
		} else if ($paymentoption == 3) {
			$pay = 'Reembolso';
		} else if ($paymentoption == 4) {
			$pay = 'Transferencia';
		} else  {
			$pay = '';
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
		
	if ($sale['intcomment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='commentx$saleid' /><div id='helpBoxx$saleid' class='helpBox'>{$sale['intcomment']}</div>
		                <script>
		                  	$('#commentx$saleid').on({
						 		'mouseover' : function() {
								 	$('#helpBoxx$saleid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBoxx$saleid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}
	if ($sale['adminComment'] != '') {
		
		$commentRead2 = "
		                <img src='images/truck.png' id='comment$saleid' /><div id='helpBox$saleid' class='helpBox'>Deliver to: {$sale['adminComment']}<br /><br />Customer comment: $comments</div>
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
		
		$commentRead2 = "";
		
	}
	
	if ($fulfilled == 0) {
		
		$clubApproved = "No";
		
	} else if ($fulfilled == 1) {
		
		$clubApproved = "<img src='images/complete.png' width='16' alt='Approved' />";
		
	} else {
		
		$clubApproved = "<img src='images/delete.png' width='16' alt='Rejected!' />";
				
	}
	
	if ($invoiced == 0) {
		
		$clubinvoiced = "No";
		
	} else if ($invoiced == 1) {
		
		$clubinvoiced = "<img src='images/complete.png' width='16' alt='Approved' />";
		
	} else {
		
		$clubinvoiced = "<img src='images/delete.png' width='16' alt='Rejected!' />";
				
	}

	
	if ($delivered == 0) {
		
		$deliveredImg = "<img src='images/awaiting.png' width='16' alt='Awaiting action' />";
		
	} else if ($delivered == 1) {
		
		$deliveredImg = "<img src='images/complete.png' width='16' alt='Approved' />";
				
	} else {
		
		$deliveredImg = "<img src='images/delete.png' width='16' alt='Rejected!' />";
		
	}

			
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount, d.purchaseid FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
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
		
		
		echo "<tr>
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
  	   
  	   		$o = 0;
	  	   	foreach ($totResult as $onesale) {
	  	   		
	  	   		if ($o == 0) {
					echo "$custnumber<br/>";
				} else {
					echo "<span class='white'>$custnumber</span><br/>";
				}
				
				$o++;
			}
			echo "</td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
  	   
  	   		$o = 0;
	  	   	foreach ($totResult as $onesale) {
	  	   		
	  	   		if ($o == 0) {
					echo "$shortName<br/>";
				} else {
					echo "<span class='white'>$shortName</span><br/>";
				}
				
				$o++;
			}
			echo "</td>

  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
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
				
			echo $category . "<br />";
		}
		echo "</td><td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
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
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {	
			echo number_format($onesale['amount'] / $onesale['quantity'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {
			if ($onesale['category'] < 3) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
			}
		}
		
		
		
		
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		
	  	   	foreach ($totResult as $onesale) {
		  	   	
		  	   	if ($discount > 0) {
			  	   	if ($onesale['purchaseid'] == 12 || $onesale['purchaseid'] == 13 || $onesale['purchaseid'] == 14 || $onesale['purchaseid'] == 15 || $onesale['purchaseid'] == 16 || $onesale['purchaseid'] == 27 || $onesale['purchaseid'] == 25 || $onesale['purchaseid'] == 26) {
			  	   	
					echo number_format($discount,0) . " <span class='smallerfont'>%</span><br />";
					
				} else {
					
					echo "<br />";
					
				}
				
			}
		}
		
		
	  	   	foreach ($totResult as $onesale) {
			  	   	if ($onesale['purchaseid'] == 12 || $onesale['purchaseid'] == 13 || $onesale['purchaseid'] == 14 || $onesale['purchaseid'] == 15 || $onesale['purchaseid'] == 16 || $onesale['purchaseid'] == 27 || $onesale['purchaseid'] == 25 || $onesale['purchaseid'] == 26) {
			  	   	
					$totAmt = $totAmt + ($onesale['amount'] * (1 - ($discount / 100)));
					
				} else {
					
					$totAmt = $totAmt + $onesale['amount'];
					
				}
		}
		
		
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		

			
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$totAmt} &euro;</strong></td>
				<td class='clickableRow centered relative' href='dispense.php?saleid={$saleid}'>$pay</td>
				<td class='centered relative'><a href='uTil/menuchange.php?saleid=$saleid&menu=$inMenuOpr'>$inMenu</a></td>
				<td class='clickableRow centered relative' href='dispense.php?saleid={$saleid}'>$clubApproved</td>
				<td class='centered relative'><a href='uTil/invchange.php?saleid=$saleid&menu=$inInvoicedOpr'>$inInvoiced</a></td>
				<td class='centered relative'><a href='uTil/delchange.php?saleid=$saleid&menu=$indeliveredOpr'>$indelivered</a></td>
				<td class='clickableRow centered relative' href='dispense.php?saleid={$saleid}'>$commentRead</td>
				<td class='clickableRow centered relative' href='dispense.php?saleid={$saleid}'>$commentRead2</td>
				</tr>
				";

	}
?>

	 </tbody>
	 </table>
	 
	 <table class='default' id='mainTable2' style='display: none;'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th>#</th>
	    <th>Club</th>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th class='centered'>Unit price (w/o IVA)</th>
	    <th>Units</th>
	    <th>Total &euro; (w/o IVA)</th>
	    <th class='centered'>Discount</th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th class='noExl'></th>
	   </tr>
	  </thead>
	  <tbody>
	  <?php
		while ($sale = $resultsy->fetch()) {
			
			$totAmt = 0;
	
		$formattedDate = date("d-m-Y", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$fulfilled = $sale['fulfilled'];
		$delivered = $sale['delivered'];
		$drivernumber = $sale['drivernumber'];
		$paymentoption = $sale['paymentoption'];
		$customer = $sale['customer'];
		$paid = $sale['paid'];
		$discount = $sale['discount'];
		$invoiced = $sale['invoiced'];
		$delivered = $sale['delivered'];
		
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
		
		$query = "SELECT shortName FROM customers WHERE number = '$custnumber'";
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

			if ($paid == 1) {
				$inMenu = "<img src='images/complete.png' width='16' />";
				$inMenuOpr = "SI";
			} else {
				$inMenu = $lang['global-no'];
				$inMenuOpr = $lang['global-no'];
			}
			if ($invoiced == 1) {
				$inInvoiced = "<img src='images/complete.png' width='16' />";
				$inInvoicedOpr = "SI";
			} else {
				$inInvoiced = $lang['global-no'];
				$inInvoicedOpr = $lang['global-no'];
			}
			if ($delivered == 1) {
				$indelivered = "<img src='images/complete.png' width='16' />";
				$indeliveredOpr = "SI";
			} else {
				$indelivered = $lang['global-no'];
				$indeliveredOpr = $lang['global-no'];
			}
	
		if ($paymentoption == 1) {
			$pay = 'Tarjeta';
		} else if ($paymentoption == 2) {
			$pay = 'PayPal';
		} else if ($paymentoption == 3) {
			$pay = 'Reembolso';
		} else if ($paymentoption == 4) {
			$pay = 'Transferencia';
		} else  {
			$pay = '';
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
	
	if ($fulfilled == 0) {
		
		$clubApproved = "No";
		
	} else if ($fulfilled == 1) {
		
		$clubApproved = "<img src='images/complete.png' width='16' alt='Approved' />";
		
	} else {
		
		$clubApproved = "<img src='images/delete.png' width='16' alt='Rejected!' />";
				
	}
	
	if ($invoiced == 0) {
		
		$clubinvoiced = "No";
		
	} else if ($invoiced == 1) {
		
		$clubinvoiced = "<img src='images/complete.png' width='16' alt='Approved' />";
		
	} else {
		
		$clubinvoiced = "<img src='images/delete.png' width='16' alt='Rejected!' />";
				
	}

	
	if ($delivered == 0) {
		
		$deliveredImg = "<img src='images/awaiting.png' width='16' alt='Awaiting action' />";
		
	} else if ($delivered == 1) {
		
		$deliveredImg = "<img src='images/complete.png' width='16' alt='Approved' />";
				
	} else {
		
		$deliveredImg = "<img src='images/delete.png' width='16' alt='Rejected!' />";
		
	}

			
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount, d.purchaseid FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
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
		
		echo "<tr>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
  	   
  	   		$o = 0;
	  	   	foreach ($totResult as $onesale) {
	  	   		
	  	   		if ($o == 0) {
					echo "$custnumber<br/>";
				} else {
					echo "<span class='white'>$custnumber</span><br/>";
				}
				
				$o++;
			}
			echo "</td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
  	   
  	   		$o = 0;
	  	   	foreach ($totResult as $onesale) {
	  	   		
	  	   		if ($o == 0) {
					echo "$shortName<br/>";
				} else {
					echo "<span class='white'>$shortName</span><br/>";
				}
				
				$o++;
			}
			echo "</td>
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
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {	
			echo number_format($onesale['amount'] / $onesale['quantity'],2) . "<br />";
		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {
			if ($onesale['category'] < 3) {
				echo number_format($onesale['quantity'],2) . "<br />";
			} else {
				echo number_format($onesale['quantity'],2) . "<br />";
			}
		}
		
	  	   	foreach ($totResult as $onesale) {
			  	   	if ($onesale['purchaseid'] == 12 || $onesale['purchaseid'] == 13 || $onesale['purchaseid'] == 14 || $onesale['purchaseid'] == 15 || $onesale['purchaseid'] == 16 || $onesale['purchaseid'] == 27 || $onesale['purchaseid'] == 25 || $onesale['purchaseid'] == 26) {
			  	   	
					$totAmt = $totAmt + ($onesale['amount'] * (1 - ($discount / 100)));
					
				} else {
					
					$totAmt = $totAmt + $onesale['amount'];
					
				}
		}
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		
		echo "</td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$totAmt}</strong></td>

<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		
	  	   	foreach ($totResult as $onesale) {
		  	   	
		  	   	if ($discount > 0) {
			  	   	if ($onesale['purchaseid'] == 12 || $onesale['purchaseid'] == 13 || $onesale['purchaseid'] == 14 || $onesale['purchaseid'] == 15 || $onesale['purchaseid'] == 16 || $onesale['purchaseid'] == 27 || $onesale['purchaseid'] == 25 || $onesale['purchaseid'] == 26) {
			  	   	
					echo number_format($discount,0) . " <span class='smallerfont'>%</span><br />";
					
				} else {
					
					echo "<br />";
					
				}
				
			}
		}
		
		
		
		echo "</td>

  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
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
				
			echo $category . "<br />";
		}
		echo "</td><td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
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
		
		
		
		
		
		echo "</td>
		
		

			
				<td class='clickableRow centered relative noExl' href='dispense.php?saleid={$saleid}'>$commentRead</td>
				</tr>
				";

	}
?>

	 </tbody>
	 </table>
	 
	 
	 <?php



displayFooter();
