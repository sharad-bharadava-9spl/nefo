<?php
	
	// Here we should check if the openings/closings were fully done, and then do a flag!
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	
	// Day by day, but start with today for experimentation
	
	// First open day, opened by
	// Closed shift #1, closed by
	// Opened shift #2, opened by
	
	// Query to look up openings
	
	// Also look up closings!
	
	$selectOpenings = "SELECT 'Cerrar dia' AS type, 'CloseDay' AS typeshort, closingid AS openingid, closingtime AS shiftStart, cashintill AS tillBalance, tillDelta, closedby AS openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract FROM closing UNION ALL SELECT 'Abrir dia' AS type, 'OpenDay' AS typeshort, openingid, openingtime AS shiftStart, tillBalance, tillDelta, openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract FROM opening UNION ALL SELECT 'Cerrar turno' AS type, 'CloseShift' AS typeshort, closingid AS openingid, shiftEnd AS shiftStart, cashintill AS tillBalance, tillDelta, closedby AS openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract FROM shiftclose UNION ALL SELECT 'Comenzar turno' AS type, 'StartShift' AS typeshort, openingid, openingtime AS shiftStart, tillBalance, tillDelta, openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract FROM shiftopen ORDER by shiftStart DESC";
		try
		{
			$results = $pdo3->prepare("$selectOpenings");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	$deleteShiftScript = <<<EOD
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
					1: {
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
					},
					8: {
						sorter: "currency"
					},
					9: {
						sorter: "currency"
					},
					10: {
						sorter: "currency"
					}
				}
			}); 

		}); 

					    
function delete_shift(shiftid,shifttype) {
	if (confirm("{$lang['expense-deleteconfirm']}")) {
				window.location = "uTil/delete-shift.php?shiftid=" + shiftid + "&shifttype=" + shifttype;
				}
}
EOD;

	pageStart($lang['shifts'], NULL, $deleteShiftScript, "pexpenses", "admin", $lang['shiftsC'] . " DE DISPENSARIO", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['responsible']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-delta']; ?></th>
	    <th><?php echo $lang['global-flowers']; ?></th>
	    <th><?php echo $lang['global-delta']; ?></th>
	    <th><?php echo $lang['global-extracts']; ?></th>
	    <th><?php echo $lang['global-delta']; ?></th>
<!--	    <th><?php echo $lang['completed']; ?>?</th>-->
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	  $i = 0;
		while ($opening = $results->fetch()) {
	

	$type = $opening['type'];
	$shiftStartOrig = $opening['shiftStart'];
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
		
		if ($_SESSION['openAndClose'] > 2) {
			
			$checkFirst = "SELECT dayClosed FROM opening WHERE openingtime < '$shiftStartOrig' ORDER BY openingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$checkFirst");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$dayClosed = $row['dayClosed'];
				
			if ($dayClosed == 2) {
				
				$completedFlag = "<img src='images/checkmark.png' width='15' />";
				
			} else {
				
				$completedFlag = "<span class='redColour'><strong>NO</strong></span>";
				
			}

		}
		
	} else if ($typeshort == 'OpenDay') {
		
		$shifttype = 2;
		$type = $lang['openday'];
		
		$selectRows = "SELECT COUNT(firstDayOpen) FROM opening";
		$rowCount = $pdo3->query("$selectRows")->fetchColumn();
		
		$checkFirst = "SELECT firstDayOpen FROM opening";
		try
		{
			$result = $pdo3->prepare("$checkFirst");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
				
		if ($rowCount == 1) {
			
			// Only 1 opening exists - first opening
		$row = $result->fetch();
				$firstDayOpen = $row['firstDayOpen'];
				
			if ($firstDayOpen == 2) {
				
				$completedFlag = "<img src='images/checkmark.png' width='15' />";
				
			} else {
				
				$completedFlag = "<span class='redColour'><strong>NO</strong></span>";
				
			}
			
		} else {
			
			// Not first opening
			$checkFirst = "SELECT dayOpened FROM closing WHERE closingtime < '$shiftStartOrig' ORDER BY closingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$checkFirst");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$dayOpened = $row['dayOpened'];
			
			if ($dayOpened == 2) {
				
				$completedFlag = "<img src='images/checkmark.png' width='15' />";
				
			} else {
				
				$completedFlag = "<span class='redColour'><strong>NO</strong></span>";
				
			}
			
		}
				
		
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
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #a80082;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #a80082;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #a80082;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #a80082;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #a80082;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #a80082;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <!--<td style='border-bottom: 4px solid #a80082;'>%s</td>-->
		  </tr>
",
		  $shifttype, $openingid, $type, $shifttype, $openingid, $shiftStart, $shifttype, $openingid, $user, $shifttype, $openingid, $prodStock, $shifttype, $openingid, $stockDelta, $shifttype, $openingid, $prodStockFlower, $shifttype, $openingid, $stockDeltaFlower, $shifttype, $openingid, $prodStockExtract, $shifttype, $openingid, $stockDeltaExtract, $completedFlag
		  );
		  
	} else {
		
	
		$expense_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <!--<td>%s</td>-->
		  </tr>",
		  $shifttype, $openingid, $type, $shifttype, $openingid, $shiftStart, $shifttype, $openingid, $user, $shifttype, $openingid, $prodStock, $shifttype, $openingid, $stockDelta, $shifttype, $openingid, $prodStockFlower, $shifttype, $openingid, $stockDeltaFlower, $shifttype, $openingid, $prodStockExtract, $shifttype, $openingid, $stockDeltaExtract, $completedFlag
		  );
		  
	}
	
	echo $expense_row;
	
	$i++;
	
  }
?>

	 </tbody>
	 </table>
	 
<?php  displayFooter(); ?>
