<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_POST['submitted'])) {
		
		
		echo <<<EOD
<!DOCTYPE html> 
<html moznomarginboxes mozdisallowselectionprint>
 <head>
  <title>{$pageTitle}</title>
  <link href="css/styles11.css" rel="stylesheet" type="text/css" />
  <link href="css/jquery-ui.css" rel="stylesheet" type="text/css" />
  <script src="scripts/jquery-1.10.2.min.js"></script>
  <script src="scripts/jquery.validate.min.js"></script>
  <script src="scripts/additional-methods.min.js"></script>
  <script src="scripts/jquery-ui.js"></script>
  <script src="scripts/webcam.js"></script>
  <style>
   @page { margin: 0; }
   @print { margin: 0; }

  </style>
  
EOD;
		
//		echo "<div style='height: 297mm; width: 210mm;'>";
				
		foreach ($_POST['toPrint'] as $printit) {
			
			if ($printit['noOfCodes'] != '' && $printit['noOfCodes'] != 0) {
				
				$noOfCodes = $printit['noOfCodes'];
				$name = $printit['name'];
				$producttype = $printit['producttype'];
				$barcode = $printit['barcode'];
				
				while ($noOfCodes > 0) {
					
					echo <<<EOD
<div style='float: left; width: 84mm; height: 24mm; text-align: center; overflow: hidden; padding-top: 1mm; line-height: 1.5em; font-size: 17px; font-family: Tahoma; '>
<img alt='testing' src='scripts/barcode.php?text=$barcode&size=56' width='300' height='50' /><br />
<span class='biggerfont'>$name</span>
</div>
					
EOD;
					
					$noOfCodes--;
				
				}
				
			
			
			}
			
		}
		
		echo <<<EOD
<script type="text/javascript">
window.print();
setTimeout(function() {
    window.history.back();
    }, 50);
</script>
EOD;
		
//		echo "</div>";
		
		
		
		exit();
		
	}
	
	// Query to look up open products
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchasePrice, salesPrice, provider, growType FROM purchases WHERE closedAt IS NULL ORDER by category ASC, purchaseDate DESC";
		try
		{
			$results = $pdo3->prepare("$selectOpenPurchases");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

	
	pageStart($lang['print-barcodes'], NULL, NULL, "ppurchases", "purchases admin", $lang['print-barcodes'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>
<br />
<form action="" method="POST">
<input type="hidden" name="submitted" />

	 <table class="default nonhover">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['provider']; ?></th>
	    <th><?php echo $lang['purchase']; ?></th>
	    <th><?php echo $lang['global-dispense']; ?></th>
	    <th><?php echo $lang['barcode']; ?></th>
	    <th><?php echo $lang['stickers']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  
	  
$i = 0;
		while ($purchase = $results->fetch()) {

	$productid = $purchase['productid'];
	$purchaseid = $purchase['purchaseid'];
	$provider = $purchase['provider'];
	$growType = $purchase['growType'];
	
	$growDetails = "SELECT name FROM providers WHERE id = $provider";
		try
		{
			$result = $pdo3->prepare("$growDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$providerName = $row['name'];


	if ($growType != 0) {
		
		$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growType";
		try
		{
			$result = $pdo3->prepare("$growDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$growtype = "(" . $row['growtype'] . ")";
			
	} else {
		
		$growtype = "";
		
	}
		
	// Determine product type, and assign query variables accordingly
	if ($purchase['category'] == 1) {
		$purchaseCategory = $lang['global-flower'];
		$prodSelect = 'g.name, g.breed2, g.flowernumber AS productnumber, g.flowertype AS producttype FROM flower g';
		$prodJoin = 'g.flowerid AND p.category = 1';
	} else if ($purchase['category'] == 2) {
		$purchaseCategory = $lang['global-extract'];
		$prodSelect = 'h.name, h.extractnumber AS productnumber, h.extracttype AS producttype FROM extract h';
		$prodJoin = 'h.extractid AND p.category = 2';
	} else {
		
		// Query to look for category
		$categoryDetails = "SELECT name FROM categories WHERE id = {$purchase['category']}";
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
			
		$prodSelect = "pr.name, '' AS productnumber, '' AS producttype FROM products pr";
		$prodJoin = "pr.productid AND p.category = {$purchase['category']}";
			
	}

		$selectProduct = "SELECT {$prodSelect}, purchases p WHERE ({$productid} = {$prodJoin})";
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
			$breed2 = $row['breed2'];
			$productnumber = $row['productnumber'];
			$producttype = $row['producttype'];
		
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}		
		
		$barCode = sprintf('%03d', $purchaseid);
		
		$updateBarcode = "UPDATE purchases SET barCode = '$barCode' WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$updateBarcode")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		
	// You need 3 for each product: 5$_SESSION['currencyoperator'], 10$_SESSION['currencyoperator'], Loose/granel
		
	$purchase_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s <span class='smallerfont'>%s</span></td>
  	   <td>%s</td>
  	   <td class='right'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td class='right'>%0.2f<span class='smallerfont3'> {$_SESSION['currencyoperator']} /g</span></td>
  	   <td>%s</td>
  	   <td>
  	    <input type='number' name='toPrint[%d][noOfCodes]' class='twoDigit defaultinput' value='0' />
    	<input type='hidden' name='toPrint[%d][name]' value='%s' />
    	<input type='hidden' name='toPrint[%d][producttype]' value='%s' />
  	    <input type='hidden' name='toPrint[%d][barcode]' value='%s' />
  	   </td>
	  </tr>",
	  $purchaseCategory, $name, $growtype, $providerName, $purchase['purchasePrice'], $purchase['salesPrice'], $barCode, $i, $i, $name, $i, $producttype, $i, $barCode);
	  
	  $i++;

	  
	  echo $purchase_row;

	  
	  
}
?>

	 </tbody>
	 </table>
<button type="submit">Submit</button>
</form>


