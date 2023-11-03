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

	pageStart($lang['purchases'], NULL, $deleteDonationScript, "ppurchases", "purchases admin", $lang['purchasescaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// FIND OUT HOW MANY TABLES TO EXPORT
	$i = 1;
	// Query to look up open products, flowers first
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = 1 ORDER by purchaseDate DESC LIMIT 100";
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
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = 2 ORDER by purchaseDate DESC LIMIT 100";
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
	
	echo "<center><a href='closed-purchases.php' class='cta'>{$lang['closed-purchases']}</a>";
	echo "<a href='new-purchase.php' class='cta'>{$lang['newpurchase']}</a>";
?>

<img src="images/excel.png" style="cursor: pointer;" onclick="tablesToExcel([<?php echo $exp1; ?>], [<?php echo $exp1; ?>], 'myfile.xls')" value="Export to Excel" />

<?php echo "</center><br /><h3 class='title2'>{$lang['open-purchases']}</h3><br />";
        

	// Query to look up categories
	$selectCats = "SELECT id, name, description, type from categories ORDER by sortorder ASC, id ASC";
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
		
		if ($type == 0) {
			$type = "u";
		} else {
			$type = "g";
		}
		
		if ($categoryid == 1) {

	
	// Query to look up open products, flowers first
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = 1 ORDER by purchaseDate DESC LIMIT 100";
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

		
		echo "<h3 class='title' onClick='load1()' style='cursor: pointer;'>{$lang['global-flowerscaps']} (g.) <img src='images/plusnew.png' id='plus1' width='15' style='margin-left: 5px;' /><span id='spinner1'></span><input type='hidden' name='click1' id='click1' class='clickControl' value='0' /></h3><span id='menu1' style='display: none;'>";

		echo <<<EOD
<script>
function load1(){
	
  var x = document.getElementById("menu1");
  if (x.style.display === "none") {
	  
    x.style.display = "block";
	$("#plus1").attr("src","images/minusnew.png");

  } else {
	  
    x.style.display = "none";
	$("#plus1").attr("src","images/plusnew.png");
	
  }

};
</script>
EOD;
						
?>


	 <table class='default' id='cloneTable' style='text-align: left;'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
       
         <a id="dlink"  style="display:none;"></a>
        <script src="~/Views/JS/JSExcel.js" type="text/javascript"></script>
        
       </td>
      </tr>
     </table>
	 <table class="default" id="t1">
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
	    <th><?php echo $lang['title-stock']; ?></th>
	    <th><?php echo $lang['stash']; ?></th>
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

		$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10 OR movementTypeid = 22)";
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
	   <td class='relative'>$barCodeImg</td>
	   <td class='relative'>%s</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);
	    	
	  echo $purchase_row;
	  
	}
}

?>

	 </tbody>
	 </table>
<br /><br />
	 </span>
	 
<?php


		} else if ($categoryid == 2) {


	// Query to look up open products, extracts next
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu, barCode FROM purchases WHERE closedAt IS NULL AND category = 2 ORDER by purchaseDate DESC LIMIT 100";
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

		

		echo "<h3 class='title' onClick='load2()' style='cursor: pointer;'>{$lang['global-extractscaps']} (g.)<img src='images/plusnew.png' id='plus2' width='15' style='margin-left: 5px;' /><span id='spinner2'></span><input type='hidden' name='click2' id='click2' class='clickControl' value='0' /></h3><span id='menu2' style='display: none;'>";
		
		echo <<<EOD
<script>
function load2(){
	
  var x = document.getElementById("menu2");
  if (x.style.display === "none") {
	  
    x.style.display = "block";
	$("#plus2").attr("src","images/minusnew.png");

  } else {
	  
    x.style.display = "none";
	$("#plus2").attr("src","images/plusnew.png");
	
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
	    <th>Precio</th>
	    <th><?php echo $lang['purchase']; ?></th>
	    <th><?php echo $lang['pur-quota']; ?></th>
	    <th><?php echo $lang['global-dispense']; ?></th>
	    <th><?php echo $lang['title-stock']; ?></th>
	    <th><?php echo $lang['stash']; ?></th>
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

		$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10 OR movementTypeid = 22)";
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
	   <td class='relative'>$barCodeImg</td>
	   <td class='relative'>%s</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);
	    	
	  echo $purchase_row;
	  
	}
}
		
		
?>

	 </tbody>
	 </table>
<br /><br />
	 </span>	 
<?php
		
		} else {
			
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

	 <table class="default" id="t<?php echo $j; ?>">
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
	    <th><?php echo $lang['title-stock']; ?></th>
	    <th><?php echo $lang['stash']; ?></th>
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

		$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10 OR movementTypeid = 22)";
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
	   <td class='relative'>$barCodeImg</td>
	   <td class='relative'>%s</td>
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
	   <td class='relative'>$barCodeImg</td>
	   <td class='relative'>%s</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'] * $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $purchase['purchaseid'], $inMenu, $inMenu, $commentRead);
	  	
  	}
	    	
	  echo $purchase_row;
	  
	}
	
	$j++;
} else {
	$j++;	
}		
			
?>

	 </tbody>
	 </table>
	<br /><br /></span> 
<?php
		}
	

	}
