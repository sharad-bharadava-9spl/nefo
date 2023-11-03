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
		
		echo "<div style='height: 297mm; width: 210mm;'>";
				
		foreach ($_POST['toPrint'] as $printit) {
			
			if ($printit['noOfCodes'] != '') {
				
				$noOfCodes = $printit['noOfCodes'];
				$name = $printit['name'];
				$producttype = $printit['producttype'];
				$barcode = $printit['barcode'];
				
				while ($noOfCodes > 0) {
					
					echo <<<EOD
<div style='float: left; width: 70mm; height: 31mm; text-align: center; overflow: hidden; padding-top: 1mm; line-height: 0.6em;'>
<img alt='testing' src='scripts/barcode.php?text=$barcode&size=40&codetype=code25' /><br />
<span class='smallerfont4'>$barcode</span><br /><br />
<span class='biggerfont'><strong>$name</strong></span><br /><br />
$producttype
</div>
					
EOD;
					
					$noOfCodes--;
				
				}
				
			
			
			}
			
		}
		
		echo "</div>";
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		exit();
		
	}
	
	// Query to look up open products
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchasePrice, salesPrice, provider, growType FROM purchases WHERE closedAt IS NULL AND (category = 1 OR category = 2) ORDER by category ASC, purchaseDate DESC";

	$resultOpen = mysql_query($selectOpenPurchases)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());

	
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
	    <th><?php echo $lang['bag-size']; ?></th>
	    <th><?php echo $lang['stickers']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  
	  
$i = 0;
while ($purchase = mysql_fetch_array($resultOpen)) {

	$productid = $purchase['productid'];
	$purchaseid = $purchase['purchaseid'];
	$provider = $purchase['provider'];
	$growType = $purchase['growType'];
	
	$growDetails = "SELECT name FROM providers WHERE id = $provider";
	
	$result = mysql_query($growDetails)
		or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$providerName = $row['name'];


	if ($growType != 0) {
		
		$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growType";
		
		$result = mysql_query($growDetails)
			or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
			
		$row = mysql_fetch_array($result);
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
	}		

		$selectProduct = "SELECT {$prodSelect}, purchases p WHERE ({$productid} = {$prodJoin})";
				
		$productResult = mysql_query($selectProduct)
			or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
			
	    $row = mysql_fetch_array($productResult);
			$name = $row['name'];
			$breed2 = $row['breed2'];
			$productnumber = $row['productnumber'];
			$producttype = $row['producttype'];
		
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}		
		
	// You need 3 for each product: 5€, 10€, Loose/granel
		
	$purchase_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s <span class='smallerfont'>%s</span></td>
  	   <td>%s</td>
  	   <td class='right'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='right'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td>%s</td>
  	   <td class='right'>10<span class='smallerfont3'> &euro;</span></td>
  	   <td>
  	    <input type='number' name='toPrint[%d][noOfCodes]' class='twoDigit' />
    	<input type='hidden' name='toPrint[%d][name]' value='%s' />
    	<input type='hidden' name='toPrint[%d][growtype]' value='%s' />
    	<input type='hidden' name='toPrint[%d][producttype]' value='%s' />
    	<input type='hidden' name='toPrint[%d][purchaseid]' value='%d' />
     	<input type='hidden' name='toPrint[%d][productnumber]' value='%d' />
  	    <input type='hidden' name='toPrint[%d][bagPrice]' value='10' />
  	    <input type='hidden' name='toPrint[%d][barcode]' value='%s' />
  	   </td>
	  </tr>",
	  $purchaseCategory, $name, $growtype, $providerName, $purchase['purchasePrice'], $purchase['salesPrice'], sprintf('%03d', $productnumber) . '10' . sprintf('%03d', $provider) . date("dm") . sprintf('%03d', $purchaseid), $i, $i, $name, $i, $growtype, $i, $producttype, $i, $purchaseid, $i, $productnumber, $i, $i, sprintf('%03d', $productnumber) . '10' . sprintf('%03d', $provider) . date("dm") . sprintf('%03d', $purchaseid));
	  
	  $i++;
	  
	$purchase_row .=	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s <span class='smallerfont'>%s</span></td>
  	   <td>%s</td>
  	   <td class='right'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='right'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td>%s</td>
  	   <td class='right'>5<span class='smallerfont3'> &euro;</span></td>
  	   <td>
  	    <input type='number' name='toPrint[%d][noOfCodes]' class='twoDigit' />
    	<input type='hidden' name='toPrint[%d][name]' value='%s' />
    	<input type='hidden' name='toPrint[%d][growtype]' value='%s' />
    	<input type='hidden' name='toPrint[%d][producttype]' value='%s' />
    	<input type='hidden' name='toPrint[%d][purchaseid]' value='%d' />
     	<input type='hidden' name='toPrint[%d][productnumber]' value='%d' />
  	    <input type='hidden' name='toPrint[%d][bagPrice]' value='5' />
  	    <input type='hidden' name='toPrint[%d][barcode]' value='%s' />
  	   </td>
	  </tr>",
	  $purchaseCategory, $name, $growtype, $providerName, $purchase['purchasePrice'], $purchase['salesPrice'], sprintf('%03d', $productnumber) . '05' . sprintf('%03d', $provider) . date("dm") . sprintf('%03d', $purchaseid), $i, $i, $name, $i, $growtype, $i, $producttype, $i, $purchaseid, $i, $productnumber, $i, $i, sprintf('%03d', $productnumber) . '05' . sprintf('%03d', $provider) . date("dm") . sprintf('%03d', $purchaseid));
	  
	  $i++;
	  
	$purchase_row .=	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s <span class='smallerfont'>%s</span></td>
  	   <td>%s</td>
  	   <td class='right'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='right'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td>%s</td>
  	   <td class='right'>Granel</td>
  	   <td>
  	    <input type='number' name='toPrint[%d][noOfCodes]' class='twoDigit' />
    	<input type='hidden' name='toPrint[%d][name]' value='%s' />
    	<input type='hidden' name='toPrint[%d][growtype]' value='%s' />
    	<input type='hidden' name='toPrint[%d][producttype]' value='%s' />
    	<input type='hidden' name='toPrint[%d][purchaseid]' value='%d' />
     	<input type='hidden' name='toPrint[%d][productnumber]' value='%d' />
  	    <input type='hidden' name='toPrint[%d][bagPrice]' value='0' />
  	    <input type='hidden' name='toPrint[%d][barcode]' value='%s' />
  	   </td>
	  </tr><tr><td colspan='8' style='background-color: #ddd;'></td></tr>",
	  $purchaseCategory, $name, $growtype, $providerName, $purchase['purchasePrice'], $purchase['salesPrice'], sprintf('%03d', $productnumber) . '00' . sprintf('%03d', $provider) . date("dm") . sprintf('%03d', $purchaseid), $i, $i, $name, $i, $growtype, $i, $producttype, $i, $purchaseid, $i, $productnumber, $i, $i, sprintf('%03d', $productnumber) . '00' . sprintf('%03d', $provider) . date("dm") . sprintf('%03d', $purchaseid));
	  
	  echo $purchase_row;

	  
	  $i++;
	  
}
?>

	 </tbody>
	 </table>
<button type="submit">Submit</button>
</form>


