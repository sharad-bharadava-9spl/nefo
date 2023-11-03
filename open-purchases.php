<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Is Workstations enabled?
	if ($_SESSION['puestosOrNot'] == 1) {
		
		if ($_SESSION['workstation'] != 'dispensary' && $_SESSION['userGroup'] > 1) {
			
   			handleError("No tienes permiso para ver esta pagina! / You are not authorized to see that page.","User has too low access level.");
			exit();
			
		}
		
	}
	
	
	getSettings();
	
	$_SESSION['purchasepage'] = 'open';
	
	if ($_SESSION['domain'] == 'dabulance') {
		
		if ($_SESSION['userGroup'] == 1 && isset($_GET['shopid'])) {
			$shopid = $_GET['shopid'];
		} else {
			
			$shopid = $_SESSION['shopid'];
			
			if ($shopid == '') {
				$shopid = 0;
			}
			
		}

		
	$deleteDonationScript = <<<EOD
	
	    $(document).ready(function() {
		    
			$('#cloneTable').width($('#mainTable').width());
			
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
					8: {
						sorter: "currency"
					},
					9: {
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
            if(exceltable != null){
            	ctx['table' + k] = exceltable.innerHTML;
        	}
        }

        //document.getElementById("dlink").href = uri + base64(format(template, ctx));
        //document.getElementById("dlink").download = filename;
        //document.getElementById("dlink").click();

        window.location.href = uri + base64(format(allOfIt, ctx));

    }
})();
EOD;

	pageStart($lang['purchases'], NULL, $deleteDonationScript, "pproducts", "admin", $lang['purchasescaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
		$selectCats = "SELECT id, name, description, type, icon from categories WHERE shopid = '$shopid' AND id > 2 ORDER by id ASC";
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
			$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, salesPrice2, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = $categoryid ORDER by purchaseDate DESC";
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
		
				$exp1 .= "'t$i', ";
		
			}
			
			$i++;
			
		}

				
	$exp1 = substr($exp1, 0, -2);
	
		
	// OPEN PRODUCTS FIRST
	
	echo "<center><a href='new-purchase.php' class='cta1'>{$lang['newpurchase']}</a>";
	
	if ($_SESSION['domain'] == 'thegarden') {
		
		if ($_SESSION['userGroup'] == 1) {
			echo "<a href='closed-purchases.php' class='cta4'>{$lang['closed-purchases']}</a>";
		}
	} else {
		echo "<a href='closed-purchases.php' class='cta4'>{$lang['closed-purchases']}</a>";
	}
	
?>
<?php if ($_SESSION['domain'] == 'dabulance') { ?>
		<a href="categories.php" class="cta4">Categories</a>
		<a href="products.php" class="cta4">Products</a>
<?php } ?>
<br />
<img src="images/excel-new.png" style="cursor: pointer;" onclick="loadExcel();" value="Export to Excel" />

<?php echo "</center>";
        

	// Query to look up categories
	$selectCats = "SELECT id, name, description, type, icon from categories WHERE shopid = '$shopid' AND id > 2 ORDER by sortorder ASC, id ASC";
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

	while ($sort = $resultCats->fetch()) {
		
		$categoryid = $sort['id'];
		$name = $sort['name'];
		$type = $sort['type'];
		$icon = $sort['icon'];
		
		if ($type == 0) {
			$type = "u";
		} else {
			$type = "g";
		}
		$domain = $_SESSION['domain'];
		$icon_image = "";
		if(trim($icon) != ''){
			$icon_image = "<img src='images/_$domain/category/$icon' class='filter-white' height='30' style='margin-right: 10px; margin-bottom: -8px;' />";
		}
			
			// Query to look up open products
			$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, salesPrice2, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = $categoryid ORDER by purchaseDate DESC LIMIT 200";
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
		
		

		echo "<h3 class='title' onClick='load$categoryid()' style='cursor: pointer;'>$icon_image $name ($type) <img src='images/expand.png' id='plus$categoryid' width='15' style='margin-left: 5px;' /><span id='spinner$categoryid'></span><input type='hidden' name='click$categoryid' id='click$categoryid' class='clickControl' value='0' /></h3><span id='menu$categoryid' style='display: none;'>";
		
		echo <<<EOD
<script>
function load$categoryid(){
	
  var x = document.getElementById("menu$categoryid");
  if (x.style.display === "none") {
	  
    x.style.display = "block";
	$("#plus$categoryid").attr("src","images/shrink.png");

  } else {
	  
    x.style.display = "none";
	$("#plus$categoryid").attr("src","images/expand.png");
	
  }

};
</script>
EOD;
						
?>

	 <table class="default" id="t<?php echo $j; ?>">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['add-realweightshort']; ?></th>
	    <th>Precio</th>
	    <th><?php echo $lang['purchase']; ?></th>
	    <th>Retail</th>
	    <th>Wholesale</th>
	    <th>United States</th>
	    <th>Barcelona</th>
	    <th>Amsterdam</th>
	    <th>Est. stock</th>
	    <th><?php echo $lang['pur-inmenu']; ?></th>
	    <th></th>
	    <th></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>

<?php

		foreach ($data as $purchase) {
	
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
			
			$inMenu = $purchase['inMenu'];
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['adminComment']}</div>
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

		$selectProduct = "SELECT name, breed2 FROM products WHERE $productid = productid";
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
			$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
			$breed2 = $row['breed2'];
		
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity), SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid";
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
	
		$row = $sale->fetch();
		if ($_SESSION['realWeight'] == 1) {		
			$sales = $row['SUM(realQuantity)'];
		} else {
			$sales = $row['SUM(quantity)'];
		}

		// Select reloads
		$selectPermAdditions = "SELECT SUM(quantity), SUM(realquantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 1";
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
			$totalReloads = $row['SUM(quantity)'];
			$realquantity = $row['SUM(realquantity)'];
			
			if ($realquantity > 0 ) {
				$totalReloads = $realquantity;
			}
			
		$selectPermAdditions = "SELECT SUM(quantity), SUM(realquantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 3 OR movementTypeid = 10 OR movementTypeid = 22)";
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
			$permAdditions = $row['SUM(quantity)'] + $totalReloads;
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
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
		$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
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
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
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
		$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
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
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
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
		 // KONSTANT CODE UPDATE BEGIN
                $selectSales = "SELECT id FROM volume_discounts WHERE purchaseid =". $purchase['purchaseid'];
                try
                {
                    $result = $pdo3->prepare("$selectSales");
                    $result->execute();
                    $data = $result->fetch();
                }
                catch (PDOException $e)
                {
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
                } 
                if ($data['id'] != '') {
                    $discountImg = "<img src='images/discount.png' width='15' />";
		} else {
                    $discountImg = "";
		}
        // KONSTANT CODE UPDATE END
	// Show G or U?
	if ($type == 'g') {

		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f {$_SESSION['currencyoperator']}</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td style='text-align: center' href='purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	    <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $purchase['salesPrice2'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);
	  
  	} else {
	  	
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}

	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.1f u</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f {$_SESSION['currencyoperator']}</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /u</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /u</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /u</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td style='text-align: center' href='purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	   <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $purchase['salesPrice2'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStashInt, $purchase['purchaseid'], $inStashExt, $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);
	  	
  	}
	    	
	  echo $purchase_row;
	  
	}
	
	$j++;
	
	echo "	 </tbody>
	 </table>
	<br /><br /></span> 
";
}		
			
?>

<?php
	

	}
		
	} else { // end dabulance
			
	$deleteDonationScript = <<<EOD
	
	    $(document).ready(function() {
		    
			$('#cloneTable').width($('#mainTable').width());
			
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
					8: {
						sorter: "currency"
					},
					9: {
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
            if(exceltable != null){
            	ctx['table' + k] = exceltable.innerHTML;
        	}
        }

        //document.getElementById("dlink").href = uri + base64(format(template, ctx));
        //document.getElementById("dlink").download = filename;
        //document.getElementById("dlink").click();

        window.location.href = uri + base64(format(allOfIt, ctx));

    }
})();
EOD;

	pageStart($lang['purchases'], NULL, $deleteDonationScript, "pproducts", "admin", $lang['purchasescaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// FIND OUT HOW MANY TABLES TO EXPORT
	$i = 1;
	// Query to look up open products, flowers first
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = 1 ORDER by purchaseDate DESC LIMIT 200";
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

		$exp1 .= "'t$i', ";

	}
	
	$i++;
	
	// Query to look up open products, extracts next
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = 2 ORDER by purchaseDate DESC LIMIT 200";
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

		$exp1 .= "'t$i', ";

	}
	
	$i++;
	
		$selectCats = "SELECT id, name, description, type from categories ORDER by id ASC";
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
			$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = $categoryid ORDER by purchaseDate DESC LIMIT 200";
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
		
				$exp1 .= "'t$i', ";
		
			}
			
			$i++;
			
		}

				
	$exp1 = substr($exp1, 0, -2);
	
		
	// OPEN PRODUCTS FIRST
	
	echo "<center><a href='new-purchase.php' class='cta1'>{$lang['newpurchase']}</a>";
	echo "<a href='closed-purchases.php' class='cta4'>{$lang['closed-purchases']}</a>";
	
?>
<?php if ($_SESSION['domain'] == 'dabulance') { ?>
		<a href="categories.php" class="cta4">Categories</a>
		<a href="products.php" class="cta4">Products</a>
<?php } ?>

<br />
<img src="images/excel-new.png" style="cursor: pointer;" onclick="loadExcel();" value="Export to Excel" />

<?php echo "</center>";
        

	// Query to look up categories
	$selectCats = "SELECT id, name, description, type, icon from categories ORDER by sortorder ASC, id ASC";
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

	while ($sort = $resultCats->fetch()) {
		
		$categoryid = $sort['id'];
		$name = $sort['name'];
		$type = $sort['type'];
		$icon = $sort['icon'];
		
		if ($type == 0) {
			$type = "u";
		} else {
			$type = "g";
		}
		$domain = $_SESSION['domain'];
		$icon_image = "";
		if(trim($icon) != ''){
			$icon_image = "<img src='images/_$domain/category/$icon' height='30' class= 'filter-white' style='margin-right: 10px; margin-bottom: -8px;' />";
		}
		if ($categoryid == 1) {

	
	// Query to look up open products, flowers first
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = 1 ORDER by purchaseDate DESC LIMIT 200";
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

		
		echo "<h3 class='title' onClick='load1()' style='cursor: pointer;'>$icon_image {$lang['global-flowerscaps']} (g.) <img src='images/expand.png' id='plus1' width='15' style='margin-left: 5px;' /><span id='spinner1'></span><input type='hidden' name='click1' id='click1' class='clickControl' value='0' /></h3><span id='menu1' style='display: none;'>";

		echo <<<EOD
<script>
function load1(){
	
  var x = document.getElementById("menu1");
  if (x.style.display === "none") {
	  
    x.style.display = "block";
	$("#plus1").attr("src","images/shrink.png");

  } else {
	  
    x.style.display = "none";
	$("#plus1").attr("src","images/expand.png");
	
  }

};
</script>
EOD;
						
?>


	 <table class="default" id="t1">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['add-realweightshort']; ?></th>
<?php if (($_SESSION['domain'] == 'bettyboopcpt' && $_SESSION['userGroup'] < 2) || ($_SESSION['domain'] == 'cannabisclubcpt' && $_SESSION['userGroup'] < 2) || ($_SESSION['domain'] != 'bettyboopcpt' && $_SESSION['domain'] != 'cannabisclubcpt')) { ?>
	    <th>Precio</th>
	    <th><?php echo $lang['purchase']; ?></th>
	    <th><?php echo $lang['pur-quota']; ?></th>
<?php } ?>
	    <th><?php echo $lang['global-dispense']; ?></th>
	    <th><?php echo $lang['pur-eststock']; ?></th>
	    <th><?php echo $lang['stash']; ?></th>
	    <th><?php echo $lang['pur-inmenu']; ?></th>
	    <th></th>
	    <th></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>

<?php

		foreach ($data as $purchase) {
			
			
	
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
		
			$inMenu = $purchase['inMenu'];
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['adminComment']}</div>
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

		$selectProduct = "SELECT name, breed2 FROM flower WHERE flowerid = $productid";
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
			$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
			$breed2 = $row['breed2'];
		
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity), SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid";
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
	
		$row = $sale->fetch();
		
		if ($_SESSION['realWeight'] == 1) {		
			$sales = $row['SUM(realQuantity)'];
		} else {
			$sales = $row['SUM(quantity)'];
		}

		// Select reloads
		$selectPermAdditions = "SELECT SUM(quantity), SUM(realquantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 1";
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
			$totalReloads = $row['SUM(quantity)'];
			$realquantity = $row['SUM(realquantity)'];
			
			if ($realquantity > 0 ) {
				$totalReloads = $realquantity;
			}
			
		$selectPermAdditions = "SELECT SUM(quantity), SUM(realquantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 3 OR movementTypeid = 10 OR movementTypeid = 22)";
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
			$permAdditions = $row['SUM(quantity)'] + $totalReloads;
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
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
		$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
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
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
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
		$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
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
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
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
		$estStock = $purchase['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
		
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
                // KONSTANT CODE UPDATE BEGIN
                $selectSales = "SELECT id FROM volume_discounts WHERE purchaseid =". $purchase['purchaseid'];
                try
                {
                    $result = $pdo3->prepare("$selectSales");
                    $result->execute();
                    $data = $result->fetch();
                }
                catch (PDOException $e)
                {
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
                } 
                if ($data['id'] != '') {
                    $discountImg = "<img src='images/discount.png' width='15' />";
		} else {
                    $discountImg = "";
		}
		
                // KONSTANT CODE UPDATE END
	if (($_SESSION['domain'] == 'bettyboopcpt' && $_SESSION['userGroup'] < 2) || ($_SESSION['domain'] == 'cannabisclubcpt' && $_SESSION['userGroup'] < 2) || ($_SESSION['domain'] != 'bettyboopcpt' && $_SESSION['domain'] != 'cannabisclubcpt')) {
		
		$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f {$_SESSION['currencyoperator']}</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td style='text-align: center' href='purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	   <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);
		
	} else {
		
		$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td style='text-align: center' href='purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	   <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);
	  
	  
  	}
	    	
	  echo $purchase_row;
	  
	}
}

?>

	 </tbody>
	 </table>
	 </span>
	 
<?php


		} else if ($categoryid == 2) {


	// Query to look up open products, extracts next
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = 2 ORDER by purchaseDate DESC LIMIT 200";
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

		

		echo "<h3 class='title' onClick='load2()' style='cursor: pointer;'>$icon_image {$lang['global-extractscaps']} (g.)<img src='images/expand.png' id='plus2' width='15' style='margin-left: 5px;' /><span id='spinner2'></span><input type='hidden' name='click2' id='click2' class='clickControl' value='0' /></h3><span id='menu2' style='display: none;'>";
		
		echo <<<EOD
<script>
function load2(){
	
  var x = document.getElementById("menu2");
  if (x.style.display === "none") {
	  
    x.style.display = "block";
	$("#plus2").attr("src","images/shrink.png");

  } else {
	  
    x.style.display = "none";
	$("#plus2").attr("src","images/expand.png");
	
  }

};
</script>
EOD;
						
?>

	 <table class="default" id="t2">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['add-realweightshort']; ?></th>
<?php if (($_SESSION['domain'] == 'bettyboopcpt' && $_SESSION['userGroup'] < 2) || ($_SESSION['domain'] == 'cannabisclubcpt' && $_SESSION['userGroup'] < 2) || ($_SESSION['domain'] != 'bettyboopcpt' && $_SESSION['domain'] != 'cannabisclubcpt')) { ?>
	    <th>Precio</th>
	    <th><?php echo $lang['purchase']; ?></th>
	    <th><?php echo $lang['pur-quota']; ?></th>
<?php } ?>
	    <th><?php echo $lang['global-dispense']; ?></th>
	    <th><?php echo $lang['pur-eststock']; ?></th>
	    <th><?php echo $lang['stash']; ?></th>
	    <th><?php echo $lang['pur-inmenu']; ?></th>
	    <th></th>
	    <th></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>

<?php

		foreach ($data as $purchase) {
	
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
			
			$inMenu = $purchase['inMenu'];
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['adminComment']}</div>
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

		$selectProduct = "SELECT name FROM extract WHERE $productid = extractid";
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
			$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity), SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid";
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
	
		$row = $sale->fetch();
		if ($_SESSION['realWeight'] == 1) {		
			$sales = $row['SUM(realQuantity)'];
		} else {
			$sales = $row['SUM(quantity)'];
		}

		// Select reloads
		$selectPermAdditions = "SELECT SUM(quantity), SUM(realquantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 1";
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
			$totalReloads = $row['SUM(quantity)'];
			$realquantity = $row['SUM(realquantity)'];
			
			if ($realquantity > 0 ) {
				$totalReloads = $realquantity;
			}
			
		$selectPermAdditions = "SELECT SUM(quantity), SUM(realquantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 3 OR movementTypeid = 10 OR movementTypeid = 22)";
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
			$permAdditions = $row['SUM(quantity)'] + $totalReloads;
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
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
		$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
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
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
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
		$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
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
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
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

		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
		                // KONSTANT CODE UPDATE BEGIN
                $selectSales = "SELECT id FROM volume_discounts WHERE purchaseid =". $purchase['purchaseid'];
                try
                {
                    $result = $pdo3->prepare("$selectSales");
                    $result->execute();
                    $data = $result->fetch();
                }
                catch (PDOException $e)
                {
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
                } 
                if ($data['id'] != '') {
                    $discountImg = "<img src='images/discount.png' width='15' />";
		} else {
                    $discountImg = "";
		}
                // KONSTANT CODE UPDATE END
	if (($_SESSION['domain'] == 'bettyboopcpt' && $_SESSION['userGroup'] < 2) || ($_SESSION['domain'] == 'cannabisclubcpt' && $_SESSION['userGroup'] < 2) || ($_SESSION['domain'] != 'bettyboopcpt' && $_SESSION['domain'] != 'cannabisclubcpt')) {
		
		$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f {$_SESSION['currencyoperator']}</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td style='text-align: center' href='purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	   <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);
	  
	} else {
		
		$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td style='text-align: center' href='purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	   <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);
	  
  	}
	    	
	  echo $purchase_row;
	  
	}
}
		
		
?>

	 </tbody>
	 </table>
	 </span>	 
<?php
		
		} else {
			
			// Query to look up open products, extracts next
			$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = $categoryid ORDER by purchaseDate DESC";
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
		
		

		echo "<h3 class='title' onClick='load$categoryid()' style='cursor: pointer;'>$icon_image $name ($type) <img src='images/expand.png' id='plus$categoryid' width='15' style='margin-left: 5px;' /><span id='spinner$categoryid'></span><input type='hidden' name='click$categoryid' id='click$categoryid' class='clickControl' value='0' /></h3><span id='menu$categoryid' style='display: none;'>";
		
		echo <<<EOD
<script>
function load$categoryid(){
	
  var x = document.getElementById("menu$categoryid");
  if (x.style.display === "none") {
	  
    x.style.display = "block";
	$("#plus$categoryid").attr("src","images/shrink.png");

  } else {
	  
    x.style.display = "none";
	$("#plus$categoryid").attr("src","images/expand.png");
	
  }

};
</script>
EOD;
						
?>

	 <table class="default" id="t<?php echo $j; ?>">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['add-realweightshort']; ?></th>
<?php if (($_SESSION['domain'] == 'bettyboopcpt' && $_SESSION['userGroup'] < 2) || ($_SESSION['domain'] == 'cannabisclubcpt' && $_SESSION['userGroup'] < 2) || ($_SESSION['domain'] != 'bettyboopcpt' && $_SESSION['domain'] != 'cannabisclubcpt')) { ?>
	    <th>Precio</th>
	    <th><?php echo $lang['purchase']; ?></th>
	    <th><?php echo $lang['pur-quota']; ?></th>
<?php } ?>
	    <th><?php echo $lang['global-dispense']; ?></th>
	    <th><?php echo $lang['pur-eststock']; ?></th>
	    <th><?php echo $lang['stash']; ?></th>
	    <th><?php echo $lang['pur-inmenu']; ?></th>
	    <th></th>
	    <th></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>

<?php

		foreach ($data as $purchase) {
	
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
			
			$inMenu = $purchase['inMenu'];
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['adminComment']}</div>
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

		$selectProduct = "SELECT name, breed2 FROM products WHERE $productid = productid";
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
			$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
			$breed2 = $row['breed2'];
		
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity), SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid";
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
	
		$row = $sale->fetch();
		if ($_SESSION['realWeight'] == 1) {		
			$sales = $row['SUM(realQuantity)'];
		} else {
			$sales = $row['SUM(quantity)'];
		}

		// Select reloads
		$selectPermAdditions = "SELECT SUM(quantity), SUM(realquantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 1";
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
			$totalReloads = $row['SUM(quantity)'];
			$realquantity = $row['SUM(realquantity)'];
			
			if ($realquantity > 0 ) {
				$totalReloads = $realquantity;
			}
			
		$selectPermAdditions = "SELECT SUM(quantity), SUM(realquantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 3 OR movementTypeid = 10 OR movementTypeid = 22)";
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
			$permAdditions = $row['SUM(quantity)'] + $totalReloads;
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
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
		$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
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
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
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
		$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
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
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
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
		 // KONSTANT CODE UPDATE BEGIN
                $selectSales = "SELECT id FROM volume_discounts WHERE purchaseid =". $purchase['purchaseid'];
                try
                {
                    $result = $pdo3->prepare("$selectSales");
                    $result->execute();
                    $data = $result->fetch();
                }
                catch (PDOException $e)
                {
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
                } 
                if ($data['id'] != '') {
                    $discountImg = "<img src='images/discount.png' width='15' />";
		} else {
                    $discountImg = "";
		}
        // KONSTANT CODE UPDATE END
        
	if (($_SESSION['domain'] == 'bettyboopcpt' && $_SESSION['userGroup'] < 2) || ($_SESSION['domain'] == 'cannabisclubcpt' && $_SESSION['userGroup'] < 2) || ($_SESSION['domain'] != 'bettyboopcpt' && $_SESSION['domain'] != 'cannabisclubcpt')) {
		
	if ($type == 'g') {

		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f {$_SESSION['currencyoperator']}</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td style='text-align: center' href='purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	    <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);
	  
  	} else {
	  	
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}

	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.1f u</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f {$_SESSION['currencyoperator']}</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /u</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /u</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /u</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td style='text-align: center' href='purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	   <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);
	  	
  	}
  	
  		} else {

	// Show G or U?
	if ($type == 'g') {

		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}
	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f g</td>
  	   <td style='text-align: center' href='purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	    <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);
	  
  	} else {
	  	
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}

	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.1f u</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /u</span></td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='purchase.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td style='text-align: center' href='purchase.php?open&purchaseid=%d'><a href='uTil/menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	   <td>$discountImg</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);
	  	
  	}
  	
			}
	    	
	  echo $purchase_row;
	  
	}
	
	$j++;
	
	echo "	 </tbody>
	 </table>
	<br /><br /></span> 
";
}		
			
?>

<?php
		}
	

	}

} // end non-dabulance

?>
<script type="text/javascript">
	
		 function loadExcel(){
 			$("#load").show();
       		window.location.href = 'open-purchases-report.php';
       		    setTimeout(function () {
			        $("#load").hide();
			    }, 5000);   
       }
</script>