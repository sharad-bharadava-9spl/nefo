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
			
	}
	
	// Query to look up individual sales
	$selectSales = "SELECT saleid, saletime, amount, unitsTot, userid, creditBefore, creditAfter, direct, adminComment FROM b_sales $timeLimit ORDER by saletime DESC $limitVar";
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
	$findStartDate = "SELECT saletime FROM b_sales ORDER BY saletime ASC LIMIT 1";
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

$("#xllink").click(function(){

	  $("#mainTable").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Ventas",
	    filename: "Ventas" //do not include extension

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
					}
				}
			}); 
			
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
function delete_sale(saleid) {
	if (confirm("{$lang['confirm-deletedispense']}")) {
				window.location = "uTil/delete-sale.php?saleid=" + saleid;
				}
}
EOD;

	pageStart("Sales", NULL, $deleteSaleScript, "psales", "sales admin", "SALES", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
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

<br />	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th style='position: relative;'><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});" style='position: absolute; top: 0; left: 10px; margin-top: -66px;'><img src="images/excel-new.png" /></a><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th class='right'><?php echo $lang['global-quantity']; ?></th>
	    <th class='right'>&euro;</th>
	    <th>Total u</th>
	    <th class='right'>Total &euro;</th>
	    <th class='right'><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th class='right'><?php echo $lang['dispense-newcredit']; ?></th>
<?php if ($_SESSION['creditOrDirect'] != 1) { ?>
	    <th><?php echo $lang['paid-by']; ?></th>
<?php } ?>
	    <th class='noExl'></th>
	    <th class='noExl'><?php echo $lang['global-delete']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
<?php
	  
		while ($sale = $results->fetch()) {
	
		$formattedDate = date("d-m-Y H:i", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$units = $sale['unitsTot'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$direct = $sale['direct'];
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
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	   
		echo "
  	   <td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>#{$memberno} - {$first_name}</td>
  	   <td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
		while ($onesale = $onesaleResult->fetch()) {
			
			$selectCatName = "SELECT name from b_categories where id = {$onesale['category']}";
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
		echo "</td><td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
		while ($onesale = $onesaleResult2->fetch()) {
			
			// Look up service name
			$selectServName = "SELECT name from b_products where productid = {$onesale['productid']}";
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
		echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
		while ($onesale = $onesaleResult3->fetch()) {
				echo number_format($onesale['quantity'],0) . " u<br />";
		}
		echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
		while ($onesale = $onesaleResult4->fetch()) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		$units = number_format($units,0);
		
			
			if ($credit == NULL && $oldcredit == NULL) {
				echo "
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'></td>";
if ($_SESSION['creditOrDirect'] != 1) {
				echo "<td class='left'>$paymentMethod</td>";
}
				echo "<td class='centered relative'>$commentRead</td>";

				echo "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
				
			} else {
				
				echo "
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>{$credit} &euro;</td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>{$newcredit} &euro;</td>";
if ($_SESSION['creditOrDirect'] != 1) {
				echo "<td class='left'>$paymentMethod</td>";
}
				echo "<td class='centered relative'>$commentRead</td>";
				echo "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
			
			}

	}
?>

	 </tbody>
	 </table>
	 
<?php  displayFooter(); ?>
