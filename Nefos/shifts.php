<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	
	// Day by day, but start with today for experimentation
	
	// First open day, opened by
	// Closed shift #1, closed by
	// Opened shift #2, opened by
	
	// Query to look up openings
	
	// Also look up closings!
	
	$selectOpenings = "SELECT 'Cerrar dia' AS type, 'CloseDay' AS typeshort, closingid AS openingid, closingtime AS shiftStart, cashintill AS tillBalance, tillDelta, closedby AS openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract FROM closing UNION ALL SELECT 'Abrir dia' AS type, 'OpenDay' AS typeshort, openingid, openingtime AS shiftStart, tillBalance, tillDelta, openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract FROM opening UNION ALL SELECT 'Cerrar turno' AS type, 'CloseShift' AS typeshort, closingid AS openingid, shiftEnd AS shiftStart, cashintill AS tillBalance, tillDelta, closedby AS openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract FROM shiftclose UNION ALL SELECT 'Comenzar turno' AS type, 'StartShift' AS typeshort, openingid, openingtime AS shiftStart, tillBalance, tillDelta, openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract FROM shiftopen ORDER by shiftStart DESC";

	$result = mysql_query($selectOpenings)
		or handleError("Error loading expenses from database.","Error loading expense from db: " . mysql_error());
		
	$deleteShiftScript = <<<EOD
function delete_shift(shiftid,shifttype) {
	if (confirm("{$lang['expense-deleteconfirm']}")) {
				window.location = "uTil/delete-shift.php?shiftid=" + shiftid + "&shifttype=" + shifttype;
				}
}
EOD;

	pageStart($lang['shifts'], NULL, $deleteShiftScript, "pexpenses", "admin", $lang['shiftsC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>
	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['responsible']; ?></th>
	    <th><?php echo $lang['global-till']; ?></th>
	    <th><?php echo $lang['global-delta']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-delta']; ?></th>
	    <th><?php echo $lang['global-flowers']; ?></th>
	    <th><?php echo $lang['global-delta']; ?></th>
	    <th><?php echo $lang['global-extracts']; ?></th>
	    <th><?php echo $lang['global-delta']; ?></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

while ($opening = mysql_fetch_array($result)) {
	
	$type = $opening['type'];
	$typeshort = $opening['typeshort'];
	$openingid = $opening['openingid'];
	$shiftStart = date("d-m-Y H:i", strtotime($opening['shiftStart'] . "+$offsetSec seconds"));
	$tillBalance = $opening['tillBalance'];
	$tillDelta = $opening['tillDelta'];
	$openedby = $opening['openedby'];
	$prodStock = $opening['prodStock'];
	$prodStockFlower = $opening['prodStockFlower'];
	$prodStockExtract = $opening['prodStockExtract'];
	$stockDelta = $opening['stockDelta'];
	$stockDeltaFlower = $opening['stockDeltaFlower'];
	$stockDeltaExtract = $opening['stockDeltaExtract'];
	
	$user = getUser($openedby);
		
	if ($typeshort == 'CloseDay') {
		$shifttype = 1;
		$type = $lang['closeday-main'];
	} else if ($typeshort == 'OpenDay') {
		$shifttype = 2;
		$type = $lang['openday'];
	} else if ($typeshort == 'CloseShift') {
		$shifttype = 3;
		$type = $lang['close-shift'];
	} else if ($typeshort == 'StartShift') {
		$shifttype = 4;
		$type = $lang['start-shift'];
	}
	
	if ($tillDelta < 0) {
		
		$tillColour = 'negative';
		
	} else if ($tillDelta > 0) {
		
		$tillColour = 'positive';
		
	} else {
		
		$tillColour = '';	
		
	}

	
	if ($shifttype == 2) {
		
		$expense_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left; border-bottom: 4px solid #a80082;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left; border-bottom: 4px solid #a80082;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left; border-bottom: 4px solid #a80082;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #a80082;'>%0.2f <span class='smallerfont'>&euro;</span></td>
	  	   <td class='clickableRow %s' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #a80082;'>%0.2f <span class='smallerfont'>&euro;</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #a80082;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #a80082;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #a80082;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #a80082;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #a80082;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #a80082;'>%0.2f <span class='smallerfont'>g.</span></td>
		  </tr>
",
		  $shifttype, $openingid, $type, $shifttype, $openingid, $shiftStart, $shifttype, $openingid, $user, $shifttype, $openingid, $tillBalance, $tillColour, $shifttype, $openingid, $tillDelta, $shifttype, $openingid, $prodStock, $shifttype, $openingid, $stockDelta, $shifttype, $openingid, $prodStockFlower, $shifttype, $openingid, $stockDeltaFlower, $shifttype, $openingid, $prodStockExtract, $shifttype, $openingid, $stockDeltaExtract
		  );
		  
	} else {
		
	
		$expense_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>&euro;</span></td>
	  	   <td class='clickableRow %s' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>&euro;</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
		  </tr>",
		  $shifttype, $openingid, $type, $shifttype, $openingid, $shiftStart, $shifttype, $openingid, $user, $shifttype, $openingid, $tillBalance, $tillColour, $shifttype, $openingid, $tillDelta, $shifttype, $openingid, $prodStock, $shifttype, $openingid, $stockDelta, $shifttype, $openingid, $prodStockFlower, $shifttype, $openingid, $stockDeltaFlower, $shifttype, $openingid, $prodStockExtract, $shifttype, $openingid, $stockDeltaExtract
		  );
		  
	}
	
	echo $expense_row;
	
  }
?>

	 </tbody>
	 </table>
	 
<?php  displayFooter(); ?>
