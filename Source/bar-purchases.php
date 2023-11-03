<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Query to look up open products
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, adminComment, closedAt, estClosing, inMenu, barCode, closingDate FROM b_purchases WHERE closedAt IS NULL ORDER by purchaseDate DESC";
		try
		{
			$resultOpen = $pdo3->prepare("$selectOpenPurchases");
			$resultOpen->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

	// Query to look up closed products
	$selectClosedPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, adminComment, closedAt, estClosing, inMenu, closingComment, barCode, closingDate FROM b_purchases WHERE closedAt IS NOT NULL ORDER by purchaseDate DESC";
		try
		{
			$resultClosed = $pdo3->prepare("$selectClosedPurchases");
			$resultClosed->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
	$deletePurchaseScript = <<<EOD
function delete_purchase(purchaseid) {
	if (confirm("{$lang['confirm-deleteproduct']}")) {
				window.location = "uTil/bar-delete-bar-purchase.php?purchaseid=" + purchaseid;
				}
}

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

	    $(document).ready(function() {
		    
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
					3: {
						sorter: "currency"
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
					}
				}
			});
			
		});

EOD;
	pageStart($lang['purchases'], NULL, $deletePurchaseScript, "ppurchases", "purchases admin", $lang['purchasescaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>

<center>
 <a href="bar-new-purchase.php" class="cta"><?php echo $lang['newpurchase']; ?></a> 
 <img src="images/excel.png" style="cursor: pointer;" onclick="tablesToExcel(['mainTable'], ['mainTable'], 'myfile.xls')" value="Export to Excel" />
</center>




	 <table class="default" id="mainTable">
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['purchase']; ?></th>
	    <th><?php echo $lang['pur-quota']; ?></th>
	    <th><?php echo $lang['global-dispense']; ?></th>
	    <th><?php echo $lang['title-stock']; ?></th>
	    <th><?php echo $lang['pur-inmenu']; ?></th>
	    <th><?php echo $lang['pur-closedat']; ?></th>
	    <th>Cerrado en</th>
	    <th></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	  		// Non-closed products
		while ($purchase = $resultOpen->fetch()) {

			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
		
			$closedAt = $purchase['closedAt'];
			
			if ($closedAt == 0) {
				$closeColour = '';
			}
			else if ($closedAt < 0) {
				$closeColour = 'negative';
			} else if ($closedAt > 0) {
				$closeColour = 'positive';
			}
			
			$inMenu = $purchase['inMenu'];
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}

			if ($purchase['adminComment'] != '') {
				
				$commentRead = "
				                <img src='images/comments.png' id='comment$productid' /><div id='helpBox$productid' class='helpBox'>{$purchase['adminComment']}</div>
				                <script>
				                  	$('#comment$productid').on({
								 		'mouseover' : function() {
										 	$('#helpBox$productid').css('display', 'block');
								  		},
								  		'mouseout' : function() {
										 	$('#helpBox$productid').css('display', 'none');
									  	}
								  	});
								</script>
				                ";
				
			} else {
				
				$commentRead = "";
				
			}		

		
		// Query to look for category
		$categoryDetails = "SELECT name FROM b_categories WHERE id = {$purchase['category']}";
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
			$purchaseCategory = $row['name'];
		
		$prodSelect = 'pr.name FROM b_products pr';
		$prodJoin = "pr.productid AND p.category = {$purchase['category']}";

		$selectProduct = "SELECT {$prodSelect}, b_purchases p WHERE ({$productid} = {$prodJoin})";
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
			$name = $row['name'];
			$formattedDate = date("d-m-Y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
			
		// Calculate Stock
		
		$selectSales = "SELECT SUM(quantity) FROM b_salesdetails WHERE purchaseid = $purchaseid";
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
			$sales = $row['SUM(quantity)'];

		$selectPermAdditions = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
		try
		{
			$result = $pdo3->prepare("$selectPermAdditions");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$permAdditions = $row['SUM(quantity)'];
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
		try
		{
			$result = $pdo3->prepare("$selectPermRemovals");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$permRemovals = $row['SUM(quantity)'];
				
		// Calculate what's in Internal stash
		$selectStashedInt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		try
		{
			$result = $pdo3->prepare("$selectStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$stashedInt = $row['SUM(quantity)'];
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$unStashedInt = $row['SUM(quantity)'];
	
				
			$inStashInt = $stashedInt - $unStashedInt;
			$inStashInt = $inStashInt;
	
	
		// Calculate what's in External stash
		$selectStashedExt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		try
		{
			$result = $pdo3->prepare("$selectStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$stashedExt = $row['SUM(quantity)'];
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$unStashedExt = $row['SUM(quantity)'];

			
		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExt = $inStashExt;
		
		$inStash = $inStashInt + $inStashExt;
		$estStock = $purchase['purchaseQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
		
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
		
	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='bar-purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='bar-purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='bar-purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow right' href='bar-purchase.php?purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='bar-purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /u</span></td>
  	   <td class='clickableRow right' href='bar-purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /u</span></td>
  	   <td class='clickableRow right' href='bar-purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /u</span></td>
  	   <td class='clickableRow right' href='bar-purchase.php?purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow' style='text-align: center' href='bar-purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: center' href='bar-purchase.php?purchaseid=%d'></td>
	   <td class='relative'></td>
	   <td class='relative'>%s</td>
	   <td class='relative'>%s</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $purchaseCategory, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inMenu, $purchase['purchaseid'], $commentRead, $barCodeImg);
	  
  	
	  echo $purchase_row;
	  
}
	  
	  		// Closed products  		
		while ($purchase = $resultClosed->fetch()) {
			$productid = $purchase['productid'];
		
			$closedAt = $purchase['closedAt'];
			
			if ($closedAt == 0) {
				$closeColour = '';
			}
			else if ($closedAt < 0) {
				$closeColour = 'negative';
			} else if ($closedAt > 0) {
				$closeColour = 'positive';
			}
			
			$inMenu = $purchase['inMenu'];
			
			if ($inMenu == 1) {
				$inMenu = 'Yes';
			} else {
				$inMenu = 'No';
			}


			if ($purchase['closingComment'] != '') {
				
				$commentRead = "
				                <img src='images/comments.png' id='comment$productid' /><div id='helpBox$productid' class='helpBox'>{$purchase['closingComment']}</div>
				                <script>
				                  	$('#comment$productid').on({
								 		'mouseover' : function() {
										 	$('#helpBox$productid').css('display', 'block');
								  		},
								  		'mouseout' : function() {
										 	$('#helpBox$productid').css('display', 'none');
									  	}
								  	});
								</script>
				                ";
				
			} else {
				
				$commentRead = "";
				
			}		
			
		// Query to look for category
		$categoryDetails = "SELECT name FROM b_categories WHERE id = {$purchase['category']}";
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
			$purchaseCategory = $row['name'];
		
		$prodSelect = 'pr.name FROM b_products pr';
		$prodJoin = "pr.productid AND p.category = {$purchase['category']}";

		$selectProduct = "SELECT {$prodSelect}, b_purchases p WHERE ({$productid} = {$prodJoin})";
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
		$name = $row['name'];
		$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
		
	  		// Non-closed products
	  		
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
	  
			$purchase_row =	sprintf("
		  	  <tr class='closedProduct'>
		  	   <td class='clickableRow' href='bar-purchase.php?purchaseid=%d'>%s</td>
		  	   <td class='clickableRow' href='bar-purchase.php?purchaseid=%d'>%s</td>
		  	   <td class='clickableRow' href='bar-purchase.php?purchaseid=%d'>%s</td>
		  	   <td class='clickableRow right' href='bar-purchase.php?purchaseid=%d'>%0.0f u</td>
		  	   <td class='clickableRow right' href='bar-purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /u</span></td>
		  	   <td class='clickableRow right' href='bar-purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /u</span></td>
		  	   <td class='clickableRow right' href='bar-purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /u</span></td>
		  	   <td></td>
		  	   <td class='clickableRow' style='text-align: center' href='bar-purchase.php?purchaseid=%d'>%s</td>
		  	   <td class='clickableRow right' href='bar-purchase.php?purchaseid=%d'><span class=' %s'>%0.02f u</span></td>
			   <td class='relative'>%s</td>
			   <td class='relative'>%s</td>
			   <td class='relative'>%s</td>
			  </tr>",
			  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $purchaseCategory, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $inMenu, $purchase['purchaseid'], $closeColour, $closedAt, date("d-m-Y", strtotime($purchase['closingDate'])), $commentRead, $barCodeImg);
			  

	  echo $purchase_row;
	  
	  
  }
		
?>

	 </tbody>
	 </table>
	 
<?php  displayFooter(); ?>
