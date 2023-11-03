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
	
	if (isset($_POST['sortsubmit'])) {
		
		foreach($_POST['cat'] as $sale) {
			
			$sortorder = $sale['sortorder'];
			$purchaseid = $sale['purchaseid'];
			
			if ($sortorder == 0) {
				
				$sortorder = 9999;
				
			}
			
			$query = "UPDATE purchases SET sortorder = '$sortorder' WHERE purchaseid = $purchaseid";
			try
			{
				$result = $pdo3->prepare("$query")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			
		}
		
	}
	
	$deleteDonationScript = <<<EOD
	
	    $(document).ready(function() {
		    
			$('#cloneTable').width($('#mainTable').width());

		    
		  		
			
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

	pageStart("Part mapping", NULL, $deleteDonationScript, "ppurchases", "purchases admin", "Part mapping", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// FIND OUT HOW MANY TABLES TO EXPORT
	$i = 1;
	
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
			$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = $categoryid ORDER by purchaseDate DESC LIMIT 100";
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
	
?>

<?php 
		
		// Query to look up categories, then products in each category
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
		
			$j = 3;
			$i = 1;

		while ($category = $resultCats->fetch()) {
			
			$categoryname = $category['name'];
			$categoryid = $category['id'];
			$type = $category['type'];
			
			if ($type == 0) {
				$type = "u";
			} else {
				$type = "g";
			}
			
			// Query to look up open products, extracts next
			$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode, sortorder FROM purchases WHERE closedAt IS NULL AND category = $categoryid ORDER by sortorder ASC, purchaseDate DESC LIMIT 100";
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
		
		

						
?>
<br /><br />

<h3 class='title'><?php echo "$categoryname ($type.)"; ?></h3>

	 <table class="default" id="t<?php echo $j; ?>">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th>Parts</th>
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
			$sortorder = $purchase['sortorder'];
			$category = $purchase['category'];
			
			$parts = '';
			// Find parts
			$selectPart = "SELECT quantity, product FROM parts WHERE purchaseid = $purchaseid";
			try
			{
				$resultsParts = $pdo3->prepare("$selectPart");
				$resultsParts->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			while ($row = $resultsParts->fetch()) {
				
				$quantity = $row['quantity'];
				$product = $row['product'];
				
				// Look up name of part
				$selectProduct = "SELECT name FROM b_products WHERE productid = $product";
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

				$parts .= "$quantity x $name<br />";
			
			}
				
			
	if ($sortorder == 9999) {
		$sortorder = 0;
	}
			
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

		$selectProduct = "SELECT name FROM products WHERE $productid = productid";
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

	  	
		if ($purchase['barCode'] != '') {
			$barCodeImg = "<img src='images/barcode.png' width='15' />";
		} else {
			$barCodeImg = "";
		}

	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='parts.php?open&purchaseid=%d'>%s<input type='hidden' name='cat[%d][purchaseid]' class='twoDigit' value='%d' /></td>
  	   <td class='clickableRow' href='parts.php?open&purchaseid=%d'>%s ({$purchase['purchaseid']})</td>
  	   <td class='clickableRow left' href='parts.php?open&purchaseid=%d'>%s</td>
  	   <td style='text-align: center' href='parts.php?open&purchaseid=%d'>%s</a></td>
	   <td class='relative'>$barCodeImg</td>
	   <td class='relative'>%s</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $i, $purchase['purchaseid'], $purchase['purchaseid'], $name, $purchase['purchaseid'], $parts, $purchase['purchaseid'], $inMenu, $commentRead);
	  	
	    	
	  echo $purchase_row;
	  
	$i++;
	}
	
	$j++;
} else {
	$j++;	
}		
			
?>

	 </tbody>
	 </table>
<?php
		}
	
?>

 

		
