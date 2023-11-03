<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$_SESSION['bar-purchasepage'] = 'open';
	
	$deleteDonationScript = <<<EOD
	
	    $(document).ready(function() {
		    
			$('#cloneTable').width($('#mainTable').width());

		    $('.default').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					},
					7: {
						sorter: "dates"
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
			$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, adminComment, inMenu, barCode FROM b_purchases WHERE closedAt IS NULL AND category = $categoryid ORDER by purchaseDate DESC";
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
	
		
	// OPEN PRODUCTS FIRST
	
	echo "<center><a href='bar-closed-stock.php' class='cta1'>{$lang['closed-purchases']}</a>";
	echo "<a href='bar-new-stock-item.php' class='cta1'>{$lang['newpurchase']}</a>";
?>

<br><img src="images/excel-new.png" style="cursor: pointer;" onclick="tablesToExcel([<?php echo $exp1; ?>], [<?php echo $exp1; ?>], 'myfile.xls');" value="Export to Excel" />

<?php echo "</center><br /><h5 class='title2'>{$lang['open-purchases']}</h3><br />";
        

	// Query to look up categories
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

	$j = 1;
	$datai = 2;
	$x = 3;
	
	while ($sort = $resultCats->fetch()) {
		
		$categoryid = $sort['id'];
		$name = $sort['name'];
		
			$type = "u";
		
			// Query to look up open products, extracts next
			$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, adminComment, inMenu, barCode FROM b_purchases WHERE closedAt IS NULL AND category = $categoryid ORDER by purchaseDate DESC";
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
		
		

		echo "<h3 class='title' onClick='load$categoryid()' style='cursor: pointer;'>$name ($type) <img src='images/plusnew.png' id='plus$categoryid' width='15' style='margin-left: 5px;' /><span id='spinner$categoryid'></span><input type='hidden' name='click$categoryid' id='click$categoryid' class='clickControl' value='0' /></h3><span id='menu$categoryid' style='display: none;'>";
		
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

	 <table class="default" id="t<?php echo $categoryid; ?>">
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
	    <th>Madrid</th>
	    <th>Ireland</th>
	    <th><strong>TOTAL Stock</strong></th>
	    <th><?php echo $lang['pur-inmenu']; ?></th>
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

		$selectProduct = "SELECT name FROM b_products WHERE productid = $productid";
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
			// Original purchase
   		$purchaseLookup = "SELECT purchaseQuantity from b_purchases where purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$purchaseLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$openingWeight = $row['purchaseQuantity'];

				
				// Sales
		$selectSales = "SELECT SUM(quantity) FROM b_salesdetails WHERE purchaseid = $purchaseid AND amount <> 0";
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
		
				// Gifts
		$selectSales = "SELECT SUM(quantity) FROM b_salesdetails WHERE purchaseid = $purchaseid AND amount = 0";
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
					$gifts = $row['SUM(quantity)'];
		
					
				// Additions and Removals (not permanent, just wrong variable name)
		$selectPermAdditions = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1";
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
				
		$selectPermRemovals = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2";
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
		$jarWeight = $openingWeight + $permAdditions - $permRemovals - $sales - $gifts;	
		
		$weightTotal = $jarWeight + $inStashInt + $inStashExt;

		$totStock = $jarWeight + $inStashInt + $inStashExt;
		$madrid = $jarWeight + $inStashInt;

		if ($madrid < 1) {
			$madrid = "<span style='color: red;'>$madrid</span>";
		} else {
			$madrid = $madrid;
		}
		if ($totStock < 1) {
			$totStock = "<span style='color: red;'>$totStock</span>";
		} else {
			$totStock = $totStock;
		}
		
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}


	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='bar-stock-item.php?open&purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='bar-stock-item.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow right' href='bar-stock-item.php?open&purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow right' href='bar-stock-item.php?open&purchaseid=%d'>%0.1f u</td>
  	   <td class='clickableRow right' href='bar-stock-item.php?open&purchaseid=%d'>%0.0f &euro;</td>
  	   <td class='clickableRow right' href='bar-stock-item.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /u</span></td>
  	   <td class='clickableRow right' href='bar-stock-item.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /u</span></td>
  	   <td class='clickableRow right' href='bar-stock-item.php?open&purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /u</span></td>
		<td class='centered'>$madrid u</td>
	    <td class='centered'>$inStashExt u</td>
	    <td class='centered'><strong>$totStock u</strong></td>
  	   <td style='text-align: center' href='bar-stock-item.php?open&purchaseid=%d'><a href='uTil/bar-menuchange.php?purchaseid=%d&menu=%s'>%s</a></td>
	   <td>$barCodeImg</td>
	   <td><span class='relativeitem'>%s</span></td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'],  $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);
	  	
  	
	    	
	  echo $purchase_row;
	   $datai++;
	  $x++;
	  
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
	displayFooter();
?>
