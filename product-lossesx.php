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
	
	$selectSales = "SELECT userid, saleid, saletime, quantity, amount, discount FROM sales WHERE 1 $timeLimit ORDER by saletime DESC";
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
	

	// Determine all saleid's for gifts and for discounts! DO NOT FORGET CHECKOUT DISCOUNTS!!!!
		while ($sale = $results->fetch()) {
		
		$saleid = $sale['saleid'];
		$discount = $sale['discount'];
		
		$selectSale = "SELECT purchaseid, quantity, amount FROM salesdetails WHERE saleid = $saleid";
		try
		{
			$resultOne = $pdo3->prepare("$selectSale");
			$resultOne->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($saleAnalysis = $resultOne->fetch()) {
			
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
			try
			{
				$result = $pdo3->prepare("$selectPrice");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$salesPrice = $row['salesPrice'];
				
			$fullPrice = round(round($salesPrice,2) * round($quantity,2),2);
				
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
	
		$row = $result->fetch();
			$formattedDate = date("d-m-Y H:i:s", strtotime($row['saletime']."+$offsetSec seconds"));
			$saletime = $row['saletime'];
			$saleid = $value;
			$saleidOne = $saleid;
			$quantity = $row['quantity'];
			$amount = $row['amount'];
			$purchaseid = $row['purchaseid'];
			$userid = $row['userid'];

		
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
			
		$userLookup = "SELECT operator FROM log WHERE logtime = '$saletime'";
		try
		{
			$result = $pdo3->prepare("$userLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user2: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$operator = $row['operator'];
			
		if ($operator > 0) {
			
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$operator}";
		try
		{
			$result = $pdo3->prepare("$userLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user3: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$first_name2 = $row['first_name'];
			$memberno2 = $row['memberno'];
			
		}
			
			
		
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount, d.purchaseid FROM salesdetails d, sales s WHERE d.saleid = $saleid and s.saleid = d.saleid";
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
			$onesaleResult6 = $pdo3->prepare("$selectoneSale");
			$onesaleResult6->execute();
			$onesaleResult7 = $pdo3->prepare("$selectoneSale");
			$onesaleResult7->execute();
			$onesaleResult8 = $pdo3->prepare("$selectoneSale");
			$onesaleResult8->execute();
			$onesaleResult9 = $pdo3->prepare("$selectoneSale");
			$onesaleResult9->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	   
		$detailedLosses .= "<tr>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";
  	   
  	   		$o = 0;
  	   		while ($onesale = $onesaleResult6->fetch()) {
	  	   		
	  	   		if ($o == 0) {
					$detailedLosses .= "$formattedDate<br/>";
				} else {
					$detailedLosses .= "<span class='white'>$formattedDate</span><br/>";
				}
				
				$o++;
			}
			$detailedLosses .= "</td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";
  	   
  	   		$q = 0;
  	   		while ($onesale = $onesaleResult9->fetch()) {
	  	   		if ($q == 0) {
					$detailedLosses .= "#$memberno2 - $first_name2<br/>";
				} else {
					$detailedLosses .= "<span class='white'>#$memberno2 - $first_name2</span><br/>";
				}
				
				$q++;
			}
			$detailedLosses .= "</td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";
  	   
  	   		$p = 0;
  	   		while ($onesale = $onesaleResult7->fetch()) {
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
				
			$detailedLosses .= $category . "<br />";
		}
		$detailedLosses .= "</td><td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";
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


			$detailedLosses .= $name . "<br />";
		}
		$detailedLosses .= "</td><td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'>";
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
				$detailedLosses .= number_format($onesale['quantity'],2) . " g<br />";
			} else {
				$detailedLosses .= number_format($onesale['quantity'],2) . " u<br />";
			}
		}
		
		$detailedLosses .= "</td><td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'>";
		
		
		$normalPrice = 0;
		$fullNormalPrice = 0;
		while ($onesale = $onesaleResult8->fetch()) {
			
			$selectPrice = "SELECT salesPrice FROM purchases WHERE purchaseid = {$onesale['purchaseid']}";
		try
		{
			$result = $pdo3->prepare("$selectPrice");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$salesPrice = $row['salesPrice'];
				
			$thisQuantity = $onesale['quantity'];
				
			$normalPrice = round($salesPrice * $thisQuantity,2);
			
			if (($normalPrice - $onesale['amount']) > 0.05) {
				
				$detailedLosses .= number_format($normalPrice,2) . " <span class='smallerfont'>".$_SESSION['currencyoperator']."</span><br />";
				$fullNormalPrice = $fullNormalPrice + $normalPrice;
				
			} else {
				
				$detailedLosses .= number_format($onesale['amount'],2) . " <span class='smallerfont'>".$_SESSION['currencyoperator']."</span><br />";
				$fullNormalPrice = $fullNormalPrice + $onesale['amount'];
				
			}
			
			
			
			
		}

		$detailedLosses .= "</td><td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'>";
		
		$fullPaidPrice = 0;
		while ($onesale = $onesaleResult4->fetch()) {
			
			$detailedLosses .= number_format($onesale['amount'],2) . " <span class='smallerfont'>".$_SESSION['currencyoperator']."</span><br />";
			
			$fullPaidPrice = $fullPaidPrice + $onesale['amount'];
			
				$selectPrice = "SELECT salesPrice FROM purchases WHERE purchaseid = {$onesale['purchaseid']}";
				try
				{
					$result = $pdo3->prepare("$selectPrice");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
	
		$row = $result->fetch();
					$salesPrice = $row['salesPrice'];
					
				$thisQuantity = $onesale['quantity'];
					
				$normalPrice = round($salesPrice * $thisQuantity,2);
			
			if ($onesale['amount'] == 0) {
				
			
				$giftedLost = number_format($giftedLost + $normalPrice,2);
				
			} else if (($normalPrice - $onesale['amount']) > 0.05) {
				
				$reducedLost = $reducedLost + $normalPrice - $onesale['amount'];
				
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
				";	
				
	}
	
	echo <<<EOD
<br /><table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;' id='t1'>
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
	    <th><?php echo $lang['responsible']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th class='right'><?php echo $lang['price']; ?></th>
	    <th class='right'><?php echo $lang['paid']; ?></th>
	    <th><?php echo $lang['total-price']; ?></th>
	    <th><?php echo $lang['total-paid']; ?></th>
	    <th><?php echo $lang['loss']; ?></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>

<?php

	echo $detailedLosses . "</tbody></table>";

displayFooter();