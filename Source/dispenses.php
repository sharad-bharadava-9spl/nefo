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
			
			$timeLimit = "WHERE MONTH(saletime) = $month AND YEAR(saletime) = $year";
			
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
	if (isset($_POST['untilDate'])) {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "WHERE DATE(saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$limitVar = "";
			
	}
	
if ($_SESSION['realWeight'] == 0) {
	
	if ($_SESSION['userGroup'] == 1) {
	
		// Query to look up sales
		$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount, direct FROM sales $timeLimit ORDER by saletime DESC $limitVar";
	
	} else {
		
		// Query to look up sales
		$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount, direct FROM sales WHERE DATE(saletime) = DATE(NOW()) ORDER by saletime DESC $limitVar";
		
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
		    
		    
$("#xllink").click(function(){

	  $("#mainTable").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Retiradas",
	    filename: "Retiradas" //do not include extension

	  });

	});
		    
		    
		    
			//$('#cloneTable').width($('#mainTable').width());
			
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

<br />
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th style='position: relative;'><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});" style='position: absolute; top: 0; left: 10px; margin-top: -66px;'><img src="images/excel-new.png" /></a><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th class='right'>&euro;</th>
	    <th>Total g</th>
	    <th>Total u</th>
	    <th>Total &euro;</th>
	    <th><?php echo $lang['member-discount']; ?>?</th>
	    <th><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th><?php echo $lang['dispense-newcredit']; ?></th>
<?php if ($_SESSION['creditOrDirect'] != 1) { ?>
	    <th><?php echo $lang['paid-by']; ?></th>
<?php } ?>
	    <th></th>
	    <th class='noExl'><?php echo $lang['global-delete']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
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
	  	   	foreach ($totResult as $onesale) {	
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
				
			if ($credit == NULL && $oldcredit == NULL) {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
				<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discount}%</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>";
if ($_SESSION['creditOrDirect'] != 1) {
				echo "<td class='left'>$paymentMethod</td>";
}
				echo "
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
				
			} else {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
				<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discount}%</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$credit} &euro;</td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$newcredit} &euro;</td>";
if ($_SESSION['creditOrDirect'] != 1) {
				echo "<td class='left'>$paymentMethod</td>";
}
				echo "
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
			
			}
		


	}
?>

	 </tbody>
	 </table>
	 

	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 

	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
<?php

} else {
	
	if ($_SESSION['userGroup'] == 1) {
			
		// Query to look up sales
		$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, realQuantity, units, adminComment, creditBefore, creditAfter, discount, direct FROM sales $timeLimit ORDER by saletime DESC $limitVar";

	} else {
		
		// Query to look up sales
		$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, realQuantity, units, adminComment, creditBefore, creditAfter, discount, direct FROM sales WHERE DATE(saletime) = DATE(NOW()) ORDER by saletime DESC";
		
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
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  
	    $(document).ready(function() {
		    
		    
		    
$("#xllink").click(function(){

	  $("#mainTable").table2excel({
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
<br />
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?> real</th>
	    <th class='right'>&euro;</th>
	    <th>Total g</th>
	    <th>Real g</th>
	    <th>Total u</th>
	    <th>Total &euro;</th>
	    <th><?php echo $lang['member-discount']; ?>?</th>
	    <th><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th><?php echo $lang['dispense-newcredit']; ?></th>
<?php if ($_SESSION['creditOrDirect'] != 1) { ?>
	    <th><?php echo $lang['paid-by']; ?></th>
<?php } ?>
	    <th></th>
	    <th class='noExl'><?php echo $lang['global-delete']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  
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
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
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
			} else {
				echo number_format($onesale['realQuantity'],2) . " u<br />";
			}
		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {	
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$realQuantity = number_format($realQuantity,2);
		$amount = number_format($amount,2);
		
		
			if ($credit == NULL && $oldcredit == NULL) {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$realQuantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
				<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discount}%</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>";
if ($_SESSION['creditOrDirect'] != 1) {
				echo "<td class='left'>$paymentMethod</td>";
}
				echo "
				<td class='noExl centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
				
			} else {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$realQuantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
				<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discount}%</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$credit} &euro;</td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$newcredit} &euro;</td>";
if ($_SESSION['creditOrDirect'] != 1) {
				echo "<td class='left'>$paymentMethod</td>";
}
				echo "
				<td class='noExl centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
			
			}
		
		

	}
?>

	 </tbody>
	 </table>
	 

<?php

}

displayFooter();