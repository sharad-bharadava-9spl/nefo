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
		$catArray = $_POST['catArray'];
	
		foreach($_POST['confirmedOpen'] as $confirmedOpenCalc) {

			$category = $confirmedOpenCalc['category'];
			$weight = $confirmedOpenCalc['weight'];
			$weightDelta = $confirmedOpenCalc['weightDelta'];
			
			${'prodStock'.$category} = ${'prodStock'.$category} + $weight;
			${'stockDelta'.$category} = ${'stockDelta'.$category} + $weightDelta;
			
		}
		
		// Retrieve arrays from SESSION (change to Cookies at some point? - to preserve closingdata in case of electrical crash)
		$openingtime = date('Y-m-d H:i:s');
		$bankBalance = $_SESSION['bankBalance'];
		
		// Is this the first ever opening? Check if an opening already exists (perhaps the dispensary was opened), and UPDATE that one. If it doens't exist, create a new one!
		if ($_SESSION['firstOpening'] == 'true') {
			
		$selectUsers = "SELECT COUNT(openingid) FROM opening ORDER BY openingtime DESC LIMIT 1";
		$rowCount = $pdo3->query("$selectUsers")->fetchColumn();
		
			// Grab opening ID
			$openingLookup = "SELECT openingid FROM opening ORDER BY openingtime DESC LIMIT 1";
			try
			{
				$result = $pdo3->prepare("$openingLookup");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			
		
			if ($rowCount == 0) {
	
				// No opening exists. Let's create it.
				$query = sprintf("INSERT INTO opening (openingtime) VALUES ('%s');",
				  $openingtime);
		
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
				
		$openingid = $pdo3->lastInsertId();
				
				// Insert into openingother
				foreach($catArray as $cat) {
					
					$catID = $cat;
					
					$query = sprintf("INSERT INTO openingother (openingid, category, categoryType, prodStock, stockDelta) VALUES ('%d', '%d', '%d', '%f', '%f');",
					  $openingid, $catID, 1, ${'prodStock'.$cat}, 0);
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
				
			} else {
				
				// Opening exists. Let's update it.
				$row = $result->fetch();
					$openingid = $row['openingid'];
					
				$query = sprintf("UPDATE opening SET openingtime = '%s' WHERE openingid = '%d'",
				  $openingtime, $openingid);
		
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

				// Delete from openingother
				$deleteOpenOther = "DELETE from openingother WHERE categoryType = 1 AND openingid = '$openingid'";
				
		try
		{
			$result = $pdo3->prepare("$deleteOpenOther")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
				// Insert into openingother
				foreach($catArray as $cat) {
					
					$catID = $cat;
					
					$query = sprintf("INSERT INTO openingother (openingid, category, categoryType, prodStock, stockDelta) VALUES ('%d', '%d', '%d', '%f', '%f');",
					  $openingid, $catID, 1, ${'prodStock'.$cat}, 0);
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

				
			$timeNow = date('Y-m-d H:i:s');
			
			if ($_SESSION['noCompare'] != 'true') {
				
				$updateClosing = sprintf("UPDATE closing SET dis2Opened = 2, dis2OpenedAt = '%s' WHERE closingid = '%d';",
					$timeNow, $closingid
					);			
					
			} else {
				
				$updateClosing = sprintf("UPDATE opening SET firstDis2Open = 2, firstDis2OpenAt = '%s' WHERE openingid = '%d';",
					$timeNow, $openingid
					);
				
			}
			
		try
		{
			$result = $pdo3->prepare("$updateClosing")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
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
				$query = sprintf("INSERT INTO openingdetails (openingid, category, categoryType, productid, purchaseid, weight, weightDelta, prodOpenComment) VALUES ('%d', '%d', '%d', '%d', '%d', '%f', '%f', '%s');",
			  			 $openingid, $category, 1, $productid, $purchaseid, $weight, $weightDelta, $prodopencomment);
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
			
			} // Product loop ends

			// On success: redirect.
			$_SESSION['successMessage'] = $lang['dispensary-opened-successfully'];
			header("Location: open-day.php");
			exit();
				
		}
		
		// Below: NOT first opening, so we'll use comparisons.
	

		// Check if the last closing has been opened, to know whether to INSERT or UPDATE
		$openingLookup = "SELECT dayOpenedNo FROM closing WHERE closingid = '$closingid'";
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$dayOpenedNo = $row['dayOpenedNo'];
			$openingid = $dayOpenedNo;
			
		if ($dayOpenedNo > 0) {
			
			// Means part of the day has been opened already, so use UPDATE
			$query = sprintf("UPDATE opening SET openingtime = '%s' WHERE openingid = '%d'",
			  $openingtime, $openingid);
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

			// Delete from openingother
			$deleteOpenOther = "DELETE from openingother WHERE categoryType = 1 AND openingid = '$openingid'";
		try
		{
			$result = $pdo3->prepare("$deleteOpenOther")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			// Insert into openingother
			foreach($catArray as $cat) {
				
				$catID = $cat;
				
				$query = sprintf("INSERT INTO openingother (openingid, category, categoryType, prodStock, stockDelta) VALUES ('%d', '%d', '%d', '%f', '%f');",
				  $openingid, $catID, 1, ${'prodStock'.$cat}, ${'stockDelta'.$cat});
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
				
			if ($_SESSION['noCompare'] != 'true') {
				
				$timeNow = date('Y-m-d H:i:s');
				$updateClosing = sprintf("UPDATE closing SET dis2Opened = 2, dis2OpenedAt = '%s' WHERE closingid = '%d';",
					$timeNow, $closingid
					);
		try
		{
			$result = $pdo3->prepare("$updateClosing")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
			}
			
			
		} else {
			
			// No opening exists. Let's create it.
			$query = sprintf("INSERT INTO opening (openingtime) VALUES ('%s');",
			  $openingtime);
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
			
		$openingid = $pdo3->lastInsertId();
			
			// Insert into openingother
			foreach($catArray as $cat) {
				
				$catID = $cat;
				
				$query = sprintf("INSERT INTO openingother (openingid, category, categoryType, prodStock, stockDelta) VALUES ('%d', '%d', '%d', '%f', '%f');",
				  $openingid, $catID, 1, ${'prodStock'.$cat}, ${'stockDelta'.$cat});
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
			
			if ($_SESSION['noCompare'] != 'true') {
				
				$timeNow = date('Y-m-d H:i:s');
				$updateClosing = sprintf("UPDATE closing SET dis2Opened = 2, dayOpenedNo = '%d', dis2OpenedAt = '%s' WHERE closingid = '%d';",
					$openingid,
					$timeNow, $closingid
					);
		try
		{
			$result = $pdo3->prepare("$updateClosing")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
					
			}
			
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
			$query = sprintf("INSERT INTO openingdetails (openingid, category, categoryType, productid, purchaseid, weight, weightDelta, prodOpenComment) VALUES ('%d', '%d', '%d', '%d', '%d', '%f', '%f', '%s');",
		  			 $openingid, $category, 1, $productid, $purchaseid, $weight, $weightDelta, $prodopencomment);
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
		
		} // Product loop ends
		
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['dispensary-opened-successfully'];
		header("Location: open-day.php");
		exit();

		## ON PAGE SUBMISSION END ##
		
	}
	
	
	if ($_POST['closingConfirm'] != 'yes') {
		echo $lang['global-onenotcomplete'];
		exit();
	}
	
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

	
	pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "step1", $lang['openday-dis2-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	$_SESSION['dayopenProduct'] = $_POST['dayopenProduct'];
	
	echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='' method='POST'><br />";
	echo "<input type='hidden' name='productConfirm' value='yes'><br />";

	$i=0;
	
	$catArray = $_POST['catArray'];
	
	foreach($catArray as $value) {
		echo '<input type="hidden" name="catArray[]" value="'. $value. '">';
	}

		foreach($_POST['dayopenProduct'] as $prodOpen) {
			$name = $prodOpen['name'];
			$category = $prodOpen['category'];
			$productid = $prodOpen['productid'];
			$purchaseid = $prodOpen['purchaseid'];
			$weight = $prodOpen['weight'];
			$catName = $prodOpen['categoryName'];
			
		if (${'header'.$category} != 'set') {
			echo "<br /><h3 class='title'>$catName</h3>";
			${'header'.$category} = 'set';
		}

	
		// Query to look up yesterday's closing balance
		if ($_SESSION['noCompare'] != 'true') {
			
			$closingLookup = "SELECT d.weight FROM closingdetails d, closing c WHERE category = $category AND purchaseid = $purchaseid and d.closingid = c.closingid ORDER BY c.closingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$closingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
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
		}
			
	$i++;
	
	// Exception: No calculations if this is first opening!
	if ($_SESSION['firstOpening'] == 'true') {
		
		$product_row = sprintf("

		 <div class='productbox'>
			<h3>%s ($purchaseid)</h3>
		<table>
		 <tr>
		  <td>{$lang['countnow']}:</td>
		  <td><input type='number' lang='nb' name='confirmedOpen[%d][weight]' id='fullWeight%d' class='fourDigit' value='%0.02f' step='0.01' /></td>
		 </tr>
		</table><br />
		 <center>{$lang['global-comment']}?</center>
		 <textarea name='confirmedOpen[%d][prodopencomment]'></textarea>
		 </div>
  	   <input type='hidden' name='confirmedOpen[%d][name]' value='%s' />
  	   <input type='hidden' name='confirmedOpen[%d][category]' value='%d' />
  	   <input type='hidden' name='confirmedOpen[%d][productid]' value='%d' />
  	   <input type='hidden' name='confirmedOpen[%d][purchaseid]' value='%d' />",
	  	$name, $i, $i, $weight, $i, $i, $name, $i, $category, $i, $productid, $i, $purchaseid
	  	);
	  
	} else {
		
		$product_row = sprintf("
<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#fullWeight%d').val();
          var b = $('#weightYday%d').val();
          var total = a - b;
          var roundedtotal = total.toFixed(2);
          $('#weightDelta%d').val(roundedtotal);
          
          var wdelta%d = $('#weightDelta%d').val();
          
          if (wdelta%d < '0.00') {
          	$('#weightDelta%d').css('color', 'red');
      	  }
      	  if (wdelta%d > '0.00') {
          	$('#weightDelta%d').css('color', 'green');
      	  }
    }

        $('#fullWeight%d').bind('keypress keyup blur', compute);
        

  }); // end ready
</script>

		 <div class='productbox'>
			<h3>%s ($purchaseid)</h3>
		<table>
		 <tr>
		  <td>{$lang['countyday']}:</td>
		  <td><input type='number' lang='nb' name='confirmedOpen[%d][weightYday]' id='weightYday%d' class='fourDigit' value='%0.02f' step='0.01' readonly /></td>
		 </tr>
		 <tr>
		  <td>{$lang['countnow']}:</td>
		  <td><input type='number' lang='nb' name='confirmedOpen[%d][weight]' id='fullWeight%d' class='fourDigit' value='%0.02f' step='0.01' /></td>
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
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $i, $i, $weightYday, $i, $i, $weight, $i, $i, $deltaColour, $weightDelta, $i, $i, $name, $i, $category, $i, $productid, $i, $purchaseid
	  );
	  
  	}
  		
	  	echo $product_row;
	// End loop for each product
	}
		
		echo "<input type='hidden' name='totDelta' value='$totDelta' />";
		echo "<input type='hidden' name='step3' value='complete' />";
 		echo "<br /><br /><button type='submit' name='oneClick' class='customButton'>{$lang['global-confirm']}</button>";
		echo "</form>";
		echo "</div>";
		## FORM INPUT END ##

displayFooter();