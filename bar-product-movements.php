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
	$selectExpenses = "SELECT movementid, movementtime, type, purchaseid, quantity, movementTypeid, comment FROM b_productmovements $timeLimit ORDER by movementtime DESC $limitVar";
		try
		{
			$results = $pdo3->prepare("$selectExpenses");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	// Create month-by-month split
	$findStartDate = "SELECT movementtime FROM b_productmovements ORDER BY movementtime ASC LIMIT 1";
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
	
	    $(document).ready(function() {
		    
		    
$("#xllink").click(function(){

	  $("#mainTable").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Movimientos",
	    filename: "Movimientos" //do not include extension

	  });

	});
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
		    
			$('#cloneTable').width($('#mainTable').width());


		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;

			
	pageStart($lang['add-movements'], NULL, $deleteDonationScript, "pmembership", 'dev-align-center', $lang['add-movements'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
	<center><div id="filterbox">
       <div class="boxcontent">
      
        <form action='' method='POST'>
	     <select id='filter' name='filter' class='defaultinput' onchange='this.form.submit()'>
	      <?php echo $optionList; ?>
		 </select>
        
        </form>
     	</div>
      
     </div>
</center><br>
 <a href="#" onclick="loadExcel();"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
<?php

	$y = 0;
		$datai = 2;
		$x = 3;
		while ($donation = $results->fetch()) {
	
	$dTime = date("d-m-Y", strtotime($donation['movementtime']));
	$dTimeSQL = date("Y-m-d", strtotime('-1 day', strtotime($donation['movementtime'])));
	
	if ($dTime != $currDate) {
		
		$nDate = date("Y-m-d", strtotime($currDate));
		
		   if($datai > 2){
	   	    $datai  = $datai+1;
	   	    $x = $x+1;


	   }
		
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
	
	$selectProdID = "SELECT category, productid FROM b_purchases WHERE purchaseid = '$purchaseid'";
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

		
		$selectName = "SELECT name FROM b_products WHERE productid = '$productid'";
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
			
		$selectCatName = "SELECT name FROM b_categories WHERE id = '$category'";
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
  	   <td class='clickableRow' href='b_purchase.php?bar-purchase=%d'>%s</td>
  	   <td class='left clickableRow' href='bar-purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='bar-purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='bar-purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: right;' href='bar-purchase.php?purchaseid=%d'>%0.02f g</td>
  	   <td class='centered clickableRow' href='bar-purchase.php?purchaseid=%d'><span class='relativeitem'>$commentRead</span<</td>
	  </tr>",
	  $rowclass, $purchaseid, $formattedDate, $purchaseid, $categoryName, $purchaseid, $name, $purchaseid, $donationName, $purchaseid, $quantity, $purchaseid
	  );
	  echo $donation_row;
	  
	  $datai++;
	  $x++;
	  $y++;
	  
  }
?>

	 </tbody>
	 </table>

   
<?php displayFooter();?>
<script type="text/javascript">
	 function loadExcel(){
 			$("#load").show();
 			var filter = "<?php echo $_POST['filter'] ?>";
       		window.location.href = 'bar-product-movements-report.php?filter='+filter;
       		    setTimeout(function () {
			        $("#load").hide();
			    }, 5000);   
       }
</script>
