<?php
	// Created by konstant for Task-14971179 on 12-11-2021
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
		                               
	$_SESSION['bar-purchasepage'] = 'closed';

	$deleteDonationScript = <<<EOD
	
	    $(document).ready(function() {
		    
			
			$('table').tablesorter({
				usNumberFormat: true,
				headers: {
					0: {
						sorter: "dates"
					},
					2: {
						sorter: "currency"
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
					},
					10: {
						sorter: "currency"
					},
					11: {
						sorter: "currency"
					}
				}
			}); 
 
	
		  		
			
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
EOD;

	pageStart($lang['purchases'], NULL, $deleteDonationScript, "ppurchases", "purchases admin", $lang['purchasescaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// FIND OUT HOW MANY TABLES TO EXPORT
	$i = 1;
	
		$selectCats = "SELECT id, name, description from b_categories WHERE id > 48 OR (id = 35 OR id = 34 OR id = 30 OR id = 19) ORDER by id ASC";
		try
		{
			$resultCats = $pdo3->prepare("$selectCats");
			$resultCats->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		while ($category = $resultCats->fetch()) {
			
			$categoryid = $category['id'];
			
			// Query to look up open products, extracts next
			$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, adminComment, inMenu, barCode, closingDate FROM b_purchases WHERE closedAt IS NOT NULL AND category = $categoryid ORDER by purchaseDate DESC";
			try
			{
				$result = $pdo3->prepare("$selectOpenPurchases");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
			if ($data) {
		     
				$exp1 .= "'t$categoryid', ";


					
		
			}
			
			$i++;
			
		}

				
	$exp1 = substr($exp1, 0, -2);
	
		
	echo "<center><a href='bar-open-stock.php' class='cta1'>{$lang['open-purchases']}</a>";
	echo "<a href='bar-new-stock-item.php' class='cta1'>{$lang['newpurchase']}</a>";
?>
<br><img src="images/excel-new.png" ID="xllink" onClick="tablesToExcel([<?php echo $exp1; ?>], [<?php echo $exp1; ?>], 'myfile.xls');" style="cursor: pointer;" value="Export to Excel" />
		
<?php
	echo "</center><br /><br /><h5 class='title'>{$lang['closed-purchases']}</h3><br />";
	
		
		// Query to look up categories, then products in each category
		$selectCats = "SELECT id, name, description from b_categories WHERE id > 48 OR (id = 35 OR id = 34 OR id = 30 OR id = 19) ORDER by  id ASC";
		try
		{
			$resultCats = $pdo3->prepare("$selectCats");
			$resultCats->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
			$j = 3;
		$datai = 2;
		$x = 3;
		while ($category = $resultCats->fetch()) {
			$categoryname = $category['name'];
			$categoryid = $category['id'];
				$type = "u";
			
			
	// Query to look up closed products, extracts next
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, closingComment, closedAt, estClosing, inMenu, barCode, closingDate FROM b_purchases WHERE closedAt IS NOT NULL AND category = $categoryid ORDER by purchaseDate DESC";
			try
			{
				$result = $pdo3->prepare("$selectOpenPurchases");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
			if ($data) {
	   if($datai > 2){
	   	    $datai  = $datai+1;
	   	    $x = $x+1;
	   }
		echo "<h3 class='title' onClick='load$categoryid()' style='cursor: pointer;'>$categoryname <img src='images/plusnew.png' id='plus$categoryid' width='15' style='margin-left: 5px;' /><span id='spinner$categoryid'></span><input type='hidden' name='click$categoryid' id='click$categoryid' class='clickControl' value='0' /></h3><span id='menu$categoryid' style='display: none;'>";
		
		echo <<<EOD
<script>
function load$categoryid(){
	
  var x = document.getElementById("menu$categoryid");
  if (x.style.display === "none") {
	  
    x.style.display = "block";
	$("#plus$categoryid").attr("src","images/minusnew.png");

  } else {
	  
    x.style.display = "none";
	$("#plus$categoryid").attr("src","images/plusnew.png");
	
  }

};
</script>
EOD;
						
?>
	 <table class="default mainTable<?php echo $categoryid; ?>" id="t<?php echo $categoryid; ?>">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['add-realweightshort']; ?></th>
	    <th>Precio</th>
	    <th><?php echo $lang['purchase']; ?></th>
	    <th><?php echo $lang['pur-quota']; ?></th>
	    <th><?php echo $lang['global-dispense']; ?></th>
	    <th></th>
	    <th></th>
	    <th><?php echo $lang['pur-closedat']; ?></th>
	    <th>Cerrado en</th>
	   </tr>
	  </thead>
	  <tbody>
<?php
		foreach ($data as $purchase) {
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
			
			$inMenu = $purchase['inMenu'];
			
			$closedAt = $purchase['closedAt'];
			
			if ($closedAt == 0) {
				$closeColour = '';
			}
			else if ($closedAt < 0) {
				$closeColour = 'negative';
			} else if ($closedAt > 0) {
				$closeColour = 'positive';
			}
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['closingComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['closingComment']}</div>
		                <script>
		                  	$('#comment$purchaseid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$purchaseid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$purchaseid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		

		$selectProduct = "SELECT name FROM b_products WHERE $productid = productid";
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
			$name = $row['name'];
			$formattedDate = date("d-m-Y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity) FROM b_salesdetails WHERE purchaseid = $purchaseid";
		try
		{
			$sale = $pdo3->prepare("$selectSales");
			$sale->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
			$sales = $row['SUM(quantity)'];

		$selectPermAdditions = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
		try
		{
			$sale = $pdo3->prepare("$selectPermAdditions");
			$sale->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $sale->fetch();
				$permAdditions = $row['SUM(quantity)'];
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
		try
		{
			$sale = $pdo3->prepare("$selectPermRemovals");
			$sale->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $sale->fetch();
				$permRemovals = $row['SUM(quantity)'];
				
		// Calculate what's in Internal stash
		$selectStashedInt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		try
		{
			$sale = $pdo3->prepare("$selectStashedInt");
			$sale->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $sale->fetch();
				$stashedInt = $row['SUM(quantity)'];
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		try
		{
			$sale = $pdo3->prepare("$selectUnStashedInt");
			$sale->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $sale->fetch();
				$unStashedInt = $row['SUM(quantity)'];
	
				
			$inStashInt = $stashedInt - $unStashedInt;
			$inStashInt = $inStashInt;
	
	
		// Calculate what's in External stash
		$selectStashedExt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		try
		{
			$sale = $pdo3->prepare("$selectStashedExt");
			$sale->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $sale->fetch();
				$stashedExt = $row['SUM(quantity)'];
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		try
		{
			$sale = $pdo3->prepare("$selectUnStashedExt");
			$sale->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $sale->fetch();
				$unStashedExt = $row['SUM(quantity)'];

			
		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExt = $inStashExt;
		
		$inStash = $inStashInt + $inStashExt;
		$estStock = $purchase['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;

	// Show G or U?
	if ($type == 'g') {
		
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='bar-stock-item.php?closed&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='bar-stock-item.php?closed&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'>%0.0f &euro;</td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'><span class=' %s'>%0.00f g</span></td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'>%s</span></td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $commentRead, $purchase['purchaseid'], $closeColour, $closedAt, $purchase['purchaseid'], date("d-m-Y", strtotime($purchase['closingDate'])));
	  
  	} else {
	  	
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='bar-stock-item.php?closed&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='bar-stock-item.php?closed&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'>%0.1f u</td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'>%0.0f &euro;</td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /u</span></td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /u</span></td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /u</span></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'><span class=' %s'>%0.00f u</span></td>
  	   <td class='clickableRow right' href='bar-stock-item.php?closed&purchaseid=%d'>%s</span></td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $commentRead, $purchase['purchaseid'], $closeColour, $closedAt, $purchase['purchaseid'], date("d-m-Y", strtotime($purchase['closingDate'])));
	  	
  	}
	    	
	  echo $purchase_row;
	  $datai++;
	  $x++;
	}
	
	$j++;
} else {
	$j++;	
}		
	
?>

	 </tbody>
	 </table>
	 </span>

<?php
		}
displayFooter();	
?>
