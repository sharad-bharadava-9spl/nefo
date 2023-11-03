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

	// Check if new Filter value was submitted, and assign query variable accordingly
	if (isset($_POST['filter'])) {
				
		$filterVar = $_POST['filter'];
		
		if ($filterVar == 100) {
			
			$limitVar = "LIMIT 100";
			$optionList = "<option value='$filterVar'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 250) {
			
			$limitVar = "LIMIT 250";
			$optionList = "<option value='$filterVar'>{$lang['last']} 250</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 500) {
			
			$limitVar = "LIMIT 500";
			$optionList = "<option value='$filterVar'>{$lang['last']} 500</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>";
			
		} else {
			
			// Grab month and year number
			$month = substr($filterVar, 0, strrpos($filterVar, '-'));	
			$year = substr($filterVar, strrpos($filterVar, '-') + 1);
			
			$timeLimit = "WHERE MONTH(movementtime) = $month AND YEAR(movementtime) = $year";
			
			$optionList = "<option value='filterVar'>$filterVar</option>
				<option value='100'>{$lang['last']} 100</option>
				<option value='250'>{$lang['last']} 250</option>
				<option value='500'>{$lang['last']} 500</option>";		
		}
			
	} else {
		
		$limitVar = "LIMIT 100";
		
		$optionList = "<option value=''>{$lang['filter']}</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
	
	// Query to look up movements
	$selectExpenses = "SELECT movementid, movementtime, type, purchaseid, quantity, movementTypeid, comment FROM productmovements $timeLimit ORDER by movementtime DESC $limitVar";

	$result2 = mysql_query($selectExpenses)
		or handleError($lang['error-donationload'],"Error loading expense from db: " . mysql_error());
		
	// Create month-by-month split
	$findStartDate = "SELECT movementtime FROM productmovements ORDER BY movementtime ASC LIMIT 1";
	
	$startResult = mysql_query($findStartDate)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

	$row = mysql_fetch_array($startResult);
		$startDate = date('01-m-Y', strtotime($row['movementtime']));
		$endDate = date('01-m-Y');
		$endDateShort = date('m-Y', strtotime($endDate));
		
		
	if ($endDateShort != $filterVar) {
		$optionList .= "<option value='$endDateShort'>$endDateShort</option>";
	}
	
	$genDateFull = date('01-m-Y', strtotime($endDate));
	$genDate = date('m-Y', strtotime($genDateFull));
	
	while (strtotime($genDateFull) > strtotime($startDate)) {
		
		$genDateFull = date('01-m-Y', strtotime("$genDateFull - 1 month"));
		$genDate = date('m-Y', strtotime($genDateFull));
		
		// Exclude option if already selected
		if ($genDate != $filterVar) {
			$optionList .= "<option value='$genDate'>$genDate</option>";
		}

	}
	
	$deleteDonationScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
$("#xllink").click(function(){

	  $("#mainTable").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Movimientos",
	    filename: "Movimientos" //do not include extension

	  });

	});
		    
		    
			$('#cloneTable').width($('#mainTable').width());


		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;

			
	pageStart($lang['add-movements'], NULL, $deleteDonationScript, "pmembership", NULL, $lang['add-movements'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
        <form action='' method='POST'>
	     <select id='filter' name='filter' onchange='this.form.submit()'>
	      <?php echo $optionList; ?>
		 </select>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
        </form>
       </td>
      </tr>
     </table>

<?php

	$y = 0;
	
while ($donation = mysql_fetch_array($result2)) {
	
	$dTime = date("d-m-Y", strtotime($donation['movementtime']));
	$dTimeSQL = date("Y-m-d", strtotime('-1 day', strtotime($donation['movementtime'])));
	
	if ($dTime != $currDate) {
		
		$nDate = date("Y-m-d", strtotime($currDate));
		
		
		
	  	echo "</tbody></table>";
		echo "<br /><br /><h3 class='title'>{$dTime}</h3>";
		
			echo <<<EOD
	 <table class="default" id="mainTable">
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th>{$lang['global-time']}</th>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['extracts-description']}</th>
	    <th>{$lang['global-quantity']}</th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
EOD;
			
	  		$currDate =  date("d-m-Y", strtotime($donation['movementtime']));

	}
	
	
	$donationid = $donation['movementid'];
	$donationtime = $donation['movementtime'];
	$type = $donation['type'];
	$purchaseid = $donation['purchaseid'];
	$quantity = $donation['quantity'];
	$donationTypeid = $donation['movementTypeid'];
	$formattedDate = date("d M H:i", strtotime($donation['movementtime'] . "+$offsetSec seconds"));
	
	$selectProdID = "SELECT category, productid FROM purchases WHERE purchaseid = '$purchaseid'";
	
	$resultProdID = mysql_query($selectProdID)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
		
	$fRow = mysql_fetch_array($resultProdID);
		$productid = $fRow['productid'];
		$category = $fRow['category'];
		
	if ($category == 1) {
		
		$selectName = "SELECT name, breed2 FROM flower WHERE flowerid = '$productid'";
		
		$resultName = mysql_query($selectName)
			or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
			
		$nRow = mysql_fetch_array($resultName);
			$name = $nRow['name'];
			$breed2 = $nRow['breed2'];
			$categoryName = "WEED";
		
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		} else {
			$name = $name;
		}

			
	} else if ($category == 2) {
		
		$selectName = "SELECT name FROM extract WHERE extractid = '$productid'";
		
		$resultName = mysql_query($selectName)
			or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
			
		$nRow = mysql_fetch_array($resultName);
			$name = $nRow['name'];		
			$categoryName = "RESINA";
		
	} else {
		
		$selectName = "SELECT name FROM products WHERE productid = '$productid'";
		
		$resultName = mysql_query($selectName)
			or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
			
		$nRow = mysql_fetch_array($resultName);
			$name = $nRow['name'];
			
		$selectCatName = "SELECT name FROM categories WHERE id = '$category'";
		
		$resultName = mysql_query($selectCatName)
			or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
			
		$cRow = mysql_fetch_array($resultName);
			$categoryName = $cRow['name'];
		
	}

	
	if ($donationTypeid == 17 || $donationTypeid == 18 || $donationTypeid == 19 || $donationTypeid == 20 ) {
		$rowclass = " class='grey' ";
	} else if ($type == 1) {
		$rowclass = " class='green' ";
	} else if ($type == 2) {
		$rowclass = " class='red' ";
	} else {
		$rowclass = "";
	}
	
	if ($donation['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$donationid' /><div id='helpBox$donationid' class='helpBox'>{$donation['comment']}</div>
		                <script>
		                  	$('#comment$donationid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$donationid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$donationid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}


	
	// Look up movement name
      	if ($_SESSION['lang'] == 'es') {
			$selectMovementName = "SELECT movementNamees FROM productmovementtypes WHERE movementTypeid = '$donationTypeid'";
			$resultName = mysql_query($selectMovementName)
				or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
			$row = mysql_fetch_array($resultName);
				$donationName = $row['movementNamees'];
		} else {
			$selectMovementName = "SELECT movementNameen FROM productmovementtypes WHERE movementTypeid = '$donationTypeid'";
			$resultName = mysql_query($selectMovementName)
				or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
			$row = mysql_fetch_array($resultName);
				$donationName = $row['movementNameen'];
		}

	
	$donation_row =	sprintf("
  	  <tr%s>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: right;' href='purchase.php?purchaseid=%d'>%0.02f g</td>
  	   <td class='centered clickableRow' style='position: relative;' href='purchase.php?purchaseid=%d'>$commentRead</td>
	  </tr>",
	  $rowclass, $purchaseid, $formattedDate, $purchaseid, $categoryName, $purchaseid, $name, $purchaseid, $donationName, $purchaseid, $quantity, $purchaseid
	  );
	  echo $donation_row;
	  
	  $y++;
	  
  }
?>

	 </tbody>
	 </table>

   
<?php displayFooter();