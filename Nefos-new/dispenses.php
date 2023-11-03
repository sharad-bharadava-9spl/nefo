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
		
		$optionList = "<option value=''>{$lang['filter']}</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
	
	
if ($_SESSION['realWeight'] == 0) {
	
	// Query to look up sales
	$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount FROM sales $timeLimit ORDER by saletime DESC $limitVar";

	$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		
	// Create month-by-month split
	$findStartDate = "SELECT saletime FROM sales ORDER BY saletime ASC LIMIT 1";
	
	$startResult = mysql_query($findStartDate)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

	$row = mysql_fetch_array($startResult);
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

	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
        <form action='' method='POST'>
	     <select id='filter' name='filter' onchange='this.form.submit()'>
	      <?php echo $optionList; ?>
		 </select>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
        </form>
       </td>
      </tr>
     </table>
<br />
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th class='right'>&euro;</th>
	    <th>Total g</th>
	    <th>Total u</th>
	    <th>Discount</th>
	    <th>Total &euro;</th>
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
	    <th><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th><?php echo $lang['dispense-newcredit']; ?></th>
<?php } ?>
	    <th></th>
	    <th class='noExl'><?php echo $lang['global-delete']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
while ($sale = mysql_fetch_array($result)) {

		$formattedDate = date("d-m-Y H:i:s", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$discount = $sale['discount'];
		
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		$userResult = mysql_query($userLookup)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
			
	    $row = mysql_fetch_array($userResult);
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
		$onesaleResult = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult2 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult3 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult4 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult5 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult6 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult7 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		
			// Make unpaid rows red:
			if ($amountpaid < $amount) {
				echo "<tr class='negative'>";
			} else {
				echo "<tr>";
			}
	   
		echo "
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
  	   
  	   		$o = 0;
  	   		while ($onesale = mysql_fetch_array($onesaleResult6)) {
	  	   		
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
  	   		while ($onesale = mysql_fetch_array($onesaleResult7)) {
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
		while ($onesale = mysql_fetch_array($onesaleResult)) {
			if ($onesale['category'] == 1) {
				$category = $lang['global-flower'];
			} else if ($onesale['category'] == 2) {
				$category = $lang['global-extract'];
			} else {
				
				// Query to look for category
				$categoryDetails = "SELECT name FROM categories WHERE id = {$onesale['category']}";
				
				$resultCat = mysql_query($categoryDetails)
					or handleError($lang['error-errorloadingflower'],"Error loading flower: " . mysql_error());
				
				$row = mysql_fetch_array($resultCat);
					$category = $row['name'];
			}
				
			echo $category . "<br />";
		}
		echo "</td><td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult2)) {
			
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
		$productResult = mysql_query($selectProduct)
			or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
			
	    $row = mysql_fetch_array($productResult);
		
		if ($row['breed2'] != '') {
			$name = $row['name'] . " x " . $row['breed2'];
		} else {
			$name = $row['name'];
		}


			echo $name . "<br />";
		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult3)) {
			if ($onesale['category'] < 3) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
			}
		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult4)) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		$discount = number_format($discount,0);
		
		if ($_SESSION['creditOrDirect'] == 1) {
		
			if ($credit == NULL && $oldcredit == NULL) {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$discount} %</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
				
			} else {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$discount} %</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$credit} &euro;</td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$newcredit} &euro;</td>
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
			
			}
		
		} else {
			
			if ($credit == NULL && $oldcredit == NULL) {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$discount} %</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
				
			} else {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$discount} %</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
			
			}
			
		}

	}
?>

	 </tbody>
	 </table>
	 

	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 

	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
<?php

} else {
	
		
	// Query to look up sales
	$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, realQuantity, units, adminComment, creditBefore, creditAfter FROM sales $timeLimit ORDER by saletime DESC $limitVar";

	$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		
	// Create month-by-month split
	$findStartDate = "SELECT saletime FROM sales ORDER BY saletime ASC LIMIT 1";
	
	$startResult = mysql_query($findStartDate)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

	$row = mysql_fetch_array($startResult);
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

	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
        <form action='' method='POST'>
	     <select id='filter' name='filter' onchange='this.form.submit()'>
	      <?php echo $optionList; ?>
		 </select>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
        </form>
       </td>
      </tr>
     </table>
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
	    <th>Total g real</th>
	    <th>Total u</th>
	    <th>Discount</th>
	    <th>Total &euro;</th>
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
	    <th><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th><?php echo $lang['dispense-newcredit']; ?></th>
<?php } ?>
	    <th></th>
	    <th class='noExl'><?php echo $lang['global-delete']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
while ($sale = mysql_fetch_array($result)) {

		$formattedDate = date("d-m-Y H:i:s", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$realQuantity = $sale['realQuantity'];
		$units = $sale['units'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		$userResult = mysql_query($userLookup)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
			
	    $row = mysql_fetch_array($userResult);
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
			
		$onesaleResult = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult2 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult3 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult4 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult5 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult6 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult7 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		
			// Make unpaid rows red:
			if ($amountpaid < $amount) {
				echo "<tr class='negative'>";
			} else {
				echo "<tr>";
			}
	   
		echo "
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
  	   
  	   		$o = 0;
  	   		while ($onesale = mysql_fetch_array($onesaleResult6)) {
	  	   		
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
  	   		while ($onesale = mysql_fetch_array($onesaleResult7)) {
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
		while ($onesale = mysql_fetch_array($onesaleResult)) {
			if ($onesale['category'] == 1) {
				$category = $lang['global-flower'];
			} else if ($onesale['category'] == 2) {
				$category = $lang['global-extract'];
			} else {
				
				// Query to look for category
				$categoryDetails = "SELECT name FROM categories WHERE id = {$onesale['category']}";
				
				$resultCat = mysql_query($categoryDetails)
					or handleError($lang['error-errorloadingflower'],"Error loading flower: " . mysql_error());
				
				$row = mysql_fetch_array($resultCat);
					$category = $row['name'];
			}
				
			echo $category . "<br />";
		}
		echo "</td><td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult2)) {
			
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
		$productResult = mysql_query($selectProduct)
			or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
			
	    $row = mysql_fetch_array($productResult);
		
		if ($row['breed2'] != '') {
			$name = $row['name'] . " x " . $row['breed2'];
		} else {
			$name = $row['name'];
		}


			echo $name . "<br />";
		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult3)) {
			if ($onesale['category'] < 3) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
			}
		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult5)) {
			if ($onesale['category'] < 3) {
				echo number_format($onesale['realQuantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['realQuantity'],2) . " u<br />";
			}
		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult4)) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$realQuantity = number_format($realQuantity,2);
		$amount = number_format($amount,2);
		
		if ($_SESSION['creditOrDirect'] == 1) {
		
			if ($credit == NULL && $oldcredit == NULL) {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$realQuantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
				
			} else {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$realQuantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$credit} &euro;</td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$newcredit} &euro;</td>
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
			
			}
		
		} else {
			
			if ($credit == NULL && $oldcredit == NULL) {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$realQuantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
				
			} else {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$realQuantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
			
			}
			
		}

	}
?>

	 </tbody>
	 </table>
	 

<?php

}

displayFooter();