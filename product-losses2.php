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

	// Query to look up sales
	// $selectSales = "SELECT s.userid, s.saleid, s.saletime, d.purchaseid, d.quantity, d.amount, d.purchaseid FROM sales s, salesdetails d WHERE s.saleid = d.saleid $timeLimit ORDER by saletime DESC";
	
	$selectSales = "SELECT userid, saleid, saletime, quantity, amount, discount FROM sales WHERE saleid = 33376 ORDER by saletime DESC";
		
	$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

	// Determine all saleid's for gifts and for discounts! DO NOT FORGET CHECKOUT DISCOUNTS!!!!
	while ($sale = mysql_fetch_array($result)) {
		
		$saleid = $sale['saleid'];
		$discount = $sale['discount'];
		
		$selectSale = "SELECT purchaseid, quantity, amount FROM salesdetails WHERE saleid = $saleid";
		
		$resultOne = mysql_query($selectSale)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

		while ($saleAnalysis = mysql_fetch_array($resultOne)) {
			
			$quantity = $saleAnalysis['quantity'];
			$amount = $saleAnalysis['amount'];
			$purchaseid = $saleAnalysis['purchaseid'];
			
			// quantity   2,35
			// amount    10,39
			
			// AMOUNT:
			// hierba     0,00
			// kritikal  10,39
			
			// FULLPRICE:
			// hierba     0,00 * 0,37 = 0,00
			// kritikal   5,25 * 1,98 = 10,39
			
			
			$selectPrice = "SELECT salesPrice FROM purchases WHERE purchaseid = $purchaseid";
			
			$priceResult = mysql_query($selectPrice)
				or handleError($lang['error-loadsales'] . mysql_error(),"Error loading sale from db: " . mysql_error());
			
			$row = mysql_fetch_array($priceResult);
				$salesPrice = $row['salesPrice'];
				
			$fullPrice = round($salesPrice * $quantity,2);
				
			echo "saleid: $saleid<br />";
			echo "quantity: $quantity<br />";
			echo "amount: $amount<br />";
			echo "purchaseid: $purchaseid<br />";
			echo "salesPrice: $salesPrice<br />";
			echo "fullPrice: $fullPrice<br />";
			
			if ($amount == $fullPrice && $amount != 0) {
				echo "NORMAL";
			// Gifted
			} else if ($quantity > 0 && $amount == 0) {
				
				echo "GIFT";
				
			} else {
				
				echo "DISCOUNT";
				
			}
			echo "<br /><br />";
			
			
			// Construct saleid arrays
			// Normal
			if ($amount == $fullPrice && $amount != 0) {
				
			// Gifted
			} else if ($quantity > 0 && $amount == 0) {
				
				if(!in_array($saleid, $giftArray)){
					$giftArray[] = $saleid;
				}
				
			// Discounted
			} else if (($fullPrice - $amount) > 0.1) {
				
				if(!in_array($saleid, $discountArray)){
					$discountArray[] = $saleid;
				}
				
			}
			
		}
		
	}
	
	if (isset($giftArray) && isset($discountArray)) {
		
		$combinedArray = array_merge($giftArray,$discountArray);
		
	} else if (isset($giftArray)) {
		
		$combinedArray = $giftArray;
		
	} else if (isset($discountArray)) {
		
		$combinedArray = $discountArray;
		
	}
	
	rsort($combinedArray);
	
	$combinedArray = array_unique($combinedArray);
	

	// Loop through array and create table value for each
	foreach($combinedArray as $value) {
				
		$selectSales = "SELECT s.saletime, s.userid, d.purchaseid, d.quantity, d.amount, d.purchaseid FROM sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saleid = $value";
		
		$result = mysql_query($selectSales)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$formattedDate = date("d-m-Y H:i:s", strtotime($row['saletime']."+$offsetSec seconds"));
			$saleid = $value;
			$saleidOne = $saleid;
			$quantity = $row['quantity'];
			$amount = $row['amount'];
			$purchaseid = $row['purchaseid'];
			$userid = $row['userid'];
			

		
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		
		$userResult = mysql_query($userLookup)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
			
	    $row = mysql_fetch_array($userResult);
			$first_name = $row['first_name'];
			$memberno = $row['memberno'];
			
			
			
		
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount, d.purchaseid FROM salesdetails d, sales s WHERE d.saleid = $saleid and s.saleid = d.saleid";
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
			
	   
		$detailedLosses .= "<tr>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";
  	   
  	   		$o = 0;
  	   		while ($onesale = mysql_fetch_array($onesaleResult6)) {
	  	   		
	  	   		if ($o == 0) {
					$detailedLosses .= "$formattedDate<br/>";
				} else {
					$detailedLosses .= "<span class='white'>$formattedDate</span><br/>";
				}
				
				$o++;
			}
			$detailedLosses .= "</td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";
  	   
  	   		$p = 0;
  	   		while ($onesale = mysql_fetch_array($onesaleResult7)) {
	  	   		if ($p == 0) {
					$detailedLosses .= "#$memberno - $first_name<br/>";
				} else {
					$detailedLosses .= "<span class='white'>#$memberno - $first_name</span><br/>";
				}
				
				$p++;
			}
$detailedLosses .= "
  	   </td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";
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
				
			$detailedLosses .= $category . "<br />";
		}
		$detailedLosses .= "</td><td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";
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


			$detailedLosses .= $name . "<br />";
		}
		$detailedLosses .= "</td><td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'>";
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
				$detailedLosses .= number_format($onesale['quantity'],2) . " g<br />";
			} else {
				$detailedLosses .= number_format($onesale['quantity'],2) . " u<br />";
			}
		}
		
		$detailedLosses .= "</td><td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'>";
		
		
		$normalPrice = 0;
		$fullNormalPrice = 0;
		while ($onesale = mysql_fetch_array($onesaleResult8)) {
			
			$selectPrice = "SELECT salesPrice FROM purchases WHERE purchaseid = {$onesale['purchaseid']}";
			
			$priceResult = mysql_query($selectPrice)
				or handleError($lang['error-loadsales'] . mysql_error(),"Error loading sale from db: " . mysql_error());
			
			$row = mysql_fetch_array($priceResult);
				$salesPrice = $row['salesPrice'];
				
			$thisQuantity = $onesale['quantity'];
				
			$normalPrice = round($salesPrice * $thisQuantity,2);
			
			$detailedLosses .= number_format($normalPrice,2) . " <span class='smallerfont'>".$_SESSION['currencyoperator']."</span><br />";
			
			$fullNormalPrice = $fullNormalPrice + $normalPrice;
			
			
			
		}

		$detailedLosses .= "</td><td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'>";
		
		$fullPaidPrice = 0;
		while ($onesale = mysql_fetch_array($onesaleResult4)) {
			
			$detailedLosses .= number_format($onesale['amount'],2) . " <span class='smallerfont'>".$_SESSION['currencyoperator']."</span><br />";
			
			$fullPaidPrice = $fullPaidPrice + $onesale['amount'];
			
				$selectPrice = "SELECT salesPrice FROM purchases WHERE purchaseid = {$onesale['purchaseid']}";
				
				$priceResult = mysql_query($selectPrice)
					or handleError($lang['error-loadsales'] . mysql_error(),"Error loading sale from db: " . mysql_error());
				
				$row = mysql_fetch_array($priceResult);
					$salesPrice = $row['salesPrice'];
					
				$thisQuantity = $onesale['quantity'];
					
				$normalPrice = round($salesPrice * $thisQuantity,2);
			
			if ($onesale['amount'] == 0) {
				
			
				$giftedLost = number_format($giftedLost + $normalPrice,2);
				
			} else if (($normalPrice - $onesale['amount']) > 0.01) {
				
				$reducedLost = number_format($reducedLost + ($normalPrice - $onesale['amount']),2);
				
				// normal price - paid price
				
			}
			
			
		}
		$detailedLosses .= "</td>";
	
		$amount = number_format($amount,2);
		$fullNormalPrice = number_format($fullNormalPrice,2);
		$fullPaidPrice = number_format($fullPaidPrice,2);
		$fullLoss = number_format($fullNormalPrice - $fullPaidPrice,2);

		$quantity = number_format($quantity,2);
		$fullPrice = number_format($fullPrice,2);
		$loss = number_format($fullPrice - $amount,2);
						
				$detailedLosses .= "
				<td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'><strong>{$fullNormalPrice} {$_SESSION['currencyoperator']}</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'><strong>{$fullPaidPrice} {$_SESSION['currencyoperator']}</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'><strong>{$fullLoss} {$_SESSION['currencyoperator']}</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'><strong>{$giftedLost} {$_SESSION['currencyoperator']}</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'><strong>{$reducedLost} {$_SESSION['currencyoperator']}</strong></td>
				";	
				
	}
	
	echo <<<EOD
<br /><table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;' id='t1'>
<!-- <tr>
  <td class='left'>Dispensado con precio normal</td>
  <td class='right'>{$expr(number_format($normalDispTOT,2))} ?</td>
 </tr>
 <tr>
  <td class='left'>Dispensado con precio descontado</td>
  <td class='right'>{$expr(number_format($reducedDispTOT,2))} ?</td>
 </tr>
 <tr>
  <td class='left'>Regalado</td>
  <td class='right'>{$expr(number_format($giftedDispTOT,2))} ?</td>
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
  <td class='right' style='border-bottom: 1px solid #ababab;'><strong>{$expr(number_format($reducedLost + $giftedLost,2))} {$_SESSION['currencyoperator']}</strong></td>
 </tr>
</table>
EOD;
	
?>
<br /><br />
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th class='right'>Price</th>
	    <th class='right'>Paid</th>
	    <th>Total price</th>
	    <th>Total paid</th>
	    <th>Total loss</th>
	    <th>GIFT acc</th>
	    <th>DISC acc</th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>

<?php

	echo $detailedLosses . "</tbody></table>";

displayFooter();