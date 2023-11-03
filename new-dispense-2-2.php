<?php
	// file updated by konstant for Task-15087048 on 29-04-2022
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	// KONSTANT CODE UPDATE BEGIN
    $operatorId = $_SESSION['user_id'];
    $fullmenu = $_SESSION['fullmenu'];
    // KONSTANT CODE UPDATE END
   
    if ($domain == 'holysmokebarcelona') {
	 
$noNegative = <<<EOD
			  change: {
				  range: [0,1000000]
			  },
EOD;

    }
    
    
	if ($_SESSION['domain'] == 'macarena' || $_SESSION['domain'] == 'breakingbuds') {
		
		$query = "SELECT 'shiftclose' AS type, closingid AS typeid, closingtime AS time FROM shiftclose UNION ALL SELECT 'shiftopen' AS type, openingid AS typeid, openingtime AS time FROM shiftopen UNION ALL SELECT 'closing' AS type, closingid AS typeid, closingtime AS time FROM closing UNION ALL SELECT 'opening' AS type, openingid AS typeid, openingtime AS time FROM opening ORDER by time DESC";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$type = $row['type'];
			$typeid = $row['typeid'];
			$time = $row['time'];
		
		if ($type == 'closing' || $type == 'shiftclose') {
		
			
			pageStart("CCS", NULL, $testinput, "pindex", "loggedIn", "CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
			echo "<br /><center><div id='scriptMsg'><div class='error'>El dia/turno no esta abierto! No puedes hacer operaciones hasta que abras un dia/turno.</div></div></center>";

			exit();
			
		}
		
	}
    
	// Retrieve System settings
	getSettings();
	// Did this page re-submit with a new donation? 
	if ($_POST['newDonation'] > 0) {
			
	    $userid = $_POST['user_id'];
  	    $credit = $_POST['credit'];
		$amount = $_POST['newDonation'];
		$donatedTo = 1;
		$registertime = date('Y-m-d H:i:s');
		$postSales = $_POST['sales'];
		$_SESSION['savedSale'] = $_POST['sales'];
	    $_SESSION['saveDispense'] = basename($_SERVER['REQUEST_URI']);
	    $_SESSION['new-dispense-flag'] = 1;
	    $_SESSION['dispense_user_id'] = $_POST['user_id'];
		$operator = $_SESSION['user_id'];
		// Look up user credit
		$userCredit = "SELECT credit FROM users WHERE user_id = '{$userid}'";
		try
		{
			$result = $pdo3->prepare("$userCredit");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$oldCredit = $row['credit'];
		$newCredit = $amount + $oldCredit;
				
		// Query to add to Donations table
		 $query = sprintf("INSERT INTO donations (userid, donationTime, type, amount, comment, creditBefore, creditAfter, donatedTo, operator) VALUES ('%d', '%s', '%d', '%f', '%s', '%f', '%f', '%d', '%d');",
		  $userid, $registertime, '1', $amount, $comment, $oldCredit, $newCredit, $donatedTo, $operator);
		  
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
		
			
		// Query to update user profile
		$updateUser = sprintf("UPDATE users SET credit = '%f', lastDispense = '%s' WHERE user_id = '%d';",
			$newCredit,	$registertime, $userid);
				
		try
		{
			$result = $pdo3->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		// KONSTANT CODE UPDATE BEGIN
		// Check the summary for last products
		$getSavedSummary = sprintf("SELECT count(*) from saveDispense_summary where user_id = '%d' ", $userid); 
		try{
			$result = $pdo3->prepare("$getSavedSummary"); 
			$result->execute(); 
		}
		catch (PDOException $e){
			$error = 'Error Fetching data: ' . $e->getMessage();
			echo $error;
			exit();
		}
		
		$savedSummary_rows = $result->fetchColumn(); 
		if($savedSummary_rows>0){
			$query = sprintf("UPDATE saveDispense_summary SET gramsTOT='%f', realgramsTOT='%f', unitsTOT='%f', credit='%f', newcredit='%f', eurcalcTOT='%f' WHERE user_id = '%d'",
			 $_POST['gramsTOT'], $_POST['realgramsTOT'], $_POST['unitsTOT'], $newCredit, $_POST['newcredit'], $_POST['eurcalcTOT'], $userid);
					try
					{
						$result = $pdo3->prepare("$query")->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error inserting data: ' . $e->getMessage();
							echo $error;
							exit();
					}
		}else{
				// Query to save to savesales_details table
				 $query = sprintf("INSERT INTO saveDispense_summary (user_id, gramsTOT, realgramsTOT, unitsTOT, credit, newcredit, eurcalcTOT) VALUES ('%d', '%f', '%f', '%f', '%f', '%f', %f);",
				  $userid,  $_POST['gramsTOT'], $_POST['realgramsTOT'],  $_POST['unitsTOT'],  $newCredit,  $_POST['newcredit'],  $_POST['eurcalcTOT']);
				  
				try
				{
					$result = $pdo3->prepare("$query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error inserting data: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
		}
		foreach($postSales as $postSale){
			
			// Check the entries for last product
			 $getSaveddata = sprintf("SELECT count(*) from savesales_details where purchase_id = '%d' AND user_id = '%d' ", $postSale[purchaseid], $userid); 
			try{
				$result = $pdo3->prepare("$getSaveddata"); 
				$result->execute(); 
			}
			catch (PDOException $e){
				$error = 'Error Fetching data: ' . $e->getMessage();
				echo $error;
				exit();
			}
			
			$savedData_rows = $result->fetchColumn(); 
			if($savedData_rows>0){
				$query = sprintf("UPDATE savesales_details SET grams='%f', euro='%f', realGrams='%f', grams2='%f' WHERE purchase_id='%d' AND user_id = '%d'",
				 $postSale[grams], $postSale[euro], $postSale[realGrams], $postSale[grams2], $postSale[purchaseid], $userid);
						try
						{
							$result = $pdo3->prepare("$query")->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error inserting data: ' . $e->getMessage();
								echo $error;
								exit();
						}
			}else{
					if($postSale[grams] != 0){
						// Query to save to savesales_details table
						 $query = sprintf("INSERT INTO savesales_details (user_id, purchase_id, grams, euro, realGrams, grams2) VALUES ('%d', '%d', '%f', '%f', '%f', '%f');",
						  $userid, $postSale[purchaseid], $postSale[grams], $postSale[euro], $postSale[realGrams], $postSale[grams2]);
						  
						try
						{
							$result = $pdo3->prepare("$query")->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error inserting data: ' . $e->getMessage();
								echo $error;
								exit();
						}
					}
			}
		}
		
	// KONSTANT CODE UPDATE END
				
			// On success: redirect.
			$_SESSION['successMessage'] = $lang['global-added'] . " " . $amount . $lang['donation-addedsuccessfully'] . $newCredit . " ".$_SESSION['currencyoperator'];
			
			header("Location: new-dispense-2.php?user_id=$userid");
			exit();
			
	}
	
if ($_SESSION['realWeight'] == 0) {
	
	// Did this page re-submit with a form? If so, check & store details
	
	// Remember to save with direct or credit flag!!!!!!!!!!!!!! 1 = cash, 2 = tarjeta, 3 = cash. dB field called 'direct'.
	if (isset($_POST['dispense'])) {
		

		
  	    $user_id = $_POST['user_id'];
		$euroTOT = $_POST['euroTOT'];
		$eurcalcTOT = $_POST['eurcalcTOT'];
		$gramsTOT = $_POST['gramsTOT'];
		$unitsTOT = $_POST['unitsTOT'];
		$paidTOT = $_POST['paidTOT'];
		$gramsTOT = $_POST['gramsTOT'];
		$adminComment = $_POST['adminComment'];
		$newcredit = $_POST['newcredit'];
		$credit = $_POST['credit'];
		$realCredit = $_POST['realCredit'];
		$realNewCredit = $_POST['realNewCredit'];		
  	    $day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$paidUntil = $_POST['paidUntil'];	
		$totDiscountInput = $_POST['totDiscountInput'];	
		$pmtType = $_POST['pmtType'];
		$owndate = $_POST['owndate'];
		$hour = $_POST['hour'];
		$minute = $_POST['minute'];
		$eurdiscount = $_POST['eurdiscount'];
		
		// calulate all totals on server side

		$saleGrams = 0;
	    $saleUnits = 0;
	    $saleEuro = 0;
	    $unitExist = 0;
	    $gramExist = 0;

  	   foreach($_POST['sales'] as $sale) {

				 	$selectCats = "SELECT type from categories WHERE id=".$sale['category'];
					
					try
					{
						$resultCat = $pdo3->prepare("$selectCats");
						$resultCat->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$cat_row = $resultCat->fetch();
						$cat_type = $cat_row['type'];

						if ($cat_type == 0 && $sale['category'] > 2) {
							// units
							$saleUnits = $saleUnits + $sale['grams'] +  $sale['grams2'];
							if($sale['grams'] > 0 || $sale['grams2'] > 0){
								$unitExist = 1;
							}
						} else {
							// grams
							$saleGrams = $saleGrams + $sale['grams'] +  $sale['grams2'];
							if($sale['grams'] > 0 || $sale['grams2'] > 0){
								$gramExist = 1;
							}
						}

						$saleEuro = $saleEuro + $sale['euro'];
						
						if ($_SESSION['domain'] == 'greengoat') {
							
							
					// code update start by konstant for dispense validation on 28-02-2022
						if($sale['grams'] > 0 && ($sale['euro'] == 0 || $sale['euro'] == '')){
							$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
							header("Location: new-dispense-2.php?user_id=$user_id");
							exit();
						}
					// code update end by konstant for dispense validation on 28-02-2022

					// code update start by konstant for dispense validation on 09-03-2022
					if($sale['grams'] > 0){
						if($sale['volume_discount'] != 0 || $sale['volume_discount_amount'] != 0){
							if($sale['euro'] != $sale['volume_discount']){
								$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
								header("Location: new-dispense-2.php?user_id=$user_id");
								exit();
							}
							if($sale['grams'] != $sale['volume_discount_amount']){
								$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
								header("Location: new-dispense-2.php?user_id=$user_id");
								exit();
							}
						}/*else{
							$sale_price = $sale['ppg'] * $sale['grams'];
							if($sale['euro'] != $sale_price){
								$_SESSION['errorMessage'] = "4 Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
								header("Location: new-dispense-2.php?user_id=$user_id");
								exit();
							}
						}*/
					}	

							
						}

		}
		
						if ($_SESSION['domain'] == 'greengoat') {
							
		// code update start  by konstant for dispense validation on 08-04-2022
		if($eurcalcTOT != $saleEuro){
			$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
			header("Location: new-dispense-2.php?user_id=$user_id");
			exit();
		}
		if($gramsTOT != $saleGrams){
			$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
			header("Location: new-dispense-2.php?user_id=$user_id");
			exit();
		}		
		if($unitsTOT != $saleUnits){
			$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
			header("Location: new-dispense-2.php?user_id=$user_id");
			exit();
		}
		// code update end by konstant for dispense validation on 08-04-2022
		//echo $unitExist." ".$gramExist; die;
		// check totals on server side

						}


		//echo $unitExist." ".$gramExist; die;
		// check totals on server side

		if($gramExist){
			if($saleGrams == 0){
				$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
				header("Location: new-dispense-2.php?user_id=$user_id");
				exit();
			}
		}		

		if($unitExist){
			if($saleUnits == 0){
				$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
				header("Location: new-dispense-2.php?user_id=$user_id");
				exit();
			}
		}



		if ($_SESSION['dispDonate'] == 1) {
			$totalCheck =  $saleGrams + $saleUnits + $_POST['newDonation'];
		}else{
			$totalCheck =  $saleGrams + $saleUnits;
		}
		
		if($totalCheck == 0){
			$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
			header("Location: new-dispense-2.php?user_id=$user_id");
			exit();
		}
		
	
		
		if ($paidUntil == '') {
			$paidUntil = date('Y-m-d H:i:s');
		}
		// KONSTANT CODE UPDATE BEGIN
	    $discType = $_POST['discType']; 
		   
			foreach($_POST['sales'] as $sale) {
					if(!empty($sale['grams2']) && $sale['grams2'] != ''){
				  		$disc_arr['gift_value'][] = $sale['grams2'];
				  	}
				  	if(!empty($sale['volume_discount']) && $sale['volume_discount'] != ''){
				  		$disc_arr['volume_discount'][] = $sale['volume_discount'];
				  	}
				  		$disc_arr['discType'] = $sale['discType'];
				  		$disc_arr['discPercentage'] = $sale['discPercentage'];
				  	if(!empty($sale['happy_hour_discount']) && $sale['happy_hour_discount'] != ''){	
				  			$disc_arr['happy_hour_discount'][] = $sale['happy_hour_discount'];
				  		}
			}
			
			 if(!empty($disc_arr['gift_value'])){
				$giftDiscount = 7;
			 }
			 if(!empty($disc_arr['volume_discount'])){
			 	$volumeDiscount = 6;
			 }			 
			 if(!empty($disc_arr['happy_hour_discount'])){
			 	$happyhourDiscount = 3;
			 }
			 if($disc_arr['discType'] != ''){
			 	$discountType = $disc_arr['discType'];
			 }			 
			 if($disc_arr['discPercentage'] != ''){
			 	$discountPercent = $disc_arr['discPercentage'];
			 }
	    // KONSTANT CODE UPDATE END
		
		
		if ($pmtType == '' || $pmtType == 0) {
			$pmtType = 3;
		}
		
		if ($_SESSION['domain'] == 'testsystem') {
			$pmtType = 1;
		}
		
		if ($owndate != '') {
			
			$fulldate = "$owndate $hour:$minute";
			$saletime = date("Y-m-d H:i:s", strtotime($fulldate));
			
		} else {
		
			$saletime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????
		}		
		
		
		// Look up user credit
		$userCredit = "SELECT credit, creditEligible, maxCredit FROM users WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$userCredit");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $result->fetch();
			$creditLookup = $row['credit'];
			$newCreditCalc = $creditLookup - $eurcalcTOT;
			$creditEligible = $row['creditEligible'];
			$maxCredit = $row['maxCredit'];
			
			/* 
			
				Credit: 	 8,50
				Purchase: 	38,50
				Newcredit: -30,00
				
			
			*/
			
			// What if it's on Direct, and a member has insufficient saldo? Gotta run the checjk then too. Use direct = 3 I reckon?
			
			if ($newCreditCalc < 0 && $creditEligible == 0 && $pmtType == 3) {
			
				$_SESSION['errorMessage'] = $lang['credit-not-sufficient'] . "!";
				
				pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				exit();
	
				
			} else if ($creditEligible == 1 && $newCreditCalc < (0 - $maxCredit) && $pmtType == 3) {
			
				$_SESSION['errorMessage'] = $lang['credit-exceeded'] . ": " .  $maxCredit;
				
				pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				exit();
				
			}
			
		if ($pmtType == 3) {
			
			// Query to update user credit
			$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
				$newCreditCalc, $user_id
				);
					
			try
			{
				$result = $pdo3->prepare("$updateUser")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			// Query to add new sale to Sales table - 6 arguments
		   $query = sprintf("INSERT INTO sales (saletime, operatorid, userid, amount, amountPaid, quantity, units, adminComment, settled, settledTo, creditBefore, creditAfter, expiry, realQuantity, discount, direct, discounteur) VALUES ('%s', '%d', '%d', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%f', '%f', '%s', '%f', '%f', '%d', '%f');", 
		  	$saletime, $operatorId, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $unitsTOT, $adminComment, $saletime, '1', $creditLookup, $newCreditCalc, $paidUntil, $gramsTOT, $totDiscountInput, $pmtType, $eurdiscount);   
		
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
				
			$saleid = $pdo3->lastInsertId();
			// KONSTANT CODE UPDATE BEGIN
			if($discountType < 5){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $discountType, $discountPercent);
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			if($totDiscountInput !='' && !empty($totDiscountInput)){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, 5, $totDiscountInput);
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// query to add gift discounts in sales_discount table
			
		
			if($giftDiscount){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $giftDiscount, '');
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// query to add volume discounts in sales_discount table			
			if($volumeDiscount){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $volumeDiscount, '');
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// query to add happy hour discounts in sales_discount table			
			if($happyhourDiscount){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $happyhourDiscount, '');
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// KONSTANT CODE UPDATE END
			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO f_sales (saletime, userid, amount, amountPaid, quantity, units, adminComment, settled, settledTo, creditBefore, creditAfter, expiry, realQuantity, discount, direct) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%f', '%f', '%s', '%f', '%f', '%d');",
		  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $unitsTOT, $adminComment, $saletime, '1', $creditLookup, $newCreditCalc, $paidUntil, $gramsTOT, $totDiscountInput, $pmtType);
		
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
				
			
		} else {
			
			// Query to add new sale to Sales table - 6 arguments
		   $query = sprintf("INSERT INTO sales (saletime, operatorid, userid, amount, amountPaid, quantity, units, adminComment, settled, settledTo, expiry, realQuantity, discount, direct, discounteur) VALUES ('%s', '%d', '%d', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%s', '%f', '%f', '%d', '%f');",
		  	$saletime, $operatorId, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $unitsTOT, $adminComment, $saletime, '1', $paidUntil, $gramsTOT, $totDiscountInput, $pmtType, $eurdiscount);  
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
				
			$saleid = $pdo3->lastInsertId();
			// KONSTANT CODE UPDATE BEGIN
			if($discountType < 5){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $discountType, $discountPercent);
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			if($totDiscountInput !='' && !empty($totDiscountInput)){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, 5, $totDiscountInput);
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// query to add gift discounts in sales_discount table
			
		
			if($giftDiscount){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $giftDiscount, '');
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// query to add volume discounts in sales_discount table			
			if($volumeDiscount){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $volumeDiscount, '');
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// query to add happy hour discounts in sales_discount table			
			if($happyhourDiscount){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $happyhourDiscount, '');
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// KONSTANT CODE UPDATE END
			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO f_sales (saletime, userid, amount, amountPaid, quantity, units, adminComment, settled, settledTo, expiry, realQuantity, discount, direct) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%s', '%f', '%f', '%d');",
		  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $unitsTOT, $adminComment, $saletime, '1', $paidUntil, $gramsTOT, $totDiscountInput, $pmtType);
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
		
		
	foreach($_POST['sales'] as $sale) {
		$name = $sale['name'];
		$category = $sale['category'];
		$productid = $sale['productid'];
		$purchaseid = $sale['purchaseid'];
		$grams = $sale['grams'];
		$grams2 = $sale['grams2'];
		// KONSTANT CODE UPDATE BEGIN
		$salediscType = $sale['discType'];
		$discPercentage = $sale['discPercentage'];
		$happy_hour_discount = $sale['happy_hour_discount'];
		$volume_discount = $sale['volume_discount'];
		$volume_per_discount = $sale['volume_per_discount'];
		$happyDiscount = $volumeDiscount = 0;
		if($happy_hour_discount){
			$happyDiscount = $sale['happy_hour_discount'];
		}
		if($volume_discount){
			$volumeDiscount = $sale['volume_discount'];
		}
		if ($totDiscountInput > 0) {
			$euro = $sale['euro'] * ((100 - $totDiscountInput) / 100);
		} else {
			$euro = $sale['euro'];
		}
		$gramsTot = $grams + $grams2;
		
		if ($gramsTot > 0) {
			
			if ($grams > 0 && $grams2 > 0) {
				
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity, discountType, discountPercentage, happyhourDiscount, volumeDiscount) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f', '%d', '%f', '%f', '%f');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid, $grams, $salediscType, $discPercentage, $happyDiscount, $volume_per_discount);
			  
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
				
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity,  discountType, discountPercentage) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f', '%d', '%f');",
			  	$saleid, $category, $productid, $grams2, '0', $purchaseid, $grams2, 7, '');
			  
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
				
				
			} else {
			
				if ($grams  == 0) {
					
					$grams = $grams2;
					
				}
	    	
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity, discountPercentage, happyhourDiscount, volumeDiscount) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f', '%f', '%f', '%f');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid, $grams, $discPercentage, $happyDiscount, $volume_per_discount);
			  
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
	}
	
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		10, $logTime, $user_id, $_SESSION['user_id'], $eurcalcTOT, $creditLookup, $newCreditCalc);
		
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
		// Remove summary and saved details from dispenses
		$query = sprintf("DELETE from savesales_details where user_id = '%d'", $user_id);
		
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
		$query = sprintf("DELETE from saveDispense_summary where user_id = '%d'", $user_id);
		
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
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['dispense-added'];
		header("Location: dispense.php?saleid=" . $saleid);
		exit();
	}
	/***** FORM SUBMIT END *****/
	
	
	// Get the card ID
	if ($_POST['cardid'] != '') {
		
		$cardid = $_POST['cardid'];
		
		if ($cardid == '') {
			
				$_SESSION['errorMessage'] = $lang['scan-error'];
			
		} else {
		
			// Query to look up user
			$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid = '$cardid'")->fetchColumn();
			
			if ($rowCount == 0) {
				// Query to look up user
				$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid2 = '{$cardid}'")->fetchColumn();
				
				if ($rowCount == 0) {
					// Query to look up user
					$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid3 = '{$cardid}'")->fetchColumn();
					
					if ($rowCount == 0) {
				   		handleError($lang['error-keyfob'],"");
					} else {
						$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid3 = '{$cardid}'");
					}
					
				} else {
					$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid2 = '{$cardid}'");
				}
	
				
			} else {
				$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid = '{$cardid}'");
			}
			
					
			$result->execute();
			
			$row = $result->fetch();
				$user_id = $row['user_id'];
				
			// Check if chip is registered more than once
			if ($rowCount > 1) {
				
				$_SESSION['errorMessage'] = $lang['chip-registered-more-than-once'];
				header("Location: duplicate-chip.php?cardid=$cardid");
				exit();
			
			}
		}
				
	} else if (isset($_POST['userSelect'])) {
		$user_id = $_POST['userSelect'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nomember'],"");
	}
	
	// Check if member is eligible for dispensing
	$userDetails = "SELECT userGroup, paymentWarning, paymentWarningDate, paidUntil, exento FROM users WHERE user_id = '{$user_id}'";
	
	try
	{
		$result = $pdo3->prepare("$userDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
		$userGroup = $row['userGroup'];
		$paymentWarning = $row['paymentWarning'];
		$exento = $row['exento'];
		$paymentWarningDate = $row['paymentWarningDate'];
		$paidUntil = strtotime(date('Y-m-d H:i', strtotime($row['paidUntil'])));
		$nowTime = strtotime(date('Y-m-d H:i'));
		$pwd = strtotime(date('Y-m-d H:i',strtotime($paymentWarningDate)));
		
	if ($userGroup > 6) {
		$_SESSION['errorMessage'] = $lang['cannot-dispense'];
		header("Location: no-dispense.php?user_id=$user_id");
		exit();
	}
	
	if ($_SESSION['dispExpired'] == 0 && $exento == 0) {
		if ($paymentWarning == 1 && $nowTime > $pwd && $nowTime > $paidUntil) {
			$_SESSION['errorMessage'] = $lang['cannot-dispense'];
			header("Location: no-dispense.php?user_id=$user_id");
			exit();
		}
	}
	
	if ($_SESSION['domain'] == 'shambala' && $exento == 0 && $userGroup > 4) {
		if ($nowTime > $paidUntil) {
			$_SESSION['errorMessage'] = $lang['cannot-dispense'];
			header("Location: no-dispense.php?user_id=$user_id");
			exit();
		}
	}

	
	
	// Was a donation made, or a cart saved?
	if (isset($_SESSION['savedSale'])) {
		
		$savedSale = $_SESSION['savedSale'];
		unset($_SESSION['savedSale']);
		
	}
	
	// If membertime is lower than first sale date, use first sale date instead
	$userDetails = "SELECT registeredSince FROM users WHERE user_id = '{$user_id}'";
	
	try
	{
		$result = $pdo3->prepare("$userDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
		$memberSince = date('Y-m-d', strtotime($row['registeredSince']));
	
	// Look up user details for showing profile on the Sales page
	$userDetails2 = "SELECT memberno, paidUntil, userGroup, first_name, last_name, datediff(curdate(),'$memberSince') AS daysMember, paymentWarning, paymentWarningDate, mconsumption, day, month, year, credit, creditEligible, discount, photoext, cardid, nationality, exento FROM users WHERE user_id = '{$user_id}'";
	
	try
	{
		$result = $pdo3->prepare("$userDetails2");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row2 = $result->fetch();
		$memberno = $row2['memberno'];
		$first_name = $row2['first_name'];
		$last_name = $row2['last_name'];
		$paidUntil = $row2['paidUntil'];
		$userGroup = $row2['userGroup'];
		$day = $row2['day'];
		$month = $row2['month'];
		$year = $row2['year'];
		$mconsumption = $row2['mconsumption'];
		$daysMember = $row2['daysMember'];
		$paymentWarning = $row2['paymentWarning'];
		$paymentWarningDate = $row2['paymentWarningDate'];
		$paymentWarningDateReadable = date('d M', strtotime($paymentWarningDate));
		$credit = $row2['credit'];
		$realCredit = $credit;
		$creditEligible = $row2['creditEligible'];
		$discount = $row2['discount'];
		$photoext = $row2['photoext'];
		$cardid = $row2['cardid'];
		$nationality = $row2['nationality'];		
		$exento = $row2['exento'];	
		
	    if ($domain == 'cannabisclubcpt' || $domain == 'eltemplo') {
	    	$chipCheck = ", range: ['{$cardid}','{$cardid}']";
		}

		
			if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
				$giftOption = "<img src='images/free-green.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
			} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
				$giftOption = "<img src='images/gift-big.png' id='free%d' style='margin-bottom: 0; margin-left: 5px; margin-top: 10px; margin-bottom: 6px;' />";
			} else {
				$giftOption = "<span style='display: block; height: 10px;'></span><input type='hidden' value='%d' />";
			}
		
    if ($_SESSION['domain'] == 'faded') {
	    
		if ($_SESSION['userGroup'] > 1) {
			if (($userGroup < 4) && ($_SESSION['user_id'] != 5290)) {
				
				pageStart($lang['mini-profile'], NULL, $deleteNoteScript, "pminiprofile", NULL, $lang['mini-profileC'], $_SESSION['successMessage'], "Solo Pani puede dispensar trabajadores!");
				exit();
				
			}
		}
		
	}

		
	// Query to look up total sales and find weekly average
	$selectSales = "SELECT SUM(amount) FROM sales WHERE userid = $user_id";
	try
	{
		$result = $pdo3->prepare("$selectSales");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
		$totalAmount = $row['SUM(amount)'];
		$totalAmountPerDay = $totalAmount / $daysMember;
		$totalAmountPerWeek = $totalAmountPerDay * 7;
		
		if ($_SESSION['keypads'] == 1) {
			
			$keypadActive = <<<EOD
$('.onScreenKeypad').keypad({
    layout: ['789' + $.keypad.CLOSE, 
        '456' + $.keypad.CLEAR, 
        '123' + $.keypad.BACK, 
        '.0' + $.keypad.SPACE]});			
EOD;
		} else {
			
			$keypadActive = '';
			
		}
		
// Allow negative credit
if ($creditEligible == 1) {
	$validationScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  
    $(document).ready(function() {
	    
$keypadActive
	    
$.validator.addMethod('totalCheck', function(value, element, params) {
    var field_1 = $('input[name="' + params[0] + '"]').val(),
        field_2 = $('input[name="' + params[1] + '"]').val(),
        field_3 = $('input[name="' + params[2] + '"]').val();
        
        if(field_3 == null){
        	field_3 = 0;
        }
    // return parseInt(value) === parseInt(field_1) + parseInt(field_2);
    
    if ((parseFloat(field_1) + parseFloat(field_2) + parseFloat(field_3)) == 0) {
	    return false;
    } else {
	    return true;
    }
}, "Enter the number of persons (including yourself)");
	    	    
	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  cardid: {
				  required: true
				  $chipCheck
			  },
			  $noNegative
			  eurcalcTOT: {
				  range: [0,"{$_SESSION['dispenseLimit']}"]
			  },
			  gramsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT', 'newDonation']
			  },
			  unitsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT', 'newDonation']
			  },
			  pmtType: {
				  required: true,
				  range: [0,3]
			  },
			  hour: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,23]
			  },
			  minute: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,59]
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function(form) {
				   $(".oneClick").attr("disabled", true);
				   // form1
				      setTimeout(function(){
			       form.submit();
			   	}, 700);
	    	  }
	  }); // end validate
	
EOD;
} else {
	$validationScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
    $(document).ready(function() {
	    	    
$keypadActive
        
$.validator.addMethod('totalCheck', function(value, element, params) {
    var field_1 = $('input[name="' + params[0] + '"]').val(),
        field_2 = $('input[name="' + params[1] + '"]').val(),
        field_3 = $('input[name="' + params[2] + '"]').val();
        
        if(field_3 == null){
        	field_3 = 0;
        }
    // return parseInt(value) === parseInt(field_1) + parseInt(field_2);
    
    if ((parseFloat(field_1) + parseFloat(field_2) + parseFloat(field_3)) == 0) {
	    return false;
    } else {
	    return true;
    }
}, "Enter the number of persons (including yourself)");
	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  newcredit: {
				  range: [0,100000]
			  },
			  cardid: {
				  required: true
				  $chipCheck
			  },
			  $noNegative
			  eurcalcTOT: {
				  range: [0,"{$_SESSION['dispenseLimit']}"]
			  },
			  gramsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT', 'newDonation']
			  },
			  unitsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT', 'newDonation']
			  },
			  pmtType: {
				  required: true,
				  range: [0,3]
			  },
			  hour: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,23]
			  },
			  minute: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,59]
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function(form) {
    	  	
   $(".oneClick").attr("disabled", true);
   // form2

   setTimeout(function(){
       form.submit();
   	}, 700);

   
	    	  }
	  }); // end validate

EOD;
}
	  
	$validationScript .= <<<EOD
	$("#credit").click(function () {
		$(".donateField").toggle();
	});
	
	
	$(function(){
	    $('#chipClick').click(function() {
	        $("#cardscan").val('$cardid');
	    });
	});
	  
	  function getItems()
{
var sum = 0;
$( '.calc' ).each( function( i , e ) {
    var v = +$( e ).val();
    if ( !isNaN( v ) )
        sum += v;
} );
  var rsum = sum.toFixed(2);
  $('#grcalcTOT').val(rsum);
  $('#grcalcTOT2').val(rsum);
  $('#grcalcTOTexp').val(rsum);
var sumB = 0;
$( '.calc2' ).each( function( i , e ) {
    var vB = +$( e ).val() ;
    if ( !isNaN( vB ) )
        sumB += vB;
} );
  var rsumB = sumB.toFixed(2);
  
	    var sumDisc = 0;
	
	    $("input[type=checkbox]:checked").each(function(){
	      sumDisc += parseInt($(this).val());
	    });
		$('#totDiscount').html("(" + sumDisc + "%)");
		$('#totDiscountInput').val(sumDisc);
		
		var appliedDisc = (100 - sumDisc) / 100;
		
		var tempPrice = rsumB * appliedDisc;
		
		var eurdisc = $('#eurdiscount').val();
		
		var newPrice = tempPrice - eurdisc;		
  $('#eurcalcTOT').val(newPrice);
  $('#eurcalcTOT2').val(newPrice);
  $('#eurcalcTOTexp').val(newPrice);
  
  
var sumC = 0;
$( '.calc3' ).each( function( i , e ) {
    var vC = +$( e ).val();
    if ( !isNaN( vC ) )
        sumC += vC;
} );
$( '.calc4' ).each( function( i , e ) {
    var vD = +$( e ).val();
    if ( !isNaN( vD ) )
        sumC += vD;
} );
  var sumC = sumC.toFixed(2);
  $('#unitcalcTOT').val(sumC);
  $('#unitcalcTOT2').val(sumC);
  
}
EOD;
if ($_SESSION['dispDonate'] == 1) {
	
	$validationScript .= <<<EOD
	if ($credit < 0) {
	   function computeTot() {
	          var a = $('#realCredit').val();
	          var b = $('#eurcalcTOT').val();
          	  var c = $('#newDonation').val();
	          var total = ((a*1) - (b*1) + (c*1));
	          var roundedtotal = total.toFixed(2);
	          $('#realNewCredit').val(roundedtotal);
	   }
	} else {
   function computeTot() {
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
          var c = $('#newDonation').val();
	      var total = ((a*1) - (b*1) + (c*1));
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
          $('#newcredit2').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
        }
    }
    
   function commaChange() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
        }
   $('#newDonation').bind('keypress keyup blur change', commaChange);
EOD;
} else {
	
	$validationScript .= <<<EOD
	if ($credit < 0) {
	   function computeTot() {
	          var a = $('#realCredit').val();
	          var b = $('#eurcalcTOT').val();
	          var total = ((a*1) - (b*1));
	          var roundedtotal = total.toFixed(2);
	          $('#realNewCredit').val(roundedtotal);
	   }
	} else {
   function computeTot() {
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
	      var total = ((a*1) - (b*1));
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
          $('#newcredit2').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
        }
    }
EOD;
}
	$validationScript .= <<<EOD
	
	
	$("#pmt1").click(function () {
		setTimeout(function(){
		$("#newcredit").val($("#credit").val());
		$("#newcredit2").val($("#credit").val());
		$("#changeTable").css("display", "table-row");
		$("#paid").focus();
		}, 700);
	});	
	$("#pmt2").click(function () {
		setTimeout(function(){
		$("#newcredit").val($("#credit").val());
		$("#newcredit2").val($("#credit").val());
		$("#changeTable").css("display", "none");
		}, 700);
	});	
	$("#pmt3").click(function () {
		setTimeout(function(){
	    getItems();
	    computeTot();
		$("#changeTable").css("display", "none");
		}, 700);

	});	
	
	$("#openComment").click(function (a) {
	a.preventDefault();
	$("#hiddenComment").toggle();
	//$("#openComment").css("display", "none");
	});	
	
	$("#openDispenseDate").click(function (a) {
	a.preventDefault();
	$("#customDispenseDate").toggle();
	//$("#openDispenseDate").css("display", "none");
	});	
	
	$("#openDiscount").click(function (a) {
	a.preventDefault();
	$("#discountholder").toggle();
	});	
	
	
	 
	
	
	$("#minimizeMemberBox").click(function () {
	$("#hiddenSummary").css("display", "block");
	$("#memberbox").css("visibility", "hidden");
	});	
	
	$("#minimizeSummaryBox").click(function () {
	$("#memberbox").css("visibility", "visible");
	$("#hiddenSummary").css("display", "none");
	});
	
	
    // Calculate discounts
	$("input[type=checkbox]").change(function(){
	  recalculate();
	});
	
	
	function recalculate() {
		
	    var sumDisc = 0;
	
	    $("input[type=checkbox]:checked").each(function(){
	      sumDisc += parseInt($(this).val());
	    });
		$('#totDiscount').html("(" + sumDisc + "%)");
		$('#totDiscountInput').val(sumDisc);
		
	}
	
	
	
	/*
	
		Transform 20 to 0,8
		x = 100 - 20
		x / 100
	
	
	*/
// Removed CLICK from this one:	
$(document).on('click keypress keyup blur', function(event) {
   if (!$(event.target).is("#cardscan")) {
   if (!$(event.target).is(".title, #plus1, #minus1")) {
   if (!$(event.target).is("#main")) {
   if (!$(event.target).is("#pmt1")) {
   if (!$(event.target).is("#pmt2")) {
   if (!$(event.target).is("#pmt3")) {
   if (!$(event.target).is("#submitButton")) {
   if (!$(event.target).is("#cart_img, #cart_count")) {
   if (!$(event.target).is("#paid")) {
   if (!$(event.target).is("#chipClick")) {
	// uncheck all radio boxes
	$("input:radio").attr("checked", false);
	setTimeout(function(){
		getItems();
	    computeTot();
	    }, 700);
	}
	}
	}
	}
	}
	}
	}
	}
	}
   }
});
$("#paid").on('click keypress keyup blur', function(event) {
	var aX = $('#eurcalcTOT').val();
	var bX = $('#paid').val();
	var totalX = bX - aX;
	var roundedtotalX = totalX.toFixed(2);
	$('#change').val(roundedtotalX);	
});
	getItems();
    computeTot();
  }); // end ready
  
EOD;
	pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// First lookup userdetails, incl. medicinal or not, then decide what to do (which quqeries to execute)
	$userDetails = "SELECT usageType, nationality FROM users WHERE user_id = {$user_id}";
	try
	{
		$result = $pdo3->prepare("$userDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
		$usageType = $row['usageType'];
		$nationality = $row['nationality'];
if ($_SESSION['dispsig'] == 1) {
			echo <<<EOD
<script type="text/javascript" src="scripts/SigWebTablet.js"></script>
<script type="text/javascript">
var tmr;
function onSign()
{
   var ctx = document.getElementById('cnv').getContext('2d');         
   SetDisplayXSize( 500 );
   SetDisplayYSize( 100 );
   SetJustifyMode(0);
   ClearTablet();
   tmr = SetTabletState(1, ctx, 50) || tmr;
}
function onClear()
{
   ClearTablet();
}
function onDone()
{
   if(NumberOfTabletPoints() == 0)
   {
      alert("Tienes que firmar!");
   }
   else
   {
      SetTabletState(0, tmr);
      //RETURN TOPAZ-FORMAT SIGSTRING
      SetSigCompressionMode(1);
      document.FORM1.bioSigData.value=GetSigString();
      document.FORM1.sigStringData.value += GetSigString();
      //this returns the signature in Topaz's own format, with biometric information
      //RETURN BMP BYTE ARRAY CONVERTED TO BASE64 STRING
      SetImageXSize(500);
      SetImageYSize(100);
      SetImagePenWidth(5);
      GetSigImageB64(SigImageCallback);
      document.getElementById("button2").style.background='#b6ec98';
   }
}
function SigImageCallback( str )
{
   document.FORM1.sigImageData.value = str;
}
	
</script> 
<script type="text/javascript">
window.onunload = window.onbeforeunload = (function(){
closingSigWeb()
})
function closingSigWeb()
{
   ClearTablet();
   SetTabletState(0, tmr);
}
</script>
EOD;
}
?>
<style>
#searchfield {
	background: url(images/magglass.png) no-repeat scroll 8px 10px;
	padding-left: 30px !important;
	background-color: #fff;
}
</style>
<div style='display: inline-block; text-align: left !important; float: left; position: absolute; left: 64px; margin-top: -26px;'>
<form id='searchform' autocomplete="off">
 <input type='text' name='searchfield' class='defaultinput' id='searchfield' style='width: 200px; z-index: 999999;'/>
</form>
<table id='searchtable' class='default bgwhite' style='text-align: left !important; margin-left: 0; border-left: 1px solid #ddd; border-right: 1px solid #ddd; border-top: 1px solid #ddd; margin-left: 11px; margin-top: -16px; z-index: 999999 !important;'>
</table>
</div>
<!-- Shopping cart   -->
<div class="ship_cart">
	<img src="images/shop-cart.png"><span id="cart_count">0</span>
</div>
	<!-- shooping cart popup -->
	<div  class="actionbox-npr" id = "cart_pop" title = "Cart">
		<div class='boxcomtemt'>
			<table id="memberBoxTable" >
				<tr>
					<th><strong>Product Name</strong></th>
					<th><strong>Category</strong></th>
					<th><strong>Grams</strong></th>
					<th><strong>Units</strong></th>
					<?php if($_SESSION['realWeight'] == 1){ ?>
						<th><strong>Real Grams</strong></th>
					<?php } ?>
					<th><strong>Gift (g/u)</strong></th>
					<th><strong>Price</strong></th>
				</tr>
				<tbody class="show_cart_data"></tbody>
			</table>
		</div>
	</div>
<br /><br />
<div id='dispensarymain'>
<form id="registerForm" action="" method="POST">
<div id="memberbox">
<div id="memberboxshade">
</div>
<?php
	if ($domain == 'cloud') {
		
		$topimg = "images/_$domain/ID/$user_id-front.jpg";
		
	} else {
		$topimg = "images/_$domain/members/$user_id.$photoext";
	
	}
	
	if (!file_exists($topimg)) {
		$topimg = 'images/silhouette-new.png';
	}
	
	if ($totalAmountPerWeek >= $_SESSION['highRollerWeekly']) {
		$highroller = "<br /><div class='highrollerholder'><img src='images/trophy.png' style='margin-bottom: -2px;'/> High roller</div>";
	}
	if ($usageType == 1) {
		$medicalicon = "<tr>
     <td style='padding-bottom: 7px;'><img src='images/medical-new.png' style='margin-bottom: -1px;' /></td><td>{$lang['medical-user']}</td>
    </tr>";
	} else {
		$medicalicon = "";
	}
	
	// Consumption this calendar month
	$selectSales = "SELECT SUM(quantity), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
	try
	{
		$result = $pdo3->prepare("$selectSales");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
		$amountMonth = $row['SUM(amount)'];
		$quantityMonth = $row['SUM(quantity)'];
		
		if ($quantityMonth == '') {
			$quantityMonth = 0;
		}
		
	// Determine consumption status vs limit
	$consumptionDelta = $quantityMonth - $mconsumption;
	$consumptionDeltaPlus = 0 - $consumptionDelta;
	
	// Consumption today
	$selectSales = "SELECT SUM(quantity) FROM sales WHERE userid = $user_id AND DATE(saletime) = DATE(NOW())";
	try
	{
		$result = $pdo3->prepare("$selectSales");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
		$quantityToday = $row['SUM(quantity)'];
		
	// Consumption yesterday
	$selectSales = "SELECT SUM(quantity) FROM sales WHERE userid = $user_id AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY)";
	try
	{
		$result = $pdo3->prepare("$selectSales");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
		$quantityYday = $row['SUM(quantity)'];
		
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		$bday = "<tr>
     <td style='padding-bottom: 7px;'><img src='images/birthday.png' style='margin-bottom: -4px;' /></td><td>{$lang['global-birthday']}</td>
    </tr>";
	}
	
	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1 && $exento == 0) {  // show Member w/ expiry
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if (strtotime($memberExp) == strtotime($timeNow)) {
			$expiry = "<tr>
     <td style='padding-bottom: 7px;'><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-15.png' style='margin-bottom: -2px;' /></a></td><td><a href='pay-membership.php?user_id=$user_id'>{$lang['member-expirestoday']}</a></td>
    </tr>";
		  	if ($paymentWarning == '1') {
		  	$expiry .=  "<tr>
     <td style='padding-bottom: 7px;'><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-15.png' style='margin-bottom: -2px;' /><img src='images/exclamation-15.png' style='margin-bottom: -2px; margin-left: -6px;' /></a></td><td><a href='pay-membership.php?user_id=$user_id'>{$lang['member-receivedwarning']}</a></td>
    </tr>";
		  	}
	  	} else if (strtotime($memberExp) < strtotime($timeNow)) {
		  	$expiry =  "<tr>
     <td style='padding-bottom: 7px;'><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-15.png' style='margin-bottom: -2px;' /></a></td><td><a href='pay-membership.php?user_id=$user_id'>{$lang['member-expiredon']}: $memberExpReadable.</a></td>
    </tr>";
		  	
		  	if ($paymentWarning == '1') {
		  	$expiry .=  "<tr>
     <td style='padding-bottom: 7px;'><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-15.png' style='margin-bottom: -2px;' /><img src='images/exclamation-15.png' style='margin-bottom: -2px; margin-left: -6px;' /></a></td><td><a href='pay-membership.php?user_id=$user_id'>{$lang['member-receivedwarning']}</a></td>
    </tr>";
		  	}
		  	
		}
	}
	
	if ($quantityMonth >= $mconsumption) {
		$consumptionwarning = "<tr><td style='padding-bottom: 7px;'><img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -4px;' /></td><td>{$lang['member-conslimitexc']} (+$consumptionDelta g)</td></tr>";
	} else if ($consumptionDeltaPlus < ($mconsumption * 0.1)) {
		$consumptionwarning = "<tr><td style='padding-bottom: 7px;'><img src='images/exclamation-15.png' class='warningIcon' /></td><td>{$lang['member-conslimitnear']} ($consumptionDeltaPlus g {$lang['global-remaining']})</td></tr>";
	}
	
	echo "
	<a href='profile.php?user_id=$user_id'>
	 <span class='firsttext'>#$memberno</span><br />";
	 
	if ($_SESSION['domain'] != 'cannabisclubcpt' || ($_SESSION['domain'] == 'cannabisclubcpt' && $_SESSION['userGroup'] == 1)) {
		
		echo "<a href='#' id='chipClick'><img src='images/rfid-new.png' onclick='event.preventDefault();' /></a>";
		
	}
	
	echo "
	 <span class='nametext2'>$first_name $last_name</span>
    </a><br />
     <table style='margin-top: 10px; vertical-align: top;'>
      <tr>
       <td>
        <div style='display: inline-block; line-height: 11px;'>
         <img src='$topimg' width='116' />
         $highroller
        </div>
       </td>
       <td style='vertical-align: top;'>
        <table style='margin-left: 16px;'>
         <tr>
          <td style='padding-bottom: 7px;'><img src='images/new-flag.png' style='margin-bottom: -4px; margin-right: 10px;' /></td>
          <td>$nationality</td>
         </tr>
         $medicalicon
         <tr>
          <td style='padding-bottom: 7px;'><img src='images/stats.png' style='margin-bottom: -2px; margin-right: 10px;' /></td>
          <td style='padding-bottom: 7px;'>{$expr(number_format($quantityMonth,1))} / {$expr(number_format($mconsumption,0))} g.<br /><span class='yellow'>{$lang['dispensary-today']}: {$expr(number_format($quantityToday,1))} g<br />{$lang['dispensary-yesterday']}: {$expr(number_format($quantityYday,1))} g </span></td>
         </tr>
         $bday
         $expiry
         $consumptionwarning
        </table>
       </td>
      </tr>
     </table>";
	?>
 	   <input type='hidden' id='eurcalcTOTexp' name='euroTOT' />
 	   <input type='hidden' name='dispense' value='done' /> 	   
 	   <input type='hidden' name='paidUntil' value='<?php echo $paidUntil; ?>' /> 	   
  	   <input type='hidden' name='user_id' value='<?php echo $user_id; ?>' />
  	   <input type='hidden' name='realCredit' id='realCredit' value='<?php echo $realCredit; ?>' />
  	   <input type='hidden' name='realNewCredit' id='realNewCredit' value='<?php echo $realNewCredit; ?>' />
  	   <input type='hidden' name='totDiscountInput' id='totDiscountInput' value='' />
  	   <input type='hidden' name='discType' id='discType' />
<br />  	   
  	   <table id='memberBoxTable'>
  	    <tr>
  	     <td class='dispensetd'><input type='number' lang='nb' class='blankinput first notZero' id='grcalcTOT' name='gramsTOT' value="test" readonly /> g</td>
  	     <td class='dispensetd'><input type='number' lang='nb' class='blankinput first notZero' id='unitcalcTOT' name='unitsTOT' value="0" readonly /> u</td>
  	     <td class='dispensetd'><input type='number' lang='nb' class='blankinput' id='eurcalcTOT' name='eurcalcTOT' value="0" readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
	    </tr>  	   
  	    <tr>
  	     <td class='dispensetd' colspan='3'>
  	      <table style='width: 100%;'>
  	       <tr>
  	        <td class='saldoheader'><?php echo $lang['global-credit']; ?>:</td>
  	        <td>
  	        <?php if ($credit < 0) { ?>
  	        <input type='number' lang='nb' class="specialInput" style='color: red !important;' id='credit' name='credit' value='0' readonly /> <?php echo $_SESSION['currencyoperator'] ?>
  	        <?php } else { ?>
  	        <input type='number' lang='nb' class="specialInput" id='credit' name='credit' value='<?php echo $credit; ?>' readonly /> <?php echo $_SESSION['currencyoperator'] ?>
  	        <?php } ?>
  	        </td>
  	        <td class='saldoheader' style='text-align: right;'><?php echo $lang['new-normal']; ?>:</td>
  	        <td>
  	        <?php if ($credit < 0) { ?>
  	        <input type='number' lang='nb' class='specialInput' style='color: red !important;' id='newcredit' name='newcredit' value='0' readonly /> <?php echo $_SESSION['currencyoperator'] ?>
  	        <?php } else { ?>
  	        <input type='number' lang='nb' class='specialInput' id='newcredit' name='newcredit' value='<?php echo $credit; ?>' readonly /> <?php echo $_SESSION['currencyoperator'] ?>
  	        <?php } ?>
  	        </td>
  	       </tr>
<?php if ($_SESSION['dispDonate'] == 1) { ?>
  	       <tr style='display: none;' class='donateField'>
  	        <td class='saldoheader'><?php echo $lang['donate']; ?>:</td>
  	        <td style='text-align: right; padding-right: 13px;'><input type='text' lang='nb' class='twoDigit defaultinput-no-margin' id='newDonation' name='newDonation' value='0' /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	       </tr>
<?php } ?>
  	      </table>
  	     </td>
	    </tr>  	   
  	    <tr>
  	     <td colspan='3'>
  	      <table style='width: 100%;'>
  	       <tr>
  	        <td style='width: 50%;'><a href="#" id="openDispenseDate" class='expandable'><img src='images/date-new.png' style='margin-bottom: -3px; margin-right: 3px;' /> <?php echo $lang['pur-date']; ?></a></td>
  	        <td style='width: 50%; text-align: right;'><a href="#" id="openComment" class='expandable'><img src='images/comment-new.png' style='margin-bottom: -3px; margin-right: 3px;' /> <?php echo $lang['comments']; ?></a></td>
  	       </tr>
  	       <tr>
  	        <td colspan='2'>
  	         <span id='customDispenseDate' class='expanded' style='display: none;'>
		      <input type="text" lang="nb" name="owndate" id="datepicker" class="fiveDigit defaultinput" placeholder="<?php echo $lang['pur-date']; ?>" />@
		      <input type="number" lang="nb" name="hour" id="hour" class="oneDigit defaultinput" maxlength="2" placeholder="hh" />
		      <input type="number" lang="nb" name="minute" id="minute" class="oneDigit defaultinput" maxlength="2" placeholder="mm" />
   		     </span>
   		     <span class='expanded' id='hiddenComment' style='display: none;'>
              <textarea class="defaultinput" name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>
             </span>
            </td>
  	       </tr>
  	      </table>
  	     </td>
	    </tr>
<?php

	if ($_SESSION['checkoutDiscount'] == 1 && ($_SESSION['domain'] != 'broccolis' || $_SESSION['userGroup'] == 1)) {
	
?>
  	    <tr>
  	     <td colspan='3'>
  	      <table style='width: 100%;'>
  	       <tr>
  	        <td>
  	         <a href="#" id="openDiscount" class='expandable2'><img src='images/discount.png' style='margin-bottom: -4px; margin-right: 3px;' /> <?php echo $lang['apply-discount']; ?> <span id='totDiscount'>(a%)</span></a> 
  	        </td>
  	       </tr>
  	       <tr>
  	        <td class='expanded2' id='discountholder' style='display: none;'>
  	          	   <center>
 <?php if ($_SESSION['domain'] != 'faded') { ?>
       <input type="checkbox" id="fee0" name="inDiscount" value='5' />
        <label for="fee0"><span class='full2'>5%</span></label>
 <?php } ?>
       
        <input type="checkbox" id="fee1" name="inDiscount" value='10' />
        <label for="fee1"><span class='full2'>10%</span></label>
        
<?php if ($_SESSION['domain'] != 'faded') { ?>
        <input type="checkbox" id="fee2" name="inDiscount" value='20' />
        <label for="fee2"><span class='full2'>20%</span></label>
        <input type="checkbox" id="fee3" name="inDiscount" value='30' />
        <label for="fee3"><span class='full2'>30%</span></label>
<?php } ?>
        <br />
        <span style='color: #656d60;'><?php echo $lang['member-orcaps'] . " " . $lang['member-discount']; ?> <?php echo $_SESSION['currencyoperator'] ?>:&nbsp; <input type='number' name='eurdiscount' id='eurdiscount' class='twoDigit defaultinput-no-margin' style='margin: 5px 0;' /></span>
        
  	   </center>
  	        </td>
  	       </tr>
  	      </table>
  	     </td>
	    </tr>
<?php } else {
		echo "<input type='hidden' name='eurdiscount' id='eurdiscount' class='twoDigit' value='0' />";
	}
	if ($_SESSION['creditOrDirect'] == 0 && $_SESSION['domain'] != 'testsystem') { ?>
<!--  	    <tr>
  	     <td colspan='3'><span style='font-size: 14px; display: inline-block; text-transform: uppercase; font-weight: 600; color: #626d5d;'><?php echo $lang['paid-by']; ?></td>
  	    </tr>-->
   	    <tr>
  	     <td><input type="radio" id="pmt1" name="pmtType" value='1' />
          <label for="pmt1"><span class='full' id="pmt1trigger"><?php echo $lang['cash']; ?></span></label>
         </td>
  	     <td>
<?php if ($_SESSION['bankPayments'] == 1) { ?>
          <input type="radio" id="pmt2" name="pmtType" value='2' />
          <label for="pmt2"><span class='full' id="pmt2trigger"><?php echo $lang['card']; ?></span></label>
<?php } else { ?>
          <input type="radio" id="pmt2" name="pmtType" value='2' style="visibility: hidden;" />
          <label for="pmt2" style="visibility: hidden;" ><span class='full' id="pmt2trigger"><?php echo $lang['card']; ?></span></label>
<?php } ?>
  	     </td>
  	     <td>
          <input type="radio" id="pmt3" name="pmtType" value='3' />
          <label for="pmt3"><span class='full' id="pmt3trigger"><?php echo $lang['global-credit']; ?></span></label><br />
  	     </td>
	    </tr>
<?php } ?>
  	    <tr id='changeTable' style='display: none;'>
  	     <td class='dispensetd' colspan='3'>
  	      <table style='width: 100%;'>
  	       <tr>
  	        <td class='saldoheader'><?php echo $lang['paid']; ?>:</td>
  	        <td>
  	         <input type='number' lang='nb' class='twoDigit defaultinput-no-margin' id='paid' placeholder='<?php echo $_SESSION['currencyoperator'] ?>' />
  	        </td>
  	        <td class='saldoheader' style='text-align: right;'><?php echo $lang['change-money']; ?>:</td>
  	        <td>
   	         <input type='number' lang='nb' class='twoDigit defaultinput-no-margin' id='change' name='change' placeholder='<?php echo $_SESSION['currencyoperator'] ?>' readonly />
  	        </td>
  	       </tr>
  	      </table>
  	     </td>
	    </tr>
	    
	    <?php
	if ($_SESSION['iPadReaders'] == 0) {
		
		// Chip
		if ($_SESSION['dispsig'] == 0) {
			echo <<<EOD
   	    <tr>
  	     <td colspan='3'><input type="text" class="donateOrNot defaultinput" id="cardscan" name="cardid" maxlength="30" placeholder="{$lang['global-scantoconfirm']}" /></td></tr>
EOD;
		// Topaz
		} else if ($_SESSION['dispsig'] == 1) {
			echo <<<EOD
   	    <tr>
  	     <td colspan='3'><canvas id="cnv" name="cnv" class='defaultinput' onclick="javascript:onSign()"></canvas><br />
<center><input id="button2" name="DoneBtn" class='cta1' style='margin: 0;' type="button" value="Guardar firma" onclick="javascript:onDone()" /></center></td></tr>
EOD;
			
		}
	}
?>
	   </table>
  	   
<br />
<button type="submit" class='oneClick cta6' name='oneClick' id='submitButton'><img src='images/checkmark-new.png' style='margin-bottom: -10px;' />&nbsp;<?php echo $lang['global-save']; ?></button><br /><br />
<a href="#" class='cta7' onclick="event.preventDefault(); document.getElementById('registerForm').reset();"><img src='images/cancel.png' style='margin-bottom: -6px;' />&nbsp;<?php echo $lang['global-delete']; ?></a><br /><br />
<center><a href="barcode-dispense.php?user_id=<?php echo $user_id; ?>"><img src="images/barcode-new.png" /></a></center>
</div> <!-- end memberbox -->
<div class="clearfloat"></div>
<?php
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
		
		if ($categoryid == 1) {
			
		
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "g.name ASC";
	}
		
	$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, g.description, g.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt, p.medDiscount FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY $sorting;";
	
	try
	{
		$result = $pdo3->prepare("$selectFlower");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}


		
	// Start FLOWERS menu
	if ($data) {
		
		$i = 0;
		echo "<h3 class='title' onClick='load1()' style='cursor: pointer;'><img src='images/icon-flower.png' height='30' style='margin-right: 10px; margin-bottom: -8px;' />{$lang['global-flowerscaps']} <img src='images/expand.png' id='plus1' width='21' style='margin-left: 5px;' /><img src='images/shrink.png' id='minus1' width='21' style='margin-left: 5px; display: none;' /><span id='spinner1'></span><input type='hidden' name='click1' id='click1' class='clickControl' value='0' /><input type='hidden' name='clickX1' id='clickX1' value='0' /></h3><span id='menu1'></span>";
	
	}
	
		echo <<<EOD
<script>

function load1(){
	
	if ($("#click1").val() == 0 && $("#clickX1").val() == 0) {
		$(".clickControl").val(1);
		$("#clickX1").val(1);
		$("#plus1").hide();
		$("#minus1").show();
		
	// Add 'Loading' text
	setInterval(function() {
	$("#spinner1").append(".");
  }, 1000);
  
  
	var dayJSID = parseInt($("#currenti").val());
	var fullmenu = $fullmenu;	
    $.ajax({
      type:"post",
      url:"getflowers-2.php?i="+dayJSID+"&uid=$user_id&discount=$discount&usageType=$usageType",
      datatype:"text",
      success:function(data)
      {
			$("#spinner1").remove();
	       	$('#menu1').append(data);
	       	if(fullmenu == 1){
	       		$('#menu1').show();
	       		$("#clickX1").val(3);
	       	}
			$(".clickControl").val(0);
			$("#click1").val(1);
      }
    });
    
    
	} else {
		
		if ($("#clickX1").val() == 1) {
			$("#clickX1").val(2);
			$('#menu1').hide();
			$("#plus1").show();
			$("#minus1").hide();
		} else if ($("#clickX1").val() == 2) {
			$('#menu1').show();			
			$("#clickX1").val(3);
			$("#plus1").hide();
			$("#minus1").show();
		} else {
			$('#menu1').hide();			
			$("#clickX1").val(2);
			$("#plus1").show();
			$("#minus1").hide();
		}
	}
    
};
/*var fullmenu1 = $fullmenu;
if(fullmenu1 == 1){
	window.onload = (event) =>{
	    load1();
	}
}*/
</script>
EOD;
			 
		} else if ($categoryid == 2) {
			
		
			
			
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "h.name ASC";
	}
	
  	$selectExtract = "SELECT h.extractid, h.name, h.extract, h.description, h.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY $sorting;";
	try
	{
		$result = $pdo3->prepare("$selectExtract");
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
  
		echo "<h3 class='title' onClick='load2()' style='cursor: pointer;'><img src='images/icon-extract.png' height='30' style='margin-right: 10px; margin-bottom: -8px;' />{$lang['global-extractscaps']} <img src='images/expand.png' id='plus2' width='21' style='margin-left: 5px;' /><img src='images/shrink.png' id='minus2' width='21' style='margin-left: 5px; display: none;' /><span id='spinner2'></span><input type='hidden' name='click2' id='click2' class='clickControl' value='0' /><input type='hidden' name='clickX2' id='clickX2' value='0' /><input type='hidden' name='loaded2' id='loaded2' value='0' /></h3><span id='menu2'></span>";
	
	}
  
		echo <<<EOD
<script>
function load2(){
	
	if ($("#click2").val() == 0 && $("#clickX2").val() == 0) {
		$(".clickControl").val(1);
		$("#clickX2").val(1);
		$("#plus2").hide();
		$("#minus2").show();
		
	// Add 'Loading' text
	setInterval(function() {
	$("#spinner2").append(".");
  }, 1000);
  
  
	var dayJSID = parseInt($("#currenti").val());
	var fullmenu = $fullmenu;	
    $.ajax({
      type:"post",
      url:"getextracts-2.php?i="+dayJSID+"&uid=$user_id&discount=$discount&usageType=$usageType",
      datatype:"text",
      success:function(data)
      {
			$("#spinner2").remove();
	       	$('#menu2').append(data);
	       	if(fullmenu == 1){
	       		$('#menu2').show();
	       		$("#clickX2").val(3);
	       	}
			$(".clickControl").val(0);
			$("#click2").val(1);
      }
    });
    
    
	} else {
		
		if ($("#clickX2").val() == 1) {
			$("#clickX2").val(2);
			$('#menu2').hide();
			$("#plus2").show();
			$("#minus2").hide();
		} else if ($("#clickX2").val() == 2) {
			$('#menu2').show();			
			$("#clickX2").val(3);
			$("#plus2").hide();
			$("#minus2").show();
		} else {
			$('#menu2').hide();			
			$("#clickX2").val(2);
			$("#plus2").show();
			$("#minus2").hide();
		}
	}
		$("#loaded2").val(1);
};
/*var fullmenu2 = $fullmenu;
if(fullmenu2 == 1){
	window.onload = (event) =>{
		load2();
		// $(document).ajaxStop(function(){ 
		// 	    setTimeout(load2(), 2000);
		// });
	}
}*/
</script>
EOD;

		
		} else {
			
			
	
		if ($_SESSION['menusortdisp'] == 0) {
			$sorting = "p.salesPrice ASC";
		} else {
			$sorting = "pr.name ASC";
		}
		
		// For each cat, look up products
	  	$selectProduct = "SELECT pr.productid, pr.name, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY $sorting;";
	  	
	  	
		try
		{
			$result = $pdo3->prepare("$selectProduct");
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
		
		echo "<h3 class='title' onClick='load($categoryid,$type)' style='cursor: pointer;'>$name <img src='images/expand.png' id='plus$categoryid' width='21' style='margin-left: 5px;' /><img src='images/shrink.png' id='minus$categoryid' width='21' style='margin-left: 5px; display: none;' /><span id='spinner$categoryid'></span><input type='hidden' name='click$categoryid' id='click$categoryid' class='clickControl' value='0' /><input type='hidden' name='clickX$categoryid' id='clickX$categoryid' value='0' /><input type='hidden' id='sort_type$categoryid' value='$type'></h3><span id='menu$categoryid'></span>";
		
		}
		
		echo <<<EOD
<script>

/*var fullmenu3 = $fullmenu;
if(fullmenu3 == 1){
	setTimeout(load($categoryid,$type), 2000);
	
}*/

function load(cat,type){ 
	
	if ($("#click"+cat).val() == 0 && $("#clickX"+cat).val() == 0) {
		$(".clickControl").val(1);
		$("#clickX"+cat).val(1);
		$("#plus"+cat).hide();
		$("#minus"+cat).show();
		
	// Add 'Loading' text
	setInterval(function() {
	$("#spinner"+cat).append(".");
  }, 1000);
  
  
	var dayJSID = parseInt($("#currenti").val());	
	var fullmenu = $fullmenu;
    $.ajax({
      type:"post",
      url:"getothers-2.php?i="+dayJSID+"&cat="+cat+"&type="+type+"&uid=$user_id&discount=$discount&usageType=$usageType",
      datatype:"text",
      success:function(data)
      {
			$("#spinner"+cat).remove();
	       	$('#menu'+cat).append(data);
	       	if(fullmenu == 1){
	       		$('#menu'+cat).show();
	       		$("#clickX"+cat).val(3);
	       	}
			$(".clickControl").val(0);
			$("#click"+cat).val(1);
      }
    });
    
    
	} else {
		
		if ($("#clickX"+cat).val() == 1) {
			$("#clickX"+cat).val(2);
			$('#menu'+cat).hide();
			$("#plus"+cat).show();
			$("#minus"+cat).hide();
		} else if ($("#clickX"+cat).val() == 2) {
			$('#menu'+cat).show();			
			$("#clickX"+cat).val(3);
			$("#plus"+cat).hide();
			$("#minus"+cat).show();
		} else {
			$('#menu'+cat).hide();			
			$("#clickX"+cat).val(2);
			$("#plus"+cat).show();
			$("#minus"+cat).hide();
		}
	}
    
};

/*var fullmenu3 = $fullmenu;
if(fullmenu3 == 1){
	window.onload = (event) =>{
	    setTimeout(load($categoryid,$type), 2000);
	}
}*/

</script>
EOD;
		
	}

echo <<<EOD
<script>

$(document).ready(function() {

	var element = $(".title");
	var i = 0;
	var delay = 5000; //3 Seconds
	var fullmenu = $fullmenu;

	if(fullmenu == 1){
		function change() {
	    	if (i == element.length && interval) {
		      	clearInterval(interval);
		    }
	    		element.eq(i++).trigger('click');
	  	}
	}

	//Execute on page load
	if(fullmenu == 1){
		change();
	}

	//On interval  
	var interval = setInterval(change, delay);
});

</script>
EOD;

}
	
?>
	  <div class="clearFloat"></div>
<input type='hidden' id='currenti' value='0' />		
	
<?php if ($_SESSION['dispsig'] == 1) { ?>
<INPUT TYPE=HIDDEN NAME="bioSigData">
<INPUT TYPE=HIDDEN NAME="sigImgData">
<INPUT TYPE=HIDDEN NAME="sigStringData" />
<INPUT TYPE=HIDDEN NAME="sigImageData" />
<?php } ?>
	
</form>
</div>
<?php
} else {
	
	// Did this page re-submit with a form? If so, check & store details
	
	// Remember to save with direct or credit flag!!!!!!!!!!!!!! 1 = cash, 2 = tarjeta, 3 = cash. dB field called 'direct'.
	if (isset($_POST['dispense'])) {
		

		
  	    $user_id = $_POST['user_id'];
		$euroTOT = $_POST['euroTOT'];
		$eurcalcTOT = $_POST['eurcalcTOT'];
		$gramsTOT = $_POST['gramsTOT'];
		$unitsTOT = $_POST['unitsTOT'];
		$paidTOT = $_POST['paidTOT'];
		$gramsTOT = $_POST['gramsTOT'];
		$realQuantity = $_POST['realgramsTOT'];
		$adminComment = $_POST['adminComment'];
		$newcredit = $_POST['newcredit'];
		$credit = $_POST['credit'];
		$realCredit = $_POST['realCredit'];
		$realNewCredit = $_POST['realNewCredit'];		
  	    $day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$paidUntil = $_POST['paidUntil'];	
		$totDiscountInput = $_POST['totDiscountInput'];	
		$pmtType = $_POST['pmtType'];
		$owndate = $_POST['owndate'];
		$hour = $_POST['hour'];
		$minute = $_POST['minute'];
		$eurdiscount = $_POST['eurdiscount'];
		
			
		// calulate all totals on server side

		$saleGrams = 0;
	    $saleUnits = 0;
	    $saleEuro = 0;
	    $unitExist = 0;
	    $gramExist = 0;

  	   foreach($_POST['sales'] as $sale) {

				 	$selectCats = "SELECT type from categories WHERE id=".$sale['category'];
					
					try
					{
						$resultCat = $pdo3->prepare("$selectCats");
						$resultCat->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$cat_row = $resultCat->fetch();
						$cat_type = $cat_row['type'];

						if ($cat_type == 0 && $sale['category'] > 2) {
							// units
							$saleUnits = $saleUnits + $sale['grams'] +  $sale['grams2'];
							if($sale['grams'] > 0 || $sale['grams2'] > 0){
								$unitExist = 1;
							}
						} else {
							// grams
							$saleGrams = $saleGrams + $sale['grams'] +  $sale['grams2'];
							if($sale['grams'] > 0 || $sale['grams2'] > 0){
								$gramExist = 1;
							}
						}

						$saleEuro = $saleEuro + $sale['euro'];
						
						if ($_SESSION['domain'] == 'greengoat') {
							
					// code update start by konstant for dispense validation on 28-02-2022
						if($sale['grams'] > 0 && ($sale['euro'] == 0 || $sale['euro'] == '')){
							$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
							header("Location: new-dispense-2.php?user_id=$user_id");
							exit();
						}
					// code update end by konstant for dispense validation on 28-02-2022

					// code update start by konstant for dispense validation on 09-03-2022
					if($sale['grams'] > 0){
						if($sale['volume_discount'] != 0 || $sale['volume_discount_amount'] != 0){
							if($sale['euro'] != $sale['volume_discount']){
								$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
								header("Location: new-dispense-2.php?user_id=$user_id");
								exit();
							}
							if($sale['grams'] != $sale['volume_discount_amount']){
								$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
								header("Location: new-dispense-2.php?user_id=$user_id");
								exit();
							}
						}/*else{
							$sale_price = $sale['ppg'] * $sale['grams'];
							if($sale['euro'] != $sale_price){
								$_SESSION['errorMessage'] = "4 Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
								header("Location: new-dispense-2.php?user_id=$user_id");
								exit();
							}
						}*/
					}	

							
						}


		}
		
						if ($_SESSION['domain'] == 'greengoat') {
							
		// code update start  by konstant for dispense validation on 08-04-2022
		if($eurcalcTOT != $saleEuro){
			$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
			header("Location: new-dispense-2.php?user_id=$user_id");
			exit();
		}
		if($gramsTOT != $saleGrams){
			$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
			header("Location: new-dispense-2.php?user_id=$user_id");
			exit();
		}		
		if($unitsTOT != $saleUnits){
			$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
			header("Location: new-dispense-2.php?user_id=$user_id");
			exit();
		}
		// code update end by konstant for dispense validation on 08-04-2022
		//echo $unitExist." ".$gramExist; die;
		// check totals on server side

						}


		//echo $unitExist." ".$gramExist; die;
		// check totals on server side

		if($gramExist){
			if($saleGrams == 0){
				$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
				header("Location: new-dispense-2.php?user_id=$user_id");
				exit();
			}
		}		

		if($unitExist){
			if($saleUnits == 0){
				$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
				header("Location: new-dispense-2.php?user_id=$user_id");
				exit();
			}
		}



		if ($_SESSION['dispDonate'] == 1) {
			$totalCheck =  $saleGrams + $saleUnits + $_POST['newDonation'];
		}else{
			$totalCheck =  $saleGrams + $saleUnits;
		}
		
		if($totalCheck == 0){
			$_SESSION['errorMessage'] = "Something went wrong! Please re-do the dispense. / Algo ha ido mal. Por favor intentalo de nuevo.";
			header("Location: new-dispense-2.php?user_id=$user_id");
			exit();
		}
		
		
		if ($paidUntil == '') {
			$paidUntil = date('Y-m-d H:i:s');
		}

		// KONSTANT CODE UPDATE BEGIN
		if($_POST['discType'] != 5 && $_POST['discType'] != 7){
	    	$discType = $_POST['discType'];  
		}
	   
			foreach($_POST['sales'] as $sale) {
					if(!empty($sale['grams2']) && $sale['grams2'] != ''){
				  		$disc_arr['gift_value'][] = $sale['grams2'];
				  	}
				  	if(!empty($sale['volume_discount']) && $sale['volume_discount'] != ''){
				  		$disc_arr['volume_discount'][] = $sale['volume_discount'];
				  	}
				  		$disc_arr['discType'] = $sale['discType'];
				  		$disc_arr['discPercentage'] = $sale['discPercentage'];
				  	if(!empty($sale['happy_hour_discount']) && $sale['happy_hour_discount'] != ''){
				  		$disc_arr['happy_hour_discount'][] = $sale['happy_hour_discount'];
				  	}
			}
		
			 if(!empty($disc_arr['gift_value'])){
				$giftDiscount = 7;
			 }
			 if(!empty($disc_arr['volume_discount'])){
			 	$volumeDiscount = 6;
			 }			 
			 if(!empty($disc_arr['happy_hour_discount'])){
			 	$happyhourDiscount = 3;
			 }
			 if($disc_arr['discType'] != ''){
			 	$discountType = $disc_arr['discType'];
			 }			 
			 if($disc_arr['discPercentage'] != ''){
			 	$discountPercent = $disc_arr['discPercentage'];
			 }
			
	    // KONSTANT CODE UPDATE END
		if ($pmtType == '' || $pmtType == 0) {
			$pmtType = 3;
		}
		
		if ($_SESSION['domain'] == 'testsystem') {
			$pmtType = 1;
		}
		
		if ($owndate != '') {
			
			$fulldate = "$owndate $hour:$minute";
			$saletime = date("Y-m-d H:i:s", strtotime($fulldate));
			
		} else {
		
			$saletime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????
		}
		
		
		// Look up user credit
		$userCredit = "SELECT credit, creditEligible, maxCredit FROM users WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$userCredit");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $result->fetch();
			$creditLookup = $row['credit'];
			$newCreditCalc = $creditLookup - $eurcalcTOT;
			$creditEligible = $row['creditEligible'];
			$maxCredit = $row['maxCredit'];
			
			/* 
			
				Credit: 	 8,50
				Purchase: 	38,50
				Newcredit: -30,00
				
			
			*/
			
			if ($newCreditCalc < 0 && $creditEligible == 0 && $pmtType == 3) {
			
				$_SESSION['errorMessage'] = $lang['credit-not-sufficient'] . "!";
				
				pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				exit();
	
				
			} else if ($creditEligible == 1 && $newCreditCalc < (0 - $maxCredit) && $pmtType == 3) {
			
				$_SESSION['errorMessage'] = $lang['credit-exceeded'] . ": " .  $maxCredit;
				
				pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				exit();
				
			}
			
		if ($pmtType == 3) {
			
			// Query to update user credit
			$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
				$newCreditCalc, $user_id
				);
					
			try
			{
				$result = $pdo3->prepare("$updateUser")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO sales (saletime, operatorid, userid, amount, amountPaid, quantity, realQuantity, units, adminComment, settled, settledTo, creditBefore, creditAfter, expiry, discount, direct, discounteur) VALUES ('%s', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%f', '%f', '%s', '%f', '%d', '%f');",
		  	$saletime, $operatorId, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $realQuantity, $unitsTOT, $adminComment, $saletime, '1', $creditLookup, $newCreditCalc, $paidUntil, $totDiscountInput, $pmtType, $eurdiscount);
		  	
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
				
			$saleid = $pdo3->lastInsertId();
						// KONSTANT CODE UPDATE BEGIN
			if($discountType < 5){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $discountType, $discountPercent);
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			if($totDiscountInput !='' && !empty($totDiscountInput)){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, 5, $totDiscountInput);
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// query to add gift discounts in sales_discount table
			
		
			if($giftDiscount){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $giftDiscount, '');
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// query to add volume discounts in sales_discount table			
			if($volumeDiscount){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $volumeDiscount, '');
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// query to add happy hour discounts in sales_discount table			
			if($happyhourDiscount){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $happyhourDiscount, '');
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// KONSTANT CODE UPDATE END
		  	$query = sprintf("INSERT INTO f_sales (saletime, userid, amount, amountPaid, quantity, realQuantity, units, adminComment, settled, settledTo, creditBefore, creditAfter, expiry, discount, direct) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%f', '%f', '%s', '%f', '%d');",
		  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $realQuantity, $unitsTOT, $adminComment, $saletime, '1', $creditLookup, $newCreditCalc, $paidUntil, $totDiscountInput, $pmtType);
		  	
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
				
				
		
		} else {
			
			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO sales (saletime, operatorid, userid, amount, amountPaid, quantity, realQuantity, units, adminComment, settled, settledTo, expiry, discount, direct, discounteur) VALUES ('%s', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%s', '%f', '%d', '%f');",
		  	$saletime, $operatorId, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $realQuantity, $unitsTOT, $adminComment, $saletime, '1', $paidUntil, $totDiscountInput, $pmtType, $eurdiscount);
		  	
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
				
			$saleid = $pdo3->lastInsertId();
			// KONSTANT CODE UPDATE BEGIN
			if($discountType < 5){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $discountType, $discountPercent);
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			if($totDiscountInput !='' && !empty($totDiscountInput)){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, 5, $totDiscountInput);
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// query to add gift discounts in sales_discount table
		
			if($giftDiscount){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, 7, '');
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// query to add volume discounts in sales_discount table
			if($volumeDiscount){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $volumeDiscount, '');
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// query to add happy hour discounts in sales_discount table			
			if($happyhourDiscount){
				$disc_query = sprintf("INSERT INTO sales_discount (salesId, discountType, discountPercentage) VALUES ('%d', '%s', '%f');",
			  	$saleid, $happyhourDiscount, '');
			
				try
				{
					$disc_result = $pdo3->prepare("$disc_query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			// KONSTANT CODE UPDATE END
			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO f_sales (saletime, userid, amount, amountPaid, quantity, realQuantity, units, adminComment, settled, settledTo, expiry, discount, direct) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%s', '%f', '%d');",
		  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $realQuantity, $unitsTOT, $adminComment, $saletime, '1', $paidUntil, $totDiscountInput, $pmtType);
		  	
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
		
		
	foreach($_POST['sales'] as $sale) {
		$name = $sale['name'];
		$category = $sale['category'];
		$productid = $sale['productid'];
		$purchaseid = $sale['purchaseid'];
		$grams = $sale['grams'];
		$grams2 = $sale['grams2'];
		$realGrams = $sale['realGrams'];
		// KONSTANT CODE UPDATE BEGIN
		$salediscType = $sale['discType'];
		$discPercentage = $sale['discPercentage'];
		$happy_hour_discount = $sale['happy_hour_discount'];
		$volume_discount = $sale['volume_discount'];
	    $volume_per_discount = $sale['volume_per_discount']; 
		if ($realGrams == '' || $realGrams == 0) {
			$realGrams = $grams;
		}
		if ($totDiscountInput > 0) {
			$euro = $sale['euro'] * ((100 - $totDiscountInput) / 100);
		} else {
			$euro = $sale['euro'];
		}
		$gramsTot = $grams + $grams2;
		
		if ($gramsTot > 0) {
			
			if ($grams > 0 && $grams2 > 0) {
				
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, realQuantity, amount, purchaseid, discountType, discountPercentage, happyhourDiscount, volumeDiscount) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d', '%d', '%f', '%f', '%f');",
			  	$saleid, $category, $productid, $grams, $realGrams, $euro, $purchaseid, $salediscType, $discPercentage, $happy_hour_discount, $volume_per_discount);
			  
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
				
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity,  discountType, discountPercentage) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f', '%d', '%f');",
			  	$saleid, $category, $productid, $grams2, '0', $purchaseid, $grams2, 7, '');
			  
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
				
			} else {
			
				if ($grams  == 0) {
					
					$grams = $grams2;
					$realGrams = $grams2;
					
				}
	    	
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, realQuantity, amount, purchaseid, discountType, discountPercentage, happyhourDiscount, volumeDiscount) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d', '%d', '%f', '%f', '%f');",
			  	$saleid, $category, $productid, $grams, $realGrams, $euro, $purchaseid, $salediscType, $discPercentage, $happy_hour_discount, $volume_per_discount);
			  
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
	}
	
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		10, $logTime, $user_id, $_SESSION['user_id'], $eurcalcTOT, $creditLookup, $newCreditCalc);
		
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
				//Remove all entries in from summary table
				$query = sprintf("DELETE from savesales_details where user_id = '%d'", $user_id);
				
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
				$query = sprintf("DELETE from saveDispense_summary where user_id = '%d'", $user_id);
				
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
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['dispense-added'];
		header("Location: dispense.php?saleid=" . $saleid);
		exit();
	}
	/***** FORM SUBMIT END *****/
	
	
	// Get the card ID
	if ($_POST['cardid'] != '') {
		
		$cardid = $_POST['cardid'];
		
		if ($cardid == '') {
			
				$_SESSION['errorMessage'] = $lang['scan-error'];
			
		} else {
		
			// Query to look up user
			$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid = '$cardid'")->fetchColumn();
			
			if ($rowCount == 0) {
				// Query to look up user
				$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid2 = '{$cardid}'")->fetchColumn();
				
				if ($rowCount == 0) {
					// Query to look up user
					$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid3 = '{$cardid}'")->fetchColumn();
					
					if ($rowCount == 0) {
				   		handleError($lang['error-keyfob'],"");
					} else {
						$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid3 = '{$cardid}'");
					}
					
				} else {
					$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid2 = '{$cardid}'");
				}
	
				
			} else {
				$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid = '{$cardid}'");
			}
			
					
			$result->execute();
			
			$row = $result->fetch();
				$user_id = $row['user_id'];
				
			// Check if chip is registered more than once
			if ($rowCount > 1) {
				
				$_SESSION['errorMessage'] = $lang['chip-registered-more-than-once'];
				header("Location: duplicate-chip.php?cardid=$cardid");
				exit();
			
			}
		}
		
	} else if (isset($_POST['userSelect'])) {
		$user_id = $_POST['userSelect'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nomember'],"");
	}
	
	// Check if member is eligible for dispensing
	$userDetails = "SELECT userGroup, paymentWarning, paymentWarningDate, paidUntil, exento FROM users WHERE user_id = '{$user_id}'";
	
	try
	{
		$result = $pdo3->prepare("$userDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
		$userGroup = $row['userGroup'];
		$paymentWarning = $row['paymentWarning'];
		$exento = $row['exento'];
		$paymentWarningDate = $row['paymentWarningDate'];
		$paidUntil = strtotime(date('Y-m-d H:i', strtotime($row['paidUntil'])));
		$nowTime = strtotime(date('Y-m-d H:i'));
		$pwd = strtotime(date('Y-m-d H:i',strtotime($paymentWarningDate)));
		
	if ($userGroup > 6) {
		$_SESSION['errorMessage'] = $lang['cannot-dispense'];
		header("Location: no-dispense.php?user_id=$user_id");
		exit();
	}
	if ($_SESSION['dispExpired'] == 0 && $exento == 0) {
		if ($paymentWarning == 1 && $nowTime > $pwd && $nowTime > $paidUntil) {
			$_SESSION['errorMessage'] = $lang['cannot-dispense'];
			header("Location: no-dispense.php?user_id=$user_id");
			exit();
		}
	}
	
	if ($_SESSION['domain'] == 'shambala' && $exento == 0 && $userGroup > 4) {
		if ($nowTime > $paidUntil) {
			$_SESSION['errorMessage'] = $lang['cannot-dispense'];
			header("Location: no-dispense.php?user_id=$user_id");
			exit();
		}
	}
	

	
/*	$cartID = 'savedCart' . $user_id;
	echo $cartID;
	echo "h: " . $_SESSION[$cartID];*/
	
	// Was a donation made, or a cart saved?
	if (isset($_SESSION['savedSale'])) {
		
		$savedSale = $_SESSION['savedSale'];
		unset($_SESSION['savedSale']);
		
	} /*else if (isset($_SESSION[$cartID])) {
		
		$savedSale = $_SESSION[$cartID];
		unset($_SESSION[$cartID]);
		
	}*/
	
	// If membertime is lower than first sale date, use first sale date instead
	$userDetails = "SELECT registeredSince FROM users WHERE user_id = '{$user_id}'";
	
	try
	{
		$result = $pdo3->prepare("$userDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
		$memberSince = date('Y-m-d', strtotime($row['registeredSince']));
	
	
		// Look up user details for showing profile on the Sales page
		$userDetails2 = "SELECT memberno, paidUntil, userGroup, first_name, last_name, datediff(curdate(),'$memberSince') AS daysMember, paymentWarning, paymentWarningDate, mconsumption, day, month, year, credit, creditEligible, discount, photoext, cardid, nationality, exento FROM users WHERE user_id = '{$user_id}'";
	try
	{
		$result = $pdo3->prepare("$userDetails2");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row2 = $result->fetch();
		$memberno = $row2['memberno'];
		$first_name = $row2['first_name'];
		$last_name = $row2['last_name'];
		$paidUntil = $row2['paidUntil'];
		$userGroup = $row2['userGroup'];
		$day = $row2['day'];
		$month = $row2['month'];
		$year = $row2['year'];
		$mconsumption = $row2['mconsumption'];
		$daysMember = $row2['daysMember'];
		$paymentWarning = $row2['paymentWarning'];
		$paymentWarningDate = $row2['paymentWarningDate'];
		$paymentWarningDateReadable = date('d M', strtotime($paymentWarningDate));
		$credit = $row2['credit'];
		$realCredit = $credit;
		$creditEligible = $row2['creditEligible'];
		$discount = $row2['discount'];
		$photoext = $row2['photoext'];
		$cardid = $row2['cardid'];		
		$nationality = $row2['nationality'];		
		$exento = $row2['exento'];		
		
	    if ($domain == 'cannabisclubcpt' || $domain == 'eltemplo') {
	    	$chipCheck = ", range: ['{$cardid}','{$cardid}']";
		}
		
			if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
				$giftOption = "<img src='images/free-green.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
			} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
				$giftOption = "<img src='images/gift-big.png' id='free%d' style='margin-bottom: 0; margin-left: 5px; margin-top: 10px; margin-bottom: 6px;' />";
			} else {
				$giftOption = "<span style='display: block; height: 10px;'></span><input type='hidden' value='%d' />";
			}
		
	// Query to look up total sales and find weekly average
	$selectSales = "SELECT SUM(amount) FROM sales WHERE userid = $user_id";
	try
	{
		$result = $pdo3->prepare("$selectSales");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
		$totalAmount = $row['SUM(amount)'];
		$totalAmountPerDay = $totalAmount / $daysMember;
		$totalAmountPerWeek = $totalAmountPerDay * 7;
		
		
		if ($_SESSION['keypads'] == 1) {
			
			$keypadActive = <<<EOD
$('.onScreenKeypad').keypad({
    layout: ['789' + $.keypad.CLOSE, 
        '456' + $.keypad.CLEAR, 
        '123' + $.keypad.BACK, 
        '.0' + $.keypad.SPACE]});			
EOD;
		} else {
			
			$keypadActive = '';
			
		}
// Allow negative credit

	if ($_SESSION['domain'] != 'testsystem') {
	$negCredit = <<<EOD
	
newcredit: {
				  range: [0,100000]
			  },
	
EOD;

	}
if ($creditEligible == 1) {
	$validationScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
    $(document).ready(function() {
	    
$keypadActive
	    
$.validator.addMethod('totalCheck', function(value, element, params) {
    var field_1 = $('input[name="' + params[0] + '"]').val(),
        field_2 = $('input[name="' + params[1] + '"]').val(),
        field_3 = $('input[name="' + params[2] + '"]').val();
        
        if(field_3 == null){
        	field_3 = 0;
        }
    // return parseInt(value) === parseInt(field_1) + parseInt(field_2);
    
    if ((parseFloat(field_1) + parseFloat(field_2) + parseFloat(field_3)) == 0) {
	    return false;
    } else {
	    return true;
    }
}, "Enter the number of persons (including yourself)");
	    	    
	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  cardid: {
				  required: true
				  $chipCheck
			  },
			  $noNegative
			  eurcalcTOT: {
				  range: [0,"{$_SESSION['dispenseLimit']}"]
			  },
			  gramsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT', 'newDonation']
			  },
			  unitsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT', 'newDonation']
			  },
			  pmtType: {
				  required: true,
				  range: [0,3]
			  },
			  hour: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,23]
			  },
			  minute: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,59]
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function(form) {
   $(".oneClick").attr("disabled", true);
   // form3
      setTimeout(function(){
       form.submit();
   	}, 700);
	    	  }
	  }); // end validate
EOD;
} else {
	$validationScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
    $(document).ready(function() {
	    
$keypadActive
	    	    
$.validator.addMethod('totalCheck', function(value, element, params) {
    var field_1 = $('input[name="' + params[0] + '"]').val(),
        field_2 = $('input[name="' + params[1] + '"]').val(),
        field_3 = $('input[name="' + params[2] + '"]').val();
        
        if(field_3 == null){
        	field_3 = 0;
        }
    // return parseInt(value) === parseInt(field_1) + parseInt(field_2);
    
    if ((parseFloat(field_1) + parseFloat(field_2) + parseFloat(field_3)) == 0) {
	    return false;
    } else {
	    return true;
    }
}, "Enter the number of persons (including yourself)");
	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  $negCredit
			  cardid: {
				  required: true
				  $chipCheck
			  },
			  $noNegative
			  eurcalcTOT: {
				  range: [0,"{$_SESSION['dispenseLimit']}"]
			  },
			  gramsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT', 'newDonation']
			  },
			  unitsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT', 'newDonation']
			  },
			  pmtType: {
				  required: true,
				  range: [0,3]
			  },
			  hour: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,23]
			  },
			  minute: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,59]
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function(form) {
   $(".oneClick").attr("disabled", true);
   // form4
      setTimeout(function(){
       form.submit();
   	}, 700);
	    	  }
	  }); // end validate

EOD;
}
	  
	$validationScript .= <<<EOD
	
	$("#credit").click(function () {
		$(".donateField").css("display", "table-row");
	});
	
	
	$(function(){
	    $('#chipClick').click(function() {
	        $("#cardscan").val('$cardid');
	    });
	});
	  
	  function getItems()
{
var sum = 0;
$( '.calc' ).each( function( i , e ) {
    var v = +$( e ).val();
    if ( !isNaN( v ) )
        sum += v;
} );
  var rsum = sum.toFixed(2);
  $('#grcalcTOT').val(rsum);
  $('#grcalcTOT2').val(rsum);
  $('#grcalcTOTexp').val(rsum);
var sumB = 0;
$( '.calc2' ).each( function( i , e ) {
    var vB = +$( e ).val() ;
    if ( !isNaN( vB ) )
        sumB += vB;
} );
  var rsumB = sumB.toFixed(2);
  
	    var sumDisc = 0;
	
	    $("input[type=checkbox]:checked").each(function(){
	      sumDisc += parseInt($(this).val());
	    });
		$('#totDiscount').html("(" + sumDisc + "%)");
		$('#totDiscountInput').val(sumDisc);
		
		var appliedDisc = (100 - sumDisc) / 100;
		
		var tempPrice = rsumB * appliedDisc;
		
		var eurdisc = $('#eurdiscount').val();
		
		var newPrice = tempPrice - eurdisc;		
  $('#eurcalcTOT').val(newPrice);
  $('#eurcalcTOT2').val(newPrice);
  $('#eurcalcTOTexp').val(newPrice);
  
  
var sumC = 0;
$( '.calc3' ).each( function( i , e ) {
    var vC = +$( e ).val();
    if ( !isNaN( vC ) )
        sumC += vC;
} );
$( '.calc4' ).each( function( i , e ) {
    var vD = +$( e ).val();
    if ( !isNaN( vD ) )
        sumC += vD;
} );
  var sumC = sumC.toFixed(2);
  $('#unitcalcTOT').val(sumC);
  $('#unitcalcTOT2').val(sumC);
  
  
var sumE = 0;
$( '.calc5' ).each( function( i , e ) {
    var vE = +$( e ).val();
    if ( !isNaN( vE ) )
        sumE += vE;
} );
var sumF = 0;
$( '.calc6' ).each( function( i , e ) {
    var vF = +$( e ).val();
    if ( !isNaN( vF ) )
        sumF += vF;
} );
	  sumF += sumE;
  $('#realgrcalcTOT').val(sumF);
}
EOD;
if ($_SESSION['dispDonate'] == 1) {
	
	$validationScript .= <<<EOD
	if ($credit < 0) {
	   function computeTot() {
	          var a = $('#realCredit').val();
	          var b = $('#eurcalcTOT').val();
          	  var c = $('#newDonation').val();
	          var total = ((a*1) - (b*1) + (c*1));
	          var roundedtotal = total.toFixed(2);
	          $('#realNewCredit').val(roundedtotal);
	   }
	} else {
   function computeTot() {
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
          var c = $('#newDonation').val();
	      var total = ((a*1) - (b*1) + (c*1));
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
          $('#newcredit2').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
        }
    }
   function commaChange() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
        }
   $('#newDonation').bind('keypress keyup blur change', commaChange);
EOD;
} else {
	
	$validationScript .= <<<EOD
	if ($credit < 0) {
	   function computeTot() {
	          var a = $('#realCredit').val();
	          var b = $('#eurcalcTOT').val();
	          var total = ((a*1) - (b*1));
	          var roundedtotal = total.toFixed(2);
	          $('#realNewCredit').val(roundedtotal);
	   }
	} else {
   function computeTot() {
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
	      var total = ((a*1) - (b*1));
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
          $('#newcredit2').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
        }
    }
EOD;
}
	$validationScript .= <<<EOD
	$("#pmt1").click(function () {
		setTimeout(function(){
		$("#newcredit").val($("#credit").val());
		$("#newcredit2").val($("#credit").val());
		$("#changeTable").css("display", "table-row");
		$("#paid").focus();
		}, 700);
	});	
	$("#pmt2").click(function () {
		setTimeout(function(){
		$("#newcredit").val($("#credit").val());
		$("#newcredit2").val($("#credit").val());
		 }, 700);
	});	
	$("#pmt3").click(function () {
		setTimeout(function(){
	    getItems();
	    computeTot();
	    }, 700);
	});	
	
	$("#openComment").click(function (a) {
	a.preventDefault();
	$("#hiddenComment").toggle();
	//$("#openComment").css("display", "none");
	});	
	
	$("#openDispenseDate").click(function (a) {
	a.preventDefault();
	$("#customDispenseDate").toggle();
	//$("#openDispenseDate").css("display", "none");
	});	
	
	$("#openDiscount").click(function (a) {
	a.preventDefault();
	$("#discountholder").toggle();
	});	
	
	$("#minimizeMemberBox").click(function () {
	$("#hiddenSummary").css("display", "block");
	$("#memberbox").css("visibility", "hidden");
	});	
	
	$("#minimizeSummaryBox").click(function () {
	$("#memberbox").css("visibility", "visible");
	$("#hiddenSummary").css("display", "none");
	});
		
	
    // Calculate discounts
	$("input[type=checkbox]").change(function(){
	  recalculate();
	});
	
	
	function recalculate() {
		
	    var sumDisc = 0;
	
	    $("input[type=checkbox]:checked").each(function(){
	      sumDisc += parseInt($(this).val());
	    });
		$('#totDiscount').html("(" + sumDisc + "%)");
		$('#totDiscountInput').val(sumDisc);
		
	}
	
	/*
	
		Transform 20 to 0,8
		x = 100 - 20
		x / 100
	
	
	*/
	
// Removed CLICK from this one:	
$(document).on('click keypress keyup blur', function(event) {
   if (!$(event.target).is("#cardscan")) {
   if (!$(event.target).is(".title, #plus1, #minus1")) {
   if (!$(event.target).is("#main")) {
   if (!$(event.target).is("#pmt1")) {
   if (!$(event.target).is("#pmt2")) {
   if (!$(event.target).is("#cart_img, #cart_count")) {
   if (!$(event.target).is("#pmt3")) {
   if (!$(event.target).is("#submitButton")) {
   if (!$(event.target).is("#paid")) {
   if (!$(event.target).is("#chipClick")) {
	// uncheck all radio boxes
	//$("input:radio").attr("checked", false);
		setTimeout(function(){
			getItems();
		    computeTot();
		    }, 700);
		
	}
	}
	}
	}
	}
	}
	}
	}
	}
   }
});
$("#paid").on('click keypress keyup blur', function(event) {
	var aX = $('#eurcalcTOT').val();
	var bX = $('#paid').val();
	var totalX = bX - aX;
	var roundedtotalX = totalX.toFixed(2);
	$('#change').val(roundedtotalX);	
});
	getItems();
    computeTot();
  }); // end ready
  
EOD;
	pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// First lookup userdetails, incl. medicinal or not, then decide what to do (which quqeries to execute)
	$userDetails = "SELECT usageType FROM users WHERE user_id = {$user_id}";
	try
	{
		$result = $pdo3->prepare("$userDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
	$usageType = $row['usageType'];
	
if ($_SESSION['dispsig'] == 1) {
	echo <<<EOD
<script type="text/javascript" src="scripts/SigWebTablet.js"></script>
<script type="text/javascript">
var tmr;
function onSign()
{
   var ctx = document.getElementById('cnv').getContext('2d');         
   SetDisplayXSize( 500 );
   SetDisplayYSize( 100 );
   SetJustifyMode(0);
   ClearTablet();
   tmr = SetTabletState(1, ctx, 50) || tmr;
}
function onClear()
{
   ClearTablet();
}
function onDone()
{
   if(NumberOfTabletPoints() == 0)
   {
      alert("Tienes que firmar!");
   }
   else
   {
      SetTabletState(0, tmr);
      //RETURN TOPAZ-FORMAT SIGSTRING
      SetSigCompressionMode(1);
      document.FORM1.bioSigData.value=GetSigString();
      document.FORM1.sigStringData.value += GetSigString();
      //this returns the signature in Topaz's own format, with biometric information
      //RETURN BMP BYTE ARRAY CONVERTED TO BASE64 STRING
      SetImageXSize(500);
      SetImageYSize(100);
      SetImagePenWidth(5);
      GetSigImageB64(SigImageCallback);
      document.getElementById("button2").style.background='#b6ec98';
   }
}
function SigImageCallback( str )
{
   document.FORM1.sigImageData.value = str;
}
	
</script> 
<script type="text/javascript">
window.onunload = window.onbeforeunload = (function(){
closingSigWeb()
})
function closingSigWeb()
{
   ClearTablet();
   SetTabletState(0, tmr);
}
</script>
EOD;
}
?>
<style>
#searchfield {
	background: url(images/magglass.png) no-repeat scroll 8px 10px;
	padding-left: 30px !important;
	background-color: #fff;
}
</style>
<div style='display: inline-block; text-align: left !important; float: left; position: absolute; left: 64px; margin-top: -26px;'>
<form id='searchform' autocomplete="off">
 <input type='text' name='searchfield' class='defaultinput' id='searchfield' style='width: 200px; z-index: 999999;'/>
</form>
<table id='searchtable' class='default bgwhite' style='text-align: left !important; margin-left: 0; border-left: 1px solid #ddd; border-right: 1px solid #ddd; border-top: 1px solid #ddd; margin-left: 3px; margin-top: -4px; z-index: 999999 !important;'>
</table>
</div>
<br /><br />
<!-- Shopping cart   -->
<div class="ship_cart">
	<img src="images/shop-cart.png"><span id="cart_count">0</span>
</div>
	<!-- shooping cart popup -->
	<div  class="actionbox-npr" id = "cart_pop" title = "Cart">
		<div class='boxcomtemt'>
			<table id="memberBoxTable" >
				<tr>
					<th><strong>Product Name</strong></th>
					<th><strong>Category</strong></th>
					<th><strong>Grams</strong></th>
					<th><strong>Units</strong></th>
					<?php if($_SESSION['realWeight'] == 1){ ?>
						<th><strong>Real Grams</strong></th>
					<?php } ?>
					<th><strong>Gift (g/u)</strong></th>
					<th><strong>Price</strong></th>
				</tr>
				<tbody class="show_cart_data"></tbody>
			</table>
		</div>
	</div>
<br /><br />
<div id='dispensarymain'>
<form id="registerForm" action="" method="POST">
<div id="memberbox">
<div id="memberboxshade">
</div>
<?php
	if ($domain == 'cloud') {
		
		$topimg = "images/_$domain/ID/$user_id-front.jpg";
		
	} else {
		$topimg = "images/_$domain/members/$user_id.$photoext";
	
	}
	
	if (!file_exists($topimg)) {
		$topimg = 'images/silhouette-new.png';
	}
	
	if ($totalAmountPerWeek >= $_SESSION['highRollerWeekly']) {
		$highroller = "<br /><div class='highrollerholder'><img src='images/trophy.png' style='margin-bottom: -2px;'/> High roller</div>";
	}
	if ($usageType == 1) {
		$medicalicon = "<tr>
     <td style='padding-bottom: 7px;'><img src='images/medical-new.png' style='margin-bottom: -1px;' /></td><td>{$lang['medical-user']}</td>
    </tr>";
	} else {
		$medicalicon = "";
	}
	
	// Consumption this calendar month
	$selectSales = "SELECT SUM(quantity), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
	try
	{
		$result = $pdo3->prepare("$selectSales");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
		$amountMonth = $row['SUM(amount)'];
		$quantityMonth = $row['SUM(quantity)'];
		
		if ($quantityMonth == '') {
			$quantityMonth = 0;
		}
		
	// Determine consumption status vs limit
	$consumptionDelta = $quantityMonth - $mconsumption;
	$consumptionDeltaPlus = 0 - $consumptionDelta;
	
	// Consumption today
	$selectSales = "SELECT SUM(quantity) FROM sales WHERE userid = $user_id AND DATE(saletime) = DATE(NOW())";
	try
	{
		$result = $pdo3->prepare("$selectSales");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
		$quantityToday = $row['SUM(quantity)'];
		
	// Consumption yesterday
	$selectSales = "SELECT SUM(quantity) FROM sales WHERE userid = $user_id AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY)";
	try
	{
		$result = $pdo3->prepare("$selectSales");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
		$quantityYday = $row['SUM(quantity)'];
		
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		$bday = "<tr>
     <td style='padding-bottom: 7px;'><img src='images/birthday.png' style='margin-bottom: -4px;' /></td><td>{$lang['global-birthday']}</td>
    </tr>";
	}
	
	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1 && $exento == 0) {  // show Member w/ expiry
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if (strtotime($memberExp) == strtotime($timeNow)) {
			$expiry = "<tr>
     <td style='padding-bottom: 7px;'><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-15.png' style='margin-bottom: -2px;' /></a></td><td><a href='pay-membership.php?user_id=$user_id'>{$lang['member-expirestoday']}</a></td>
    </tr>";
		  	if ($paymentWarning == '1') {
		  	$expiry .=  "<tr>
     <td style='padding-bottom: 7px;'><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-15.png' style='margin-bottom: -2px;' /><img src='images/exclamation-15.png' style='margin-bottom: -2px; margin-left: -6px;' /></a></td><td><a href='pay-membership.php?user_id=$user_id'>{$lang['member-receivedwarning']}</a></td>
    </tr>";
		  	}
	  	} else if (strtotime($memberExp) < strtotime($timeNow)) {
		  	$expiry =  "<tr>
     <td style='padding-bottom: 7px;'><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-15.png' style='margin-bottom: -2px;' /></a></td><td><a href='pay-membership.php?user_id=$user_id'>{$lang['member-expiredon']}: $memberExpReadable.</a></td>
    </tr>";
		  	
		  	if ($paymentWarning == '1') {
		  	$expiry .=  "<tr>
     <td style='padding-bottom: 7px;'><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-15.png' style='margin-bottom: -2px;' /><img src='images/exclamation-15.png' style='margin-bottom: -2px; margin-left: -6px;' /></a></td><td><a href='pay-membership.php?user_id=$user_id'>{$lang['member-receivedwarning']}</a></td>
    </tr>";
		  	}
		  	
		}
	}
	
	if ($quantityMonth >= $mconsumption) {
		$consumptionwarning = "<tr><td style='padding-bottom: 7px;'><img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -4px;' /></td><td>{$lang['member-conslimitexc']} (+$consumptionDelta g)</td></tr>";
	} else if ($consumptionDeltaPlus < ($mconsumption * 0.1)) {
		$consumptionwarning = "<tr><td style='padding-bottom: 7px;'><img src='images/exclamation-15.png' class='warningIcon' /></td><td>{$lang['member-conslimitnear']} ($consumptionDeltaPlus g {$lang['global-remaining']})</td></tr>";
	}
	
	echo "
	<a href='profile.php?user_id=$user_id'>
	 <span class='firsttext'>#$memberno</span><br />
     <a href='#' id='chipClick'><img src='images/rfid-new.png' onclick='event.preventDefault();' /></a> <span class='nametext2'>$first_name $last_name</span>
    </a><br />
     <table style='margin-top: 10px; vertical-align: top;'>
      <tr>
       <td>
        <div style='display: inline-block; line-height: 11px;'>
         <img src='$topimg' width='116' />
         $highroller
        </div>
       </td>
       <td style='vertical-align: top;'>
        <table style='margin-left: 16px;'>
         <tr>
          <td style='padding-bottom: 7px;'><img src='images/new-flag.png' style='margin-bottom: -4px; margin-right: 10px;' /></td>
          <td>$nationality</td>
         </tr>
         $medicalicon
         <tr>
          <td style='padding-bottom: 7px;'><img src='images/stats.png' style='margin-bottom: -2px; margin-right: 10px;' /></td>
          <td style='padding-bottom: 7px;'>{$expr(number_format($quantityMonth,1))} / {$expr(number_format($mconsumption,0))} g.<br /><span class='yellow'>{$lang['dispensary-today']}: {$expr(number_format($quantityToday,1))} g<br />{$lang['dispensary-yesterday']}: {$expr(number_format($quantityYday,1))} g </span></td>
         </tr>
         $bday
         $expiry
         $consumptionwarning
        </table>
       </td>
      </tr>
     </table>";
	?>
 	   <input type='hidden' id='eurcalcTOTexp' name='euroTOT' />
 	   <input type='hidden' name='dispense' value='done' /> 	   
 	   <input type='hidden' name='paidUntil' value='<?php echo $paidUntil; ?>' /> 	   
  	   <input type='hidden' name='user_id' value='<?php echo $user_id; ?>' />
  	   <input type='hidden' name='realCredit' id='realCredit' value='<?php echo $realCredit; ?>' />
  	   <input type='hidden' name='realNewCredit' id='realNewCredit' value='<?php echo $realNewCredit; ?>' />
  	   <input type='hidden' name='totDiscountInput' id='totDiscountInput' value='' />
<br />  	    
  	   <table id='memberBoxTable'>
  	    <tr>
  	     <td class='dispensetd'><input type='number' lang='nb' class='blankinput first notZero' id='grcalcTOT' name='gramsTOT' value="" readonly /> g<input type='number' lang='nb' class='specialInput first notZero' id='realgrcalcTOT' name='realgramsTOT' value="0" readonly style='display: none;' /></td>
  	     <td class='dispensetd'><input type='number' lang='nb' class='blankinput first notZero' id='unitcalcTOT' name='unitsTOT' value="0" readonly /> u</td>
  	     <td class='dispensetd'><input type='number' lang='nb' class='blankinput' id='eurcalcTOT' name='eurcalcTOT' value="0" readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
	    </tr>  	   
  	    <tr>
  	     <td class='dispensetd' colspan='3'>
  	      <table style='width: 100%;'>
  	       <tr>
  	        <td class='saldoheader'><?php echo $lang['global-credit']; ?>:</td>
  	        <td>
  	        <?php if ($credit < 0) { ?>
  	        <input type='number' lang='nb' class="specialInput" style='color: red !important;' id='credit' name='credit' value='0' readonly /> <?php echo $_SESSION['currencyoperator'] ?>
  	        <?php } else { ?>
  	        <input type='number' lang='nb' class="specialInput" id='credit' name='credit' value='<?php echo $credit; ?>' readonly /> <?php echo $_SESSION['currencyoperator'] ?>
  	        <?php } ?>
  	        </td>
  	        <td class='saldoheader' style='text-align: right;'><?php echo $lang['new-normal']; ?>:</td>
  	        <td>
  	        <?php if ($credit < 0) { ?>
  	        <input type='number' lang='nb' class='specialInput' style='color: red !important;' id='newcredit' name='newcredit' value='0' readonly /> <?php echo $_SESSION['currencyoperator'] ?>
  	        <?php } else { ?>
  	        <input type='number' lang='nb' class='specialInput' id='newcredit' name='newcredit' value='<?php echo $credit; ?>' readonly /> <?php echo $_SESSION['currencyoperator'] ?>
  	        <?php } ?>
  	        </td>
  	       </tr>
<?php if ($_SESSION['dispDonate'] == 1) { ?>
  	       <tr style='display: none;' class='donateField'>
  	        <td class='saldoheader'><?php echo $lang['donate']; ?>:</td>
  	        <td style='text-align: right; padding-right: 13px;'><input type='text' lang='nb' class='twoDigit defaultinput-no-margin' id='newDonation' name='newDonation' value='0' /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	       </tr>
<?php } ?>
  	      </table>
  	     </td>
	    </tr>  	   
  	    <tr>
  	     <td colspan='3'>
  	      <table style='width: 100%;'>
  	       <tr>
  	        <td style='width: 50%;'><a href="#" id="openDispenseDate" class='expandable'><img src='images/date-new.png' style='margin-bottom: -3px; margin-right: 3px;' /> <?php echo $lang['pur-date']; ?></a></td>
  	        <td style='width: 50%; text-align: right;'><a href="#" id="openComment" class='expandable'><img src='images/comment-new.png' style='margin-bottom: -3px; margin-right: 3px;' /> <?php echo $lang['comments']; ?></a></td>
  	       </tr>
  	       <tr>
  	        <td colspan='2'>
  	         <span id='customDispenseDate' class='expanded' style='display: none;'>
		      <input type="text" lang="nb" name="owndate" id="datepicker" class="fiveDigit defaultinput" placeholder="<?php echo $lang['pur-date']; ?>" />@
		      <input type="number" lang="nb" name="hour" id="hour" class="oneDigit defaultinput" maxlength="2" placeholder="hh" />
		      <input type="number" lang="nb" name="minute" id="minute" class="oneDigit defaultinput" maxlength="2" placeholder="mm" />
   		     </span>
   		     <span class='expanded' id='hiddenComment' style='display: none;'>
              <textarea class="defaultinput" name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>
             </span>
            </td>
  	       </tr>
  	      </table>
  	     </td>
	    </tr>
<?php 

	if ($_SESSION['checkoutDiscount'] == 1 && ($_SESSION['domain'] != 'broccolis' || $_SESSION['userGroup'] == 1)) {
	
?>
  	    <tr>
  	     <td colspan='3'>
  	      <table style='width: 100%;'>
  	       <tr>
  	        <td>
  	         <a href="#" id="openDiscount" class='expandable2'><img src='images/discount.png' style='margin-bottom: -4px; margin-right: 3px;' /> <?php echo $lang['apply-discount']; ?> <span id='totDiscount'>(a%)</span></a> 
  	        </td>
  	       </tr>
  	       <tr>
  	        <td class='expanded2' id='discountholder' style='display: none;'>
  	          	   <center>
        <input type="checkbox" id="fee0" name="inDiscount" value='5' />
        <label for="fee0"><span class='full2'>5%</span></label>
        
        <input type="checkbox" id="fee1" name="inDiscount" value='10' />
        <label for="fee1"><span class='full2'>10%</span></label>
        
        <input type="checkbox" id="fee2" name="inDiscount" value='20' />
        <label for="fee2"><span class='full2'>20%</span></label>
        <input type="checkbox" id="fee3" name="inDiscount" value='30' />
        <label for="fee3"><span class='full2'>30%</span></label>
        <br />
        <span style='color: #656d60;'><?php echo $lang['member-orcaps'] . " " . $lang['member-discount']; ?> <?php echo $_SESSION['currencyoperator'] ?>:&nbsp; <input type='number' name='eurdiscount' id='eurdiscount' class='twoDigit defaultinput-no-margin' style='margin: 5px 0;' /></span>
        
  	   </center>
  	        </td>
  	       </tr>
  	      </table>
  	     </td>
	    </tr>
<?php } else {
		echo "<input type='hidden' name='eurdiscount' id='eurdiscount' class='twoDigit' value='0' />";
	}
	if ($_SESSION['creditOrDirect'] == 0 && $_SESSION['domain'] != 'testsystem') { ?>
<!--  	    <tr>
  	     <td colspan='3'><span style='font-size: 14px; display: inline-block; text-transform: uppercase; font-weight: 600; color: #626d5d;'><?php echo $lang['paid-by']; ?></td>
  	    </tr>-->
   	    <tr>
  	     <td><input type="radio" id="pmt1" name="pmtType" value='1' />
          <label for="pmt1"><span class='full' id="pmt1trigger"><?php echo $lang['cash']; ?></span></label>
         </td>
  	     <td>
<?php if ($_SESSION['bankPayments'] == 1) { ?>
          <input type="radio" id="pmt2" name="pmtType" value='2' />
          <label for="pmt2"><span class='full' id="pmt2trigger"><?php echo $lang['card']; ?></span></label>
<?php } else { ?>
          <input type="radio" id="pmt2" name="pmtType" value='2' style="visibility: hidden;" />
          <label for="pmt2" style="visibility: hidden;" ><span class='full' id="pmt2trigger"><?php echo $lang['card']; ?></span></label>
<?php } ?>
  	     </td>
  	     <td>
          <input type="radio" id="pmt3" name="pmtType" value='3' />
          <label for="pmt3"><span class='full' id="pmt3trigger"><?php echo $lang['global-credit']; ?></span></label><br />
  	     </td>
	    </tr>
<?php } ?>
  	    <tr id='changeTable' style='display: none;'>
  	     <td class='dispensetd' colspan='3'>
  	      <table style='width: 100%;'>
  	       <tr>
  	        <td class='saldoheader'><?php echo $lang['paid']; ?>:</td>
  	        <td>
  	         <input type='number' lang='nb' class='twoDigit defaultinput-no-margin' id='paid' placeholder='<?php echo $_SESSION['currencyoperator'] ?>' />
  	        </td>
  	        <td class='saldoheader' style='text-align: right;'><?php echo $lang['change-money']; ?>:</td>
  	        <td>
   	         <input type='number' lang='nb' class='twoDigit defaultinput-no-margin' id='change' name='change' placeholder='<?php echo $_SESSION['currencyoperator'] ?>' readonly  />
  	        </td>
  	       </tr>
  	      </table>
  	     </td>
	    </tr>
	    
	    <?php
	if ($_SESSION['iPadReaders'] == 0) {
		
		// Chip
		if ($_SESSION['dispsig'] == 0) {
			echo <<<EOD
   	    <tr>
  	     <td colspan='3'><input type="text" class="donateOrNot defaultinput" id="cardscan" name="cardid" maxlength="30" placeholder="{$lang['global-scantoconfirm']}" /></td></tr>
EOD;
		// Topaz
		} else if ($_SESSION['dispsig'] == 1) {
			echo <<<EOD
   	    <tr>
  	     <td colspan='3'><canvas id="cnv" name="cnv" class='defaultinput' onclick="javascript:onSign()"></canvas><br />
<center><input id="button2" name="DoneBtn" class='cta1' style='margin: 0;' type="button" value="Guardar firma" onclick="javascript:onDone()" /></center></td></tr>
EOD;
			
		}
	}
?>
	   </table>
  	   
<br />
<button type="submit" class='oneClick cta6' name='oneClick' id='submitButton'><img src='images/checkmark-new.png' style='margin-bottom: -10px;' />&nbsp;<?php echo $lang['global-save']; ?></button><br /><br /><br />
<a href="#" class='cta7' onclick="event.preventDefault(); document.getElementById('registerForm').reset();"><img src='images/cancel.png' style='margin-bottom: -6px;' />&nbsp;<?php echo $lang['global-delete']; ?></a><br /><br />
<center><a href="barcode-dispense.php?user_id=<?php echo $user_id; ?>"><img src="images/barcode-new.png" /></a></center>
</div> <!-- end memberbox -->
<div class="clearfloat"></div>
<?php
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
		
		if ($categoryid == 1) {
			
			
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "g.name ASC";
	}
		
	$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, g.description, g.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt, p.medDiscount FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY $sorting;";
	
	try
	{
		$result = $pdo3->prepare("$selectFlower");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	// Start FLOWERS menu
	if ($data) {
		
		$i = 0;
		echo "<h3 class='title' onClick='load1()' style='cursor: pointer;'><img src='images/icon-flower.png' height='30' style='margin-right: 10px; margin-bottom: -8px;' />{$lang['global-flowerscaps']} <img src='images/expand.png' id='plus1' width='21' style='margin-left: 5px;' /><img src='images/shrink.png' id='minus1' width='21' style='margin-left: 5px; display: none;' /><span id='spinner1'></span><input type='hidden' name='click1' id='click1' class='clickControl' value='0' /><input type='hidden' name='clickX1' id='clickX1' value='0' /></h3><span id='menu1'></span>";
	
	}
	
		echo <<<EOD
<script>

function load1(){
	
	if ($("#click1").val() == 0 && $("#clickX1").val() == 0) {
		$(".clickControl").val(1);
		$("#clickX1").val(1);
		$("#plus1").hide();
		$("#minus1").show();
		
	// Add 'Loading' text
	setInterval(function() {
	$("#spinner1").append(".");
  }, 1000);
  
  
	var dayJSID = parseInt($("#currenti").val());
	var fullmenu = $fullmenu;	
    $.ajax({
      type:"post",
      url:"getflowers-2.php?i="+dayJSID+"&uid=$user_id&discount=$discount&usageType=$usageType",
      datatype:"text",
      success:function(data)
      {
			$("#spinner1").remove();
	       	$('#menu1').append(data);
	       	if(fullmenu == 1){
	       		$('#menu1').show();
	       		$("#clickX1").val(3);
	       	}
			$(".clickControl").val(0);
			$("#click1").val(1);
      }
    });
    
    
	} else {
		
		if ($("#clickX1").val() == 1) {
			$("#clickX1").val(2);
			$('#menu1').hide();
			$("#plus1").show();
			$("#minus1").hide();
		} else if ($("#clickX1").val() == 2) {
			$('#menu1').show();			
			$("#clickX1").val(3);
			$("#plus1").hide();
			$("#minus1").show();
		} else {
			$('#menu1').hide();			
			$("#clickX1").val(2);
			$("#plus1").show();
			$("#minus1").hide();
		}
	}
    
};
</script>
EOD;
                
		 
 } else if ($categoryid == 2) {
	
				
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "h.name ASC";
	}
	
  	$selectExtract = "SELECT h.extractid, h.name, h.extract, h.description, h.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY $sorting;";
	try
	{
		$result = $pdo3->prepare("$selectExtract");
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
  
		echo "<h3 class='title' onClick='load2()' style='cursor: pointer;'><img src='images/icon-extract.png' style='margin-right: 10px; margin-bottom: -8px;'' height='30'>{$lang['global-extractscaps']} <img src='images/expand.png' id='plus2' width='21' style='margin-left: 5px;' /><img src='images/shrink.png' id='minus2' width='21' style='margin-left: 5px; display: none;' /><span id='spinner2'></span><input type='hidden' name='click2' id='click2' class='clickControl' value='0' /><input type='hidden' name='clickX2' id='clickX2' value='0' /></h3><span id='menu2'></span>";
	
	}
  
		echo <<<EOD
<script>
function load2(){
	
	if ($("#click2").val() == 0 && $("#clickX2").val() == 0) {
		$(".clickControl").val(1);
		$("#clickX2").val(1);
		$("#plus2").hide();
		$("#minus2").show();
		
	// Add 'Loading' text
	setInterval(function() {
	$("#spinner2").append(".");
  }, 1000);
  
  
	var dayJSID = parseInt($("#currenti").val());
	var fullmenu = $fullmenu;	
    $.ajax({
      type:"post",
      url:"getextracts-2.php?i="+dayJSID+"&uid=$user_id&discount=$discount&usageType=$usageType",
      datatype:"text",
      success:function(data)
      {
			$("#spinner2").remove();
	       	$('#menu2').append(data);
	       	if(fullmenu == 1){
	       		$('#menu2').show();
	       		$("#clickX2").val(3);
	       	}
			$(".clickControl").val(0);
			$("#click2").val(1);
      }
    });
    
    
	} else {
		
		if ($("#clickX2").val() == 1) {
			$("#clickX2").val(2);
			$('#menu2').hide();
			$("#plus2").show();
			$("#minus2").hide();
		} else if ($("#clickX2").val() == 2) {
			$('#menu2').show();			
			$("#clickX2").val(3);
			$("#plus2").hide();
			$("#minus2").show();
		} else {
			$('#menu2').hide();			
			$("#clickX2").val(2);
			$("#plus2").show();
			$("#minus2").hide();
		}
	}
    
};
</script>
EOD;
		
		} else {
	
		$mi = 3;
  
		if ($_SESSION['menusortdisp'] == 0) {
			$sorting = "p.salesPrice ASC";
		} else {
			$sorting = "pr.name ASC";
		}
		
		// For each cat, look up products
	  	$selectProduct = "SELECT pr.productid, pr.name, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY $sorting;";
	  	
	  	
		try
		{
			$result = $pdo3->prepare("$selectProduct");
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
		
		echo "<h3 class='title' onClick='load($categoryid,$type)' style='cursor: pointer;'>$name <img src='images/expand.png' id='plus$categoryid' width='21' style='margin-left: 5px;' /><img src='images/shrink.png' id='minus$categoryid' width='21' style='margin-left: 5px; display: none;' /><span id='spinner$categoryid'></span><input type='hidden' name='click$categoryid' id='click$categoryid' class='clickControl' value='0' /><input type='hidden' name='clickX$categoryid' id='clickX$categoryid' value='0' /><input type='hidden' id='sort_type$categoryid' value='$type'></h3><span id='menu$categoryid'></span>";
		
		}
		
		echo <<<EOD
<script>
function load(cat,type){
	
	if ($("#click"+cat).val() == 0 && $("#clickX"+cat).val() == 0) {
		$(".clickControl").val(1);
		$("#clickX"+cat).val(1);
		$("#plus"+cat).hide();
		$("#minus"+cat).show();
		
	// Add 'Loading' text
	setInterval(function() {
	$("#spinner"+cat).append(".");
  }, 1000);
  
  
	var dayJSID = parseInt($("#currenti").val());
	var fullmenu = $fullmenu;	
	
    $.ajax({
      type:"post",
      url:"getothers-2.php?i="+dayJSID+"&cat="+cat+"&type="+type+"&uid=$user_id&discount=$discount&usageType=$usageType",
      datatype:"text",
      success:function(data)
      {
			$("#spinner"+cat).remove();
	       	$('#menu'+cat).append(data);
	       	if(fullmenu == 1){
	       		$('#menu'+cat).show();
	       		$("#clickX"+cat).val(3);
	       	}
			$(".clickControl").val(0);
			$("#click"+cat).val(1);
      }
    });
    
    
	} else {
		
		if ($("#clickX"+cat).val() == 1) {
			$("#clickX"+cat).val(2);
			$('#menu'+cat).hide();
			$("#plus"+cat).show();
			$("#minus"+cat).hide();
		} else if ($("#clickX"+cat).val() == 2) {
			$('#menu'+cat).show();			
			$("#clickX"+cat).val(3);
			$("#plus"+cat).hide();
			$("#minus"+cat).show();
		} else {
			$('#menu'+cat).hide();			
			$("#clickX"+cat).val(2);
			$("#plus"+cat).show();
			$("#minus"+cat).hide();
		}
	}
    
};
</script>
EOD;
 
	}

echo <<<EOD
<script>

$(document).ready(function() {

	var element = $(".title");
	var i = 0;
	var delay = 5000; //3 Seconds
	var fullmenu = $fullmenu;

	if(fullmenu == 1){
		function change() {
	    	if (i == element.length && interval) {
		      	clearInterval(interval);
		    }
	    		element.eq(i++).trigger('click');
	  	}
	}

	//Execute on page load
	if(fullmenu == 1){
		change();
	}

	//On interval  
	var interval = setInterval(change, delay);
});

</script>
EOD;

}
echo "</center>";
?>
	  <div class="clearFloat"></div>
<input type='hidden' id='currenti' value='0' />		
<?php if ($_SESSION['dispsig'] == 1) { ?>
<INPUT TYPE=HIDDEN NAME="bioSigData">
<INPUT TYPE=HIDDEN NAME="sigImgData">
<INPUT TYPE=HIDDEN NAME="sigStringData" />
<INPUT TYPE=HIDDEN NAME="sigImageData" />
<?php } ?>
</form>
</div>
<?php
}
?>
<?php
$dispense_user_id= $_SESSION['dispense_user_id'];
 // get last saved summary
if(isset($_SESSION['dispense_user_id'])){
 $getSummaryDetails = "SELECT gramsTOT, realgramsTOT, unitsTOT, credit, newcredit, eurcalcTOT FROM saveDispense_summary WHERE user_id = $dispense_user_id";
try
{
	$result = $pdo3->prepare("$getSummaryDetails");
	$result->execute();
}
catch (PDOException $e)
{
		$error = 'Error fetching user: ' . $e->getMessage();
		echo $error;
		exit();
}
$rowSummaryCount = $result->rowCount();
$rowSummary = $result->fetch();
if($rowSummaryCount > 0 ){
?>
<script type="text/javascript">
	$(document).ready(function(){
		$("#grcalcTOT").val("<?php echo $rowSummary['gramsTOT'] ?>");
		$("#unitcalcTOT").val("<?php echo $rowSummary['unitsTOT'] ?>");
		$("#eurcalcTOT").val("<?php echo $rowSummary['eurcalcTOT'] ?>");
		$("#credit").val("<?php echo $rowSummary['credit'] ?>");
		$("#newcredit").val("<?php echo $rowSummary['newcredit'] ?>");
	});
</script>
<?php }
}
 ?>
<?php if ($_SESSION['fullmenu'] == 0) {
	
?>
<script>
$("#searchfield").bind('keyup', searchit);
$("#searchfield").bind("keydown", function(e) {
   if (e.keyCode === 13) return false;
 });
function searchit(){
	
	var searchphrase = $("#searchfield").val();
	
    $.ajax({
      type:"post",
      url:"searchdispensary.php?phrase="+searchphrase,
      datatype:"text",
      success:function(data)
      {
	       	$("#searchtable").empty()
	       	$('#searchtable').append(data);
      }
    });
    
}
function zoomTo(category,purchaseid,type) {
	
	var purchaseid = purchaseid;
	var category = category;
	var type = type;
	
	       	$("#searchtable").html('');
	if (category < 3) {
		var funct = 'load';
		window[funct + category]();
		
	} else {
		
		var funct1 = 'load('+category;
		var funct2 = ','+type+')';
		//alert(funct1 + funct2);
		//window[funct1 + funct2];
		//load(3,0);
		load(category,type);
		
		
	}
	
	setInterval(function() {
		
		if ($("#clickX"+category).val() == 1) {
			
			$('html, body').animate({
		    scrollTop: ($('.c'+purchaseid).offset().top-230)
			},500);
			$('.c'+purchaseid).focus();
			$("#clickX"+category).val(3);
			clearTimeout();
		};
  	}, 100);
	
	
}
// When clicking:
// Take purchaseid and category
// If category is open, zoom to the purchaseid, gram field
// If category is closed, open it, zoom to purchaseid, gram field
// If memberbox is maximised, minimise it!! Also, flag the field in green.
</script>
<?php
} else { ?>
<script>
$("#searchfield").bind('keyup', searchit);
$("#searchfield").bind("keydown", function(e) {
   if (e.keyCode === 13) return false;
 });
function searchit(){
	
	var searchphrase = $("#searchfield").val();
	
    $.ajax({
      type:"post",
      url:"searchdispensary.php?phrase="+searchphrase,
      datatype:"text",
      success:function(data)
      {
	       	$("#searchtable").empty()
	       	$('#searchtable').append(data);
      }
    });
    
}
function zoomTo(category,purchaseid,type) {
	
	var purchaseid = purchaseid;
	
	       	$("#searchtable").html('');
		
			$('html, body').animate({
		    scrollTop: ($('.c'+purchaseid).offset().top-230)
			},500);
			$('.c'+purchaseid).focus();
			
}
// When clicking:
// Take purchaseid and category
// If category is open, zoom to the purchaseid, gram field
// If category is closed, open it, zoom to purchaseid, gram field
// If memberbox is maximised, minimise it!! Also, flag the field in green.
</script>
<?php
}
?>
<!-- get the dispense flag var in js -->  
<script type="text/javascript">
		  function CartgetItems()
		{
				var sum = 0;
				$( '.calc' ).each( function( i , e ) {
				    var v = +$( e ).val();
				    if ( !isNaN( v ) )
				        sum += v;
				} );
				  var rsum = sum.toFixed(2);
				  $('#grcalcTOT').val(rsum);
				  $('#grcalcTOT2').val(rsum);
				  $('#grcalcTOTexp').val(rsum);
				var sumB = 0;
				$( '.calc2' ).each( function( i , e ) {
				    var vB = +$( e ).val() ;
				    if ( !isNaN( vB ) )
				        sumB += vB;
				} );
				  var rsumB = sumB.toFixed(2);
				  
					    var sumDisc = 0;
					
					    $("input[type=checkbox]:checked").each(function(){
					      sumDisc += parseInt($(this).val());
					    });
						$('#totDiscount').html("(" + sumDisc + "%)");
						$('#totDiscountInput').val(sumDisc);
						
						var appliedDisc = (100 - sumDisc) / 100;
						
						var tempPrice = rsumB * appliedDisc;
						
						var eurdisc = $('#eurdiscount').val();
						
						var newPrice = tempPrice - eurdisc;		
				  $('#eurcalcTOT').val(newPrice);
				  $('#eurcalcTOT2').val(newPrice);
				  $('#eurcalcTOTexp').val(newPrice);
				  
				  
				var sumC = 0;
				$( '.calc3' ).each( function( i , e ) {
				    var vC = +$( e ).val();
				    if ( !isNaN( vC ) )
				        sumC += vC;
				} );
				$( '.calc4' ).each( function( i , e ) {
				    var vD = +$( e ).val();
				    if ( !isNaN( vD ) )
				        sumC += vD;
				} );
				  var sumC = sumC.toFixed(2);
				  $('#unitcalcTOT').val(sumC);
				  $('#unitcalcTOT2').val(sumC);
				  
				  
				var sumE = 0;
				$( '.calc5' ).each( function( i , e ) {
				    var vE = +$( e ).val();
				    if ( !isNaN( vE ) )
				        sumE += vE;
				} );
				var sumF = 0;
				$( '.calc6' ).each( function( i , e ) {
				    var vF = +$( e ).val();
				    if ( !isNaN( vF ) )
				        sumF += vF;
				} );
					  sumF += sumE;
				  $('#realgrcalcTOT').val(sumF);
		}
		function CartcomputeTot() {
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
          var c = $('#newDonation').val();
	      var total = ((a*1) - (b*1) + (c*1));
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
          $('#newcredit2').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
        }
	function inArray(needle, haystack) {
	    var length = haystack.length;
	    for(var i = 0; i < length; i++) {
	        if(haystack[i] == needle) return true;
	    }
	    return false;
	}
	function uniqueArr(array){
		 var outputArray = []; 
          
        // Count variable is used to add the 
        // new unique value only once in the 
        // outputArray. 
        var count = 0; 
          
        // Start variable is used to set true 
        // if a repeated duplicate value is  
        // encontered in the output array. 
        var start = false; 
          
        for (j = 0; j < array.length; j++) { 
            for (k = 0; k < outputArray.length; k++) { 
                if ( array[j] == outputArray[k] ) { 
                    start = true; 
                } 
            } 
            count++; 
            if (count == 1 && start == false) { 
                outputArray.push(array[j]); 
            } 
            start = false; 
            count = 0; 
        } 
       return outputArray;
	}

	var dispenseFlag = "<?php echo $_SESSION['new-dispense-flag'] ?>";
	var currencyoperator = "<?php echo $_SESSION['currencyoperator'] ?>";
	var realWeight = "<?php echo $_SESSION['realWeight'] ?>";
	var cartProduct = [];
	if(dispenseFlag == 1){
		var myCart = JSON.parse(sessionStorage.getItem("shoppingCart"));
		for(var i in myCart){
			cartProduct.push(myCart[i].cat_id);
		}

		var cart_cat =uniqueArr(cartProduct);
		// open flowers cat
		
		
			if(inArray('1',cart_cat)){
				load1();
			}
	
		// open extracts cat
		
			if(inArray('2',cart_cat)){
				 setTimeout(load2, 2000);
			}
	
		// open other categories
	 	
				for( var j in cart_cat){
					if(inArray(cart_cat[j],cart_cat) && cart_cat[j] != '1' && cart_cat[j] != '2'){
						var sort_type = $("#sort_type"+cart_cat[j]).val();
						 (function(j){
						 	setTimeout(function(){
					  			load(cart_cat[j], sort_type);
					  		 }, 1500 * (parseInt(j)+1) );
						 	 })(j);
						}
				}


	}
				
    				setTimeout(function(){
    						CartgetItems();
    						CartcomputeTot();
    					}, 1500);
				
</script>
<!-- Shopping cart JS -->
<script type="text/javascript" src="scripts/cart.js?t=<?php echo time();?>"></script>
<a href="#top" onclick="scrollToTop();return false;" id="scrollTrigger" style="display: none;"><img src="images/scroll-top.png" style="position: fixed; bottom: 30px; right: 30px;" /></a>
<script>
function scrollToTop() {
    var position =
        document.body.scrollTop || document.documentElement.scrollTop;
    if (position) {
        window.scrollBy(0, -Math.max(1, Math.floor(position / 10)));
        scrollAnimation = setTimeout("scrollToTop()", 30);
    } else clearTimeout(scrollAnimation);
}

$(window).on('scroll', function() {
    scrollPosition = $(this).scrollTop();
    if (scrollPosition >= 400) {
        // If the function is only supposed to fire once
        $("#scrollTrigger").css("display", "block");
        $(this).off('scroll');

        // Other function stuff here...
    }
});
$(document).ready(function() {
  $(window).keydown(function(event){
    if(event.keyCode == 13) {
      event.preventDefault();
      return false;
    }
  });
});
pdispense
</script>
<?php	displayFooter();
