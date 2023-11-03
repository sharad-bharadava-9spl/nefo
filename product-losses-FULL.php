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
	if (isset($_POST['untilDate'])) {
		
		$firstLoad = 'false';
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "AND DATE(saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
			
	} else {
		
		$firstLoad = 'true';
		
		$nowDate = date("d-m-Y");
		
		$timeLimit = "AND DATE(saletime) = DATE(NOW())";
		
	}

	
	// Go through each saledetail in the period
	// Look up purchaseID's price
	// Quantity * purchase price = dispPrice vs amount
	// Add all dispPrice
	// Add all amount
	// Find difference
	
	// Gifts: quantity != 0 AND amount == 0 (to avoid blank dispenses)
	
	// Query to look up sales
	$selectSales = "SELECT s.saleid, s.saletime, d.purchaseid, d.quantity, d.amount, d.purchaseid FROM sales s, salesdetails d WHERE s.saleid = d.saleid $timeLimit ORDER by saletime ASC";
		
	$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$selectSalesB = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount, direct FROM sales WHERE 1 $timeLimit ORDER by saletime ASC";
	

	$resultB = mysql_query($selectSalesB)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

		
	while ($sale = mysql_fetch_array($result)) {
		
		$formattedDate = date("d-m-Y H:i:s", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$quantity = $sale['quantity'];
		$amount = $sale['amount'];
		$purchaseid = $sale['purchaseid'];
		
		$selectPrice = "SELECT salesPrice FROM purchases WHERE purchaseid = $purchaseid";
		
		$priceResult = mysql_query($selectPrice)
			or handleError($lang['error-loadsales'] . mysql_error(),"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($priceResult);
			$salesPrice = $row['salesPrice'];
			
		$fullPrice = round($salesPrice * $quantity,2);
		
		$diff = $fullPrice - $amount;
		
		// Normal
		if ($amount == $fullPrice && $amount != 0) {
			
			$normalDisp = $amount;
			$normalDispTOT = $normalDispTOT + $normalDisp;
			
		// Gifted
		} else if ($quantity > 0 && $amount == 0) {
			
			$giftedDisp = $fullPrice;
			$giftedDispTOT = $giftedDispTOT + $giftedDisp;
			
			$giftedLost = $giftedLost + $diff;
			$lostTOT = $lostTOT + $diff;
			
		// Discounted
		} else {
			
			$reducedDisp = $amount;
			$reducedDispTOT = $reducedDispTOT + $reducedDisp;
			
			$reducedLost = $reducedLost + $diff;
			$lostTOT = $lostTOT + $diff;
			
		}
		
		
		/*
		$fullPriceTotal = $fullPriceTotal + $fullPrice;
		$amountTotal = $amountTotal + $amount;
		$diffTotal = $diffTotal + $diff;
		
		echo "saleid: $saleid<br />";
		echo "amount: $amount<br />";
		echo "fullPrice: $fullPrice<br />";
		echo "difference: $diff<br />";
		echo "normalDispTOT: $normalDispTOT<br />";
		echo "reducedDispTOT: $reducedDispTOT<br />";
		echo "lostTOT: $lostTOT<br /><br />";
		*/
		
	}
	
	$deleteDonationScript = <<<EOD
	
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
			
EOD;

	$deleteDonationScript .= <<<EOD

		
			
		});
		
var tablesToExcel = (function () {
    var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets>'
    , templateend = '</x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head>'
    , body = '<body>'
    , tablevar = '<table>{table'
    , tablevarend = '}</table>'
    , bodyend = '</body></html>'
    , worksheet = '<x:ExcelWorksheet><x:Name>'
    , worksheetend = '</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>'
    , worksheetvar = '{worksheet'
    , worksheetvarend = '}'
    , base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))) }
    , format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }) }
    , wstemplate = ''
    , tabletemplate = '';

    return function (table, name, filename) {
        var tables = table;

        for (var i = 0; i < tables.length; ++i) {
            wstemplate += worksheet + worksheetvar + i + worksheetvarend + worksheetend;
            tabletemplate += tablevar + i + tablevarend;
        }

        var allTemplate = template + wstemplate + templateend;
        var allWorksheet = body + tabletemplate + bodyend;
        var allOfIt = allTemplate + allWorksheet;

        var ctx = {};
        for (var j = 0; j < tables.length; ++j) {
            ctx['worksheet' + j] = name[j];
        }

        for (var k = 0; k < tables.length; ++k) {
            var exceltable;
            if (!tables[k].nodeType) exceltable = document.getElementById(tables[k]);
            ctx['table' + k] = exceltable.innerHTML;
        }

        //document.getElementById("dlink").href = uri + base64(format(template, ctx));
        //document.getElementById("dlink").download = filename;
        //document.getElementById("dlink").click();

        window.location.href = uri + base64(format(allOfIt, ctx));

    }
})();
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
function delete_donation(donationid,amount,userid) {
	if (confirm("{$lang['donation-deleteconfirm']}")) {
				window.location = "uTil/delete-donation.php?donationid=" + donationid + "&amount=" + amount + "&userid=" + userid + "&donscreen";
				}
}
EOD;
			
	pageStart("Regalos y descuentos", NULL, $deleteDonationScript, "pstatus", "statutes", "Regalos y descuentos", $_SESSION['successMessage'], $_SESSION['errorMessage']);	
	
?>

	 <table class='default' id='cloneTable' style='text-align: left;'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <img src="images/excel.png" style="cursor: pointer;" onclick="tablesToExcel(['t1'], ['t1'], 'myfile.xls')" value="Export to Excel" /><br /><br />
		<div style='display: inline-block; border: 2px solid #5aa242; padding: 10px;'>
		&nbsp;<strong>Filtrar:</strong><br /> 
        <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;
		
	} else {
		
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" value="$nowDate" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" value="$nowDate" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;

	}
	
	
?>
<br /><br />

        </form>
        </div>
       </td>
      </tr>
     </table>


<?php
	
echo <<<EOD
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;' id='t1'>
<!-- <tr>
  <td class='left'>Dispensado con precio normal</td>
  <td class='right'>{$expr(number_format($normalDispTOT,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td class='left'>Dispensado con precio descontado</td>
  <td class='right'>{$expr(number_format($reducedDispTOT,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td class='left'>Regalado</td>
  <td class='right'>{$expr(number_format($giftedDispTOT,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td class='left'></td>
  <td></td>
 </tr>-->
 <tr>
  <td class='left'>Perdidos sobre descuentos</td>
  <td class='right'>{$expr(number_format($reducedLost,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td class='left' style='border-bottom: 1px dashed #ababab;'>Perdidos sobre regalados</td>
  <td class='right' style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($giftedLost,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td class='left' style='border-bottom: 1px solid #ababab;'><strong>Perdido en total</strong></td>
  <td class='right' style='border-bottom: 1px solid #ababab;'><strong>{$expr(number_format($lostTOT,2))} {$_SESSION['currencyoperator']}</strong></td>
 </tr>
</table>
EOD;
?>

	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th class='right'>Normal</th>
	    <th class='right'>Discounted</th>
	    <th>Total g</th>
	    <th>Total u</th>
	    <th>Total normal</th>
	    <th>Total discounted</th>
	    <th>Total loss</th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
while ($saleB = mysql_fetch_array($resultB)) {

		$formattedDate = date("d-m-Y H:i:s", strtotime($saleB['saletime']."+$offsetSec seconds"));
		$saleid = $saleB['saleid'];
		$userid = $saleB['userid'];
		$quantity = $saleB['quantity'];
		$units = $saleB['units'];
		$credit = $saleB['creditBefore'];
		$newcredit = $saleB['creditAfter'];
		$discount = number_format($saleB['discount'],0);
		$direct = $saleB['direct'];
		
		if ($direct == 3) {
			$paymentMethod = $lang['global-credit'];
		} else if ($direct == 2) {
			$paymentMethod = $lang['card'];
		} else if ($direct == 1) {
			$paymentMethod = $lang['cash'];
		} else {
			$paymentMethod = '';
		}
		
		$amount = $saleB['amount'];
		$amountpaid = $saleB['amountpaid'];
		
		$selectPrice = "SELECT salesPrice FROM purchases WHERE purchaseid = $purchaseid";
		
		$priceResult = mysql_query($selectPrice)
			or handleError($lang['error-loadsales'] . mysql_error(),"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($priceResult);
			$salesPrice = $row['salesPrice'];
			
		$fullPrice = round($salesPrice * $quantity,2);
		
		$diff = $fullPrice - $amount;
		
		// Normal
		if ($amount == $fullPrice && $amount != 0) {
			
		// Gifted
		} else if ($quantity > 0 && $amount == 0) {
			
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		$userResult = mysql_query($userLookup)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
			
	    $row = mysql_fetch_array($userResult);
		$first_name = $row['first_name'];
		$memberno = $row['memberno'];
		
	if ($saleB['adminComment'] != '') {
		
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
		$onesaleResult8 = mysql_query($selectoneSale)
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
			if ($onesale['category'] > 2) {
				
				// Query to look for category
				$categoryDetailsC = "SELECT name, type FROM categories WHERE id = {$onesale['category']}";
				
				$resultC = mysql_query($categoryDetailsC)
					or handleError($lang['error-errorloadingflower'],"Error loading flower: " . mysql_error());
				
				$rowC = mysql_fetch_array($resultC);
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
		while ($onesale = mysql_fetch_array($onesaleResult8)) {
			echo number_format($fullPrice / $salesPrice,2) . " <span class='smallerfont'>".$_SESSION['currencyoperator']."</span><br />";
		}
			//echo "";
			//echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";

		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult4)) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>".$_SESSION['currencyoperator']."</span><br />";
		}
		echo "</td>";
	
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
				
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} {$_SESSION['currencyoperator']}</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$fullPrice} {$_SESSION['currencyoperator']}</strong></td>
				<td class='centered relative'>$commentRead</td></tr>
				";
				
		} else {
			
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		$userResult = mysql_query($userLookup)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
			
	    $row = mysql_fetch_array($userResult);
		$first_name = $row['first_name'];
		$memberno = $row['memberno'];
		
	if ($saleB['adminComment'] != '') {
		
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
			if ($onesale['category'] > 2) {
				
				// Query to look for category
				$categoryDetailsC = "SELECT name, type FROM categories WHERE id = {$onesale['category']}";
				
				$resultC = mysql_query($categoryDetailsC)
					or handleError($lang['error-errorloadingflower'],"Error loading flower: " . mysql_error());
				
				$rowC = mysql_fetch_array($resultC);
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
		while ($onesale = mysql_fetch_array($onesaleResult4)) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>{$_SESSION['currencyoperator']}</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
				
			if ($credit == NULL && $oldcredit == NULL) {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} {$_SESSION['currencyoperator']}</strong></td>
				<td class='centered relative'>$commentRead</td>
				</tr>
				";
				
			} else {
				
				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} {$_SESSION['currencyoperator']}</strong></td>
				<td class='centered relative'>$commentRead</td></tr>
				";
			
			}
			
			
			}
		
		

		


	}
displayFooter();
