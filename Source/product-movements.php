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
		
		$optionList = "<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
	
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "WHERE DATE(movementtime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$limitVar = "";
			
	}
	
	// Query to look up movements
	$selectExpenses = "SELECT movementid, movementtime, type, purchaseid, quantity, movementTypeid, comment, user_id FROM productmovements $timeLimit ORDER by movementtime DESC $limitVar";
		try
		{
			$resultsL = $pdo3->prepare("$selectExpenses");
			$resultsL->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	// Create month-by-month split
	$findStartDate = "SELECT movementtime FROM productmovements ORDER BY movementtime ASC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$findStartDate");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
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
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });	    
	  
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
	 <table class='default' id='cloneTable' style='text-align: left;'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a><br /><br />
		<div style='display: inline-block; border: 2px solid #5aa242; padding: 10px;'>
		&nbsp;<strong><?php echo $lang['filter']; ?></strong><br /> 
        <form action='' method='POST' style='margin-top: 3px;'>
	     <select id='filter' name='filter' onchange='this.form.submit()'>
	      <?php echo $optionList; ?>
		 </select>
        </form>
        <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" placeholder="{$lang['from-date']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" placeholder={$lang['to-date']}" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;

	}
?>
        </form>
        </div>
       </td>
      </tr>
     </table>

<br />

<center><h1><?php echo $lang['summary']; ?></h1></center>
	<table class='default'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['global-flower']; ?></th>
	    <th><?php echo $lang['global-extract']; ?></th>
<?php

	$query = "SELECT id, name, type FROM categories WHERE id > 2 ORDER BY type DESC";
	try
	{
		$results = $pdo3->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
		$catlist[] = 1;
		$catlist[] = 2;

	while ($row = $results->fetch()) {
		
		$id = $row['id'];
		$name = $row['name'];
		$type = $row['type'];
		
		if ($type == 1) {
			$type = "(g)";
		} else {
			$type = "(u)";
		}
		
		$catlist[] = $id;
		
		echo "<th>$name</th>";
		$noOfCats++;
	}
	
		// $catlist[] = 0;
	
	echo "</tr></thead><tbody>";
	

		
	if ($_SESSION['lang'] == 'en') {
		
		$query = "SELECT movementTypeid, type, movementNameen AS movementname FROM productmovementtypes WHERE movementTypeid < 16 OR movementTypeid = 21 OR movementTypeid = 22 ORDER BY type ASC";
		
	} else {
		
		$query = "SELECT movementTypeid, type, movementNamees AS movementname FROM productmovementtypes WHERE movementTypeid < 16 OR movementTypeid = 21 OR movementTypeid = 22 ORDER BY type ASC";
		
	}
	
		try
		{
			$results = $pdo3->prepare("$query");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		while ($row = $results->fetch()) {
						
			$id = $row['movementTypeid'];
			$type = $row['type'];
			$movementname = $row['movementname'];
			
			if ($type == 1) {
				$rowclass = " class='green' ";
			} else if ($type == 2) {
				$rowclass = " class='red' ";
			}
			
			
			echo "<tr$rowclass><td>$movementname</td>";
			
			// Query to look up movements - one TD per category. First 1, then 2, then all categories.
			foreach($catlist as $item) {
			
				if ($timeLimit == '') {
					
					$query2 = "SELECT SUM(quantity) FROM productmovements WHERE movementTypeid = '$id' AND category = '$item' ORDER by movementtime DESC $limitVar";
					
				} else {
					
					$query2 = "SELECT SUM(quantity) FROM productmovements $timeLimit AND movementTypeid = '$id' AND category = '$item' ORDER by movementtime DESC $limitVar";
					
				}
				
				try
				{
					$results2 = $pdo3->prepare("$query2");
					$results2->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$row2 = $results2->fetch();
					$quantity = $row2['SUM(quantity)'];
					
				echo "<td class='right'>$quantity</td>";
				
			}
			
				echo "</tr>";
				
				
			
		}
			

?>
	  </tbody>
	 </table>
	 <br />
	 <br />
	 <center><h1><?php echo $lang['global-details']; ?></h1></center>
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['extracts-description']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>

<?php

	while ($donation = $resultsL->fetch()) {
	
		$donationid = $donation['movementid'];
		$donationtime = $donation['movementtime'];
		$type = $donation['type'];
		$purchaseid = $donation['purchaseid'];
		$quantity = $donation['quantity'];
		$donationTypeid = $donation['movementTypeid'];
		$user = $donation['user_id'];		
		$formattedDate = date("d M H:i", strtotime($donation['movementtime'] . "+$offsetSec seconds"));
		
		
		if ($user == '0' || $user == '999999') {
			
			$operator = '';
			
		} else {
			
			$operator = getOperator($user);
			
		}
		
		$selectProdID = "SELECT category, productid FROM purchases WHERE purchaseid = '$purchaseid'";
		try
		{
			$result = $pdo3->prepare("$selectProdID");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$fRow = $result->fetch();
		$productid = $fRow['productid'];
		$category = $fRow['category'];
		
	if ($category == 1) {
		
		$selectName = "SELECT name, breed2 FROM flower WHERE flowerid = '$productid'";
		try
		{
			$result = $pdo3->prepare("$selectName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$nRow = $result->fetch();
			$name = $nRow['name'];
			$breed2 = $nRow['breed2'];
			$categoryName = $lang['global-flower'];
		
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		} else {
			$name = $name;
		}

			
	} else if ($category == 2) {
		
		$selectName = "SELECT name FROM extract WHERE extractid = '$productid'";
		try
		{
			$result = $pdo3->prepare("$selectName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$nRow = $result->fetch();
			$name = $nRow['name'];		
			$categoryName = $lang['global-extracts'];
		
	} else {
		
		$selectName = "SELECT name FROM products WHERE productid = '$productid'";
		try
		{
			$result = $pdo3->prepare("$selectName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$nRow = $result->fetch();
			$name = $nRow['name'];
			
		$selectCatName = "SELECT name FROM categories WHERE id = '$category'";
		try
		{
			$result = $pdo3->prepare("$selectCatName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$cRow = $result->fetch();
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
		try
		{
			$result = $pdo3->prepare("$selectMovementName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$donationName = $row['movementNamees'];
		} else {
			$selectMovementName = "SELECT movementNameen FROM productmovementtypes WHERE movementTypeid = '$donationTypeid'";
		try
		{
			$result = $pdo3->prepare("$selectMovementName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$donationName = $row['movementNameen'];
		}

	
	$donation_row =	sprintf("
  	  <tr%s>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: right;' href='purchase.php?purchaseid=%d'>%0.02f g</td>
  	   <td class='centered clickableRow' style='position: relative;' href='purchase.php?purchaseid=%d'>$commentRead</td>
	  </tr>",
	  $rowclass, $purchaseid, $formattedDate, $purchaseid, $operator, $purchaseid, $categoryName, $purchaseid, $name, $purchaseid, $donationName, $purchaseid, $quantity, $purchaseid
	  );
	  echo $donation_row;
	  
	  $y++;
	  
  }
?>

	 </tbody>
	 </table>

   
<?php displayFooter();