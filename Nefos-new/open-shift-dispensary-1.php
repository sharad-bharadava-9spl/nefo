<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-closing.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	// Did the page resubmit with a form?
	if ($_POST['step3'] == 'complete') {
		
		$closingtime = $_SESSION['closingtime'];
		$closingid = $_SESSION['closingid'];
		$confirmedOpen = $_POST['confirmedOpen'];
		$_SESSION['confirmedOpen'] = $confirmedOpen;
	
		foreach($_POST['confirmedOpen'] as $confirmedOpenCalc) {

			$category = $confirmedOpenCalc['category'];
			$weight = $confirmedOpenCalc['weight'];
			$weightDelta = $confirmedOpenCalc['weightDelta'];
			
			if ($category == '1') {
				$prodStockFlower = $prodStockFlower + $weight;
				$stockDeltaFlower = $stockDeltaFlower + $weightDelta;
			} else if ($category == '2') {
				$prodStockExtract = $prodStockExtract + $weight;
				$stockDeltaExtract = $stockDeltaExtract + $weightDelta;
			}

		}

		$prodStock = $prodStockFlower + $prodStockExtract;
		$stockDelta = $stockDeltaFlower + $stockDeltaExtract;

		
		// Retrieve arrays from SESSION (change to Cookies at some point? - to preserve closingdata in case of electrical crash)
		$openingtime = date('Y-m-d H:i:s');
		$bankBalance = $_SESSION['bankBalance'];

		// Check if the last closing has been opened, to know whether to INSERT or UPDATE
		$openingLookup = "SELECT shiftOpenedNo FROM shiftclose WHERE closingid = '$closingid'";
				
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$dayOpenedNo = $row['shiftOpenedNo'];
			$openingid = $dayOpenedNo;
			
		if ($dayOpenedNo > 0) {
			
			// Means part of the day has been opened already, so use UPDATE
			$query = sprintf("UPDATE shiftopen SET openingtime = '%s', stockDelta = '%f', prodStock = '%f', prodStockFlower = '%f', prodStockExtract = '%f', stockDeltaFlower = '%f', stockDeltaExtract = '%f' WHERE openingid = '%d'",
			  $openingtime, $stockDelta, $prodStock, $prodStockFlower, $prodStockExtract, $stockDeltaFlower, $stockDeltaExtract, $openingid);
	
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
				
			$timeNow = date('Y-m-d H:i:s');
			
			$updateClosing = sprintf("UPDATE shiftclose SET disOpened = 2, disOpenedAt = '%s' WHERE closingid = '%d';",
				$timeNow,
				mysql_real_escape_string($closingid)
				);
			
			mysql_query($updateClosing)
				or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());

			
		} else {
			
			
			// Means no opening (rec. or dis.) has been done, so use INSERT
			$query = sprintf("INSERT INTO shiftopen (openingtime, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract) VALUES ('%s', '%f', '%f', '%f', '%f', '%f', '%f');",
			  $openingtime, $stockDelta, $prodStock, $prodStockFlower, $prodStockExtract, $stockDeltaFlower, $stockDeltaExtract);
	
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
				
			$openingid = mysql_insert_id();
			
			$timeNow = date('Y-m-d H:i:s');
			
			$updateClosing = sprintf("UPDATE shiftclose SET disOpened = 2, shiftOpenedNo = '%d', disOpenedAt = '%s' WHERE closingid = '%d';",
				mysql_real_escape_string($openingid),
				$timeNow,
				mysql_real_escape_string($closingid)
				);

			mysql_query($updateClosing)
				or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());
					
			
		}
		
		// Openingdetails
		foreach($_SESSION['confirmedOpen'] as $confirmedOpen) {
			$category = $confirmedOpen['category'];
			$productid = $confirmedOpen['productid'];
			$purchaseid = $confirmedOpen['purchaseid'];
			$weight = $confirmedOpen['weight'];
			$weightDelta = $confirmedOpen['weightDelta'];
			$prodopencomment = $confirmedOpen['prodopencomment'];
			
	    	// Query to add to openingdetails table
			$query = sprintf("INSERT INTO shiftopendetails (openingid, category, productid, purchaseid, weight, weightDelta, prodOpenComment) VALUES ('%d', '%d', '%d', '%d', '%f', '%f', '%s');",
		  			 $openingid, $category, $productid, $purchaseid, $weight, $weightDelta, $prodopencomment);
		  
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
		
		} // Product loop ends
		
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['dispensary-opened-successfully'];
		header("Location: open-shift.php");
		exit();

		## ON PAGE SUBMISSION END ##
		
	}
	
	if ($_POST['closingConfirm'] != 'yes') {
		echo $lang['global-onenotcomplete'];
		exit();
	}
	
	$closingid = $_SESSION['closingid'];
	
	$confirmLeave = <<<EOD
    $(document).ready(function() {   	    
document.querySelector('button').addEventListener("click", function(){
    window.btn_clicked = true;      //set btn_clicked to true
});
$(window).bind('beforeunload', function(){
    if(!window.btn_clicked){
        return 'Are you sure you want to leave this page?';
    }
});
  }); // end ready
EOD;

	
	pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "step1", $lang['openday-dis-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	$_SESSION['dayopenProduct'] = $_POST['dayopenProduct'];

	echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='' method='POST'><br />";
	echo "<input type='hidden' name='productConfirm' value='yes'><br />";

	$i=0;

	echo "<h3 class='title'>{$lang['global-flowerscaps']}</h3><div class='productboxwrap'>";

		foreach($_POST['dayopenProduct'] as $prodOpen) {
			$name = $prodOpen['name'];
			$breed2 = $prodOpen['breed2'];
			$category = $prodOpen['category'];
			$productid = $prodOpen['productid'];
			$purchaseid = $prodOpen['purchaseid'];
			$fullWeight = $prodOpen['weight'];
			$growtype = $prodOpen['growtype'];
			$breed2 = $prodOpen['breed2'];
			$tupperWeight = $prodOpen['tupperWeight'];
			
			$weight = $fullWeight - $tupperWeight;
			
			
			if ($category == '2' && $dividerset != 'yes') {
				
				// insert divider
				$dividerset = 'yes';
				echo "</div><h3 class='title'>{$lang['global-extractscaps']}</h3><div class='productboxwrap'>";
				
			}
						
			
		// Query to look up yesterday's closing balance
		$closingLookup = "SELECT d.weight FROM shiftclosedetails d, shiftclose c WHERE purchaseid = $purchaseid AND d.closingid = c.closingid ORDER BY closingtime DESC LIMIT 1";
		
		$result = mysql_query($closingLookup)
			or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());


		// Retrieve yesterdays closing data
		$row = mysql_fetch_array($result);
			$weightYday = $row['weight'];
			$weightDelta = $weight - $weightYday;
			$totDelta = $totDelta + $weightDelta;
		
		if ($weightDelta > 0) {
			$deltaColour = ' positive';
		} else if ($weightDelta < 0) {
			$deltaColour = ' negative';
		} else {
			$deltaColour = '';
		}
			
	$i++;
	$product_row = sprintf("
<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#fullWeight%d').val();
          var b = $('#weightYday%d').val();
          var c = $('#tupperWeight%d').val();
          var total = (a - c) - b;
          var roundedtotal = total.toFixed(2);
          $('#weightDelta%d').val(roundedtotal);
          
          var realTotal = a - c;
          var roundedrealTotal = realTotal.toFixed(2);
          $('#weight%d').val(roundedrealTotal);

          var wdelta%d = $('#weightDelta%d').val();
          
          if (wdelta%d < '0.00') {
          	$('#weightDelta%d').css('color', 'red');
      	  }
      	  if (wdelta%d > '0.00') {
          	$('#weightDelta%d').css('color', 'green');
      	  }
    }

        $('#weight%d').bind('keypress keyup blur', compute);
        $('#fullWeight%d').bind('keypress keyup blur', compute);
        $('#tupperWeight%d').bind('keypress keyup blur', compute);
        

  }); // end ready
</script>

		 <div class='productbox'>
			<h3>%s %s</h3>
			<center>%s&nbsp;</center><br />
		<table>
		 <tr>
		  <td>{$lang['weightyday']}:</td>
		  <td><input type='number' lang='nb' name='confirmedOpen[%d][weightYday]' id='weightYday%d' class='fourDigit' value='%0.02f' step='0.01' readonly /></td>
		 </tr>
		 <tr>
		  <td>{$lang['weightnow']}:</td>
		  <td><input type='number' lang='nb' name='confirmedOpen[%d][fullWeight]' id='fullWeight%d' class='fourDigit' value='%0.02f' step='0.01' /></td>
		 </tr>
		 <tr>
		  <td>- {$lang['jar-weight']}:</td>
		  <td><input type='number' lang='nb' name='confirmedOpen[%d][tupperWeight]' id='tupperWeight%d' class='fourDigit red' value='%0.02f' step='0.01' /></td>
		 </tr>
		 <tr>
		  <td>= {$lang['add-realweight']}:</td>
		  <td><input type='number' lang='nb' name='confirmedOpen[%d][weight]' id='weight%d' class='fourDigit' value='%0.02f' step='0.01' readonly /></td>
		 </tr>
		 <tr>
		  <td><strong>{$lang['global-delta']}:</td>
		  <td><strong><input type='number' lang='nb' name='confirmedOpen[%d][weightDelta]' id='weightDelta%d' class='fourDigit%s' value='%0.02f' step='0.01' readonly /></td>
		 </tr>
		</table><br />
		 <center>{$lang['global-comment']}?</center>
		 <textarea name='confirmedOpen[%d][prodopencomment]'></textarea>
		 </div>
  	   <input type='hidden' name='confirmedOpen[%d][name]' value='%s' />
  	   <input type='hidden' name='confirmedOpen[%d][category]' value='%d' />
  	   <input type='hidden' name='confirmedOpen[%d][productid]' value='%d' />
  	   <input type='hidden' name='confirmedOpen[%d][purchaseid]' value='%d' />",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $breed2, $growtype, $i, $i, $weightYday, $i, $i, $fullWeight, $i, $i, $tupperWeight, $i, $i, $weight, $i, $i, $deltaColour, $weightDelta, $i, $i, $name, $i, $category, $i, $productid, $i, $purchaseid
	  );
	  echo $product_row;
		// End loop for each product
		}
		echo "</div>";
		
		echo "<input type='hidden' name='totDelta' value='$totDelta' />";
		echo "<input type='hidden' name='step3' value='complete' />";
 		echo "<button type='submit' name='oneClick'>{$lang['global-confirm']}</button>";
		echo "</form>";

		## FORM INPUT END ##

displayFooter();