<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	// Does purchase ID exist?
	if (!$_GET['providerid']) {
		echo $lang['error-nopurchselected'];
		exit();
	} else  {
		$providerid = $_GET['providerid'];
	}
	
	// Query to look up provider
	$providerDetails = "SELECT registered, name, comment, providernumber, credit FROM b_providers WHERE id = $providerid";
				
	$result = mysql_query($providerDetails)
		or handleError($lang['error-loadpurchase'],"Error loading purchase: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$registered = $row['registered'];
		$name = $row['name'];
		$comment = $row['comment'];
		$providernumber = $row['providernumber'];
		$credit = $row['credit'];
		
	$selectPurchases = "SELECT 'purchase' AS type, purchaseDate, purchaseid, category, productid, purchaseQuantity, purchasePrice, paid, adminComment AS comment FROM b_purchases WHERE provider = $providerid UNION ALL SELECT 'payment' AS type, paymentTime AS purchaseDate, '' AS purchaseid, '' AS category, '' AS productid, '' AS purchaseQuantity, '' AS purchasePrice, amount AS paid, comment FROM b_providerpayments WHERE providerid = $providerid UNION ALL SELECT 'reload' AS type, movementtime AS purchaseDate, purchaseid, '' AS category, '' AS productid, quantity AS purchaseQuantity, price AS purchasePrice, paid, comment FROM b_productmovements WHERE provider = $providerid ORDER BY purchaseDate DESC";
	
	
	$resultPurchases = mysql_query($selectPurchases)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		

	$selectPurchases2 = "SELECT SUM(paid) FROM b_purchases WHERE provider = $providerid";
	
	$resultPurchases2 = mysql_query($selectPurchases2)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		
	$rowX = mysql_fetch_array($resultPurchases2);
		$purchasePaid = $rowX['SUM(paid)'];
		
		
	$selectTotal = "SELECT purchasePrice, purchaseQuantity FROM b_purchases WHERE provider = $providerid";

	$resultTotal = mysql_query($selectTotal)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		
	while ($onePurchase = mysql_fetch_array($resultTotal)) {
	
		$purchasePrice = $onePurchase['purchasePrice'];
		$purchaseQuantity = $onePurchase['purchaseQuantity'];
		
		$thisPurchase = $purchasePrice * $purchaseQuantity;
		$totalPurchased = $totalPurchased + $thisPurchase;
		
	}
	
	
	$selectTotal2 = "SELECT SUM(amount) FROM b_providerpayments WHERE providerid = $providerid";

	$resultTotal2 = mysql_query($selectTotal2)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		
	$row = mysql_fetch_array($resultTotal2);
		$totalPaid = $row['SUM(amount)'] + $purchasePaid;
		
	$totCredit = $totalPaid - $totalPurchased;
		
	$selectTotal3 = "SELECT SUM(price), SUM(paid) FROM b_productmovements WHERE provider = $providerid";

	$resultTotal3 = mysql_query($selectTotal3)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		
	$row = mysql_fetch_array($resultTotal3);
		$reloadPrice = $row['SUM(price)'];
		$reloadPaid = $row['SUM(paid)'];
		
	$totalPurchased = $totalPurchased + $reloadPrice;
	$totalPaid = $totalPaid + $reloadPaid;
		
	$totCredit = $totalPaid - $totalPurchased;
	
	pageStart($lang['providers'], NULL, $deleteNoteScript, "pprofilenew", NULL, $lang['providers'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	echo "<center><a href='bar-new-purchase.php?providerid=$providerid' class='cta'>{$lang['newpurchase']}</a>";
	echo "<a href='bar-pay-provider.php?providerid=$providerid' class='cta'>Pay</a>";
	
	$memberPhoto = 'images/members/' . $providerid . '.' .  $photoext;
	
	if (!file_exists($memberPhoto)) {
		$memberPhoto = 'images/silhouette.png';
	}

?>
  <a href="bar-edit-provider.php?providerid=<?php echo $providerid;?>" class="cta"><?php echo $lang['global-edit']; ?></a></center>

<div class="overview">
 <span class="profilepicholder"><a href="new-picture.php?user_id=<?php echo $user_id; ?>"><img class="profilepic" src="<?php echo $memberPhoto; ?>" /></a></span>
 <span class="profilefirst"><?php echo $userStar . " " . $memberno . " - " . $first_name . " " . $last_name; ?> (<a href="change-registration-date.php?userid=<?php echo $user_id; ?>" style="color: yellow;"><?php echo $membertime; ?></a>) <a href="member-contract.php?user_id=<?php echo $user_id; ?>" target="_blank"><img src="images/contract.png" style='margin-bottom: -3px; margin-left: 5px;'/></a></span>
 <br />
 <span class="profilesecond">
<?php
	if ($_SESSION['showAge'] == 1 && $_SESSION['showGender'] == 1) {
		echo $gender . ", " . $age . " " . $lang['member-yearsold'];
	} else if ($_SESSION['showAge'] == 1 && $_SESSION['showGender'] == 0) {
		echo $age . " " . $lang['member-yearsold'];
	} else if ($_SESSION['showAge'] == 0 && $_SESSION['showGender'] == 1) {
		echo $gender;
	}
?>
		
 </span>
 <br />
<div id="memberNotifications"> <span class="profilethird">
<?php 

	if ($userCredit < 0) {
		$userCreditDisplay = 0;
		$userClass = 'negative';
	} else {
		$userCreditDisplay = $userCredit;
	}
	
	if ($creditEligible == 1) {
		$creditEligibility = "*";
	} else {
		$creditEligibility = "";
	}

if ($_SESSION['creditOrDirect'] == 1) {
	echo "<a href='donation-management.php?userid=" . $user_id . "'><span class='creditDisplay'>Credit: <span class='creditAmount $userClass'>" . number_format($userCreditDisplay,2) . " &euro;$creditEligibility</span></span></a><br />";
}
	

	// If member is banned
	if ($userGroup == 7) {
		
		// Banned 
		echo "<span class='banDisplay'><span class='banHeader'>*** {$lang['bannedC']} !! ***</span><br /><strong>{$lang['reason']}:</strong><br />" . $banComment . "</span>";
		
	} else {
	
	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1 && $exento == 0) {  // show Member w/ expiry
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if (strtotime($memberExp) == strtotime($timeNow)) {
			echo "<a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 5px;' /> <span class='yellow'>" . $lang['member-expirestoday'] . "</span></a>";
	  	} else if (strtotime($memberExp) > strtotime($timeNow)) {
		  	echo "<a href='pay-membership.php?user_id=$user_id' class='white'>" . $lang['member-memberuntil'] . ": $memberExpReadable</a>";
		} else {
		  	echo "<a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-expiredon'] . ": $memberExpReadable</span></a>";
		  	
		  	if ($paymentWarning == '1') {
		  	echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px;' /> <img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: -15px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-receivedwarning'] . ": $paymentWarningDateReadable</span></a>";
		  	}
		  	
		}
		
	} else if ($userGroup == 9) {
		
		echo $groupName . "&nbsp;($bajaDate)";
		
	} else {
		
		echo $groupName . "&nbsp;";
		
		if ($exento == 1) {
			echo "(" . $lang['exempt'] . ")";
		}
		
		if ($_SESSION['puestosOrNot'] == 1) {
		
			if ($workStation == 1 || $workStation == 6 || $workStation == 11 || $workStation == 16) {
				echo "<img src='images/profile-reception.png' />&nbsp;";
			}
			if ($workStation == 5 || $workStation == 6 || $workStation == 15 || $workStation == 16) {
				echo "<img src='images/profile-bar.png' />&nbsp;";
			}
			if ($workStation == 10 || $workStation == 11 || $workStation == 15 || $workStation == 16) {
				echo "<img src='images/profile-dispensary.png' />&nbsp;";
			}
		}		
	}
	

	if ($usageType == '1') {
		echo "<br /><img src='images/medical-22.png' lass='warningIcon' style='margin-bottom: -3px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>{$lang['medicinal-user']}</span>";
	}
	
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		echo "<br /><img src='images/cake-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>{$lang['global-birthday']}</span>";
	}
	
	$file = 'images/ID/' . $user_id . '-front.' . $dniext1;
	$file2 = 'images/ID/' . $user_id . '-back.' . $dniext2;
	$file3 = 'images/sigs/' . $user_id . '.png';

	if (!file_exists($file)) {
    	echo "<br /><a href='new-id-scan.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 6px;' /><span class='yellow'>" . $lang['member-dninotscanned'] . "</span></a>";
	}
	
	if (!file_exists($file3)) {
    	echo "<br /><a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 6px;' /><span class='yellow'>" . $lang['signature-missing'] . "</span></a>";
	}
	
	// Retrieve system settings, to determine high roller and consumption %
	$selectSettings = "SELECT highRollerWeekly, consumptionPercentage FROM systemsettings";

	$settingsResult = mysql_query($selectSettings)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($settingsResult);
		$highRollerWeekly = $row['highRollerWeekly'];
		$consumptionPercentage = $row['consumptionPercentage'] / 100;

	// Is the user a high roller?
	if ($totalAmountPerWeek >= $highRollerWeekly) {
		echo "<br /><img src='images/hi-roller.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>High roller</span>";
	}
	
	

	
	if ($userNotes != '') {
		$noteCount = mysql_num_rows($noteCheck); //2
		$i = 1;
		
			echo <<<EOD
<br />
<span id='adminComment' onClick="javascript:toggleDiv('userNotes');">
 <img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' />
 <span class='yellow' id='showComment' style='cursor: pointer;'>{$lang['global-admincomment']}</span>
</span>
EOD;

	if ($_GET['deleted'] == 'yes' || isset($_GET['openComment'])) {
		echo "<div id='userNotes'>";
	} else {
		echo "<div id='userNotes' style='display: none;'>";
	}
	
		echo <<<EOD
	 <table class="profileNew">
  	  <tr>
  	   <th class="smallerfont" style='width: 120px;'><strong>{$lang['pur-date']}</strong></th>
  	   <th class="smallerfont" style='width: 120px;'><strong>{$lang['responsible']}</strong></th>
  	   <th class="smallerfont" colspan='2'><strong>{$lang['global-comment']}</strong></th>
	  </tr>
EOD;
		while ($userNote = mysql_fetch_array($userNotes)) {
	
			if ($userNote['notetime'] == NULL) {
				$formattedDate = '';
			} else {
				$formattedDate = date("d-m-y H:i", strtotime($userNote['notetime'] . "+$offsetSec seconds"));
			}
			$noteid = $userNote['noteid'];
			$note = $userNote['note'];
			$responsible = $userNote['worker'];
			$worker = getUser($responsible);
			
		if($i == $noteCount) {
	   		echo <<<EOD
 <tr>
  <td style='border-bottom: 0;'>$formattedDate</td>
  <td style='border-bottom: 0;'>$worker</td>
  <td style='border-bottom: 0;'>$note</td>
  <td style='border-bottom: 0;'><a href="javascript:delete_note($noteid,$user_id)"><img src='images/delete.png' width='15' /></a></td>
 </tr>
EOD;
		} else {
			echo <<<EOD
 <tr>
  <td>$formattedDate</td>
  <td>$worker</td>
  <td>$note</td>
  <td><a href="javascript:delete_note($noteid,$user_id)"><img src='images/delete.png' width='15' /></a></td>
 </tr>
EOD;
	}
			echo <<<EOD
EOD;
	$i++;
		}
echo "</table></div>";
	}

	 if ($amtOwed > 0.1 && $userGroup > 2) {
		 echo "<br /><a href='settle-debt-2.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>" . $lang['member-hasdebt'] . "</span></a>";
	 }

	// Determine consumption status vs limit
	$consumptionDelta = $quantityMonth - $mconsumption;
	$consumptionDeltaPlus = 0 - $consumptionDelta;
	
	
	if ($quantityMonth >= $mconsumption) {
		echo "<br /><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>" . $lang['member-conslimitexc'] . " (+$consumptionDelta g)</span>";
	} else if ($consumptionDeltaPlus < ($mconsumption * $consumptionPercentage)) {
		echo "<br /><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>" . $lang['member-conslimitnear'] . " ($consumptionDeltaPlus g " . $lang['global-remaining'] . ")</span>";
	}
	
	
}



	echo "</span></div><span class='profilefourth'>";
	

?>
<br />
<?php 


?>
<br />
</span>
 </div> <!-- END OVERVIEW -->
 
  <div class="clearfloat"></div><br />
  
  
<div id="profileWrapper">


<div id="leftprofile">

 <div id="detailedinfo">
 <center>
 <h4><?php echo $lang['avalista']; ?>(s)</h4>
 <br />

<?php

		/*
		If 0 aval:
		 - Entrevistado? + Add aval
		 
		If 1 aval:
		 - Show aval + Add aval
		 
		If 2 aval:
		 - Show both avales
		 
		What about 'change aval' button?
		
		Also show 'cadena de avalistas'
		
		*/
		
	if ($friend > 0 && $friend2 > 0) {
		
		// 2 avals
		
		// Aval #1
		$friendDetails1 = "SELECT starCat, memberno, first_name, last_name, photoext FROM users WHERE user_id = $friend";
			
		$friendResult1 = mysql_query($friendDetails1)
			or handleError($lang['error-avalload'],"Error loading user: " . mysql_error());
			
		$row1 = mysql_fetch_array($friendResult1);
			$starCat1 = $row1['starCat'];
			$memberno1 = $row1['memberno'];
			$first_name1 = $row1['first_name'];
			$last_name1 = $row1['last_name'];
			$photoext1 = $row1['photoext'];
		
		if ($starCat1 == 1) {
	   		$userStar1 = "<img src='images/star-yellow.png' width='15' />";
		} else if ($starCat1 == 2) {
	   		$userStar1 = "<img src='images/star-black.png' width='15' />";
		} else if ($starCat1 == 3) {
	   		$userStar1 = "<img src='images/star-green.png' width='15' />";
		} else if ($starCat1 == 4) {
	   		$userStar1 = "<img src='images/star-red.png' width='15' />";
		} else {
	   		$userStar1 = "";
		}
		
		$fileF1 = 'images/members/' . $friend . '.' . $photoext1;
					
 		echo "<div style='width: 50%; float: left;'><a href='aval-details.php?user_id=$friend&chain=true' class='white'>";
		
		if (file_exists($fileF1)) {
			echo "<img src='$fileF1' height='75' /><br />";
		} else {
			echo "<img src='images/silhouette.png' height='75' /><br />";
		}

		echo "$userStar1 $memberno1 - $first_name1 $last_name1</a><br /><a href='add-aval.php?aval=1&user_id=$user_id'>[{$lang['change']}]</a>";
	
		

	
		echo "</div>";
		
		$friendDetails2 = "SELECT starCat, memberno, first_name, last_name, photoext FROM users WHERE user_id = $friend2";
			
		$friendResult2 = mysql_query($friendDetails2)
			or handleError($lang['error-avalload'],"Error loading user: " . mysql_error());
			
		$row2 = mysql_fetch_array($friendResult2);
			$starCat2 = $row2['starCat'];
			$memberno2 = $row2['memberno'];
			$first_name2 = $row2['first_name'];
			$last_name2 = $row2['last_name'];
			$photoext2 = $row2['photoext'];
		
		if ($starCat2 == 1) {
	   		$userStar2 = "<img src='images/star-yellow.png' width='15' />";
		} else if ($starCat2 == 2) {
	   		$userStar2 = "<img src='images/star-black.png' width='15' />";
		} else if ($starCat2 == 3) {
	   		$userStar2 = "<img src='images/star-green.png' width='15' />";
		} else if ($starCat2 == 4) {
	   		$userStar2 = "<img src='images/star-red.png' width='15' />";
		} else {
	   		$userStar2 = "";
		}
		
		$fileF2 = 'images/members/' . $friend2 . '.' . $photoext2;
					
 		echo "<div style='width: 50%; float: left;'><a href='aval-details.php?user_id=$friend2&chain=true' class='white'>";
		
		if (file_exists($fileF2)) {
			echo "<img src='$fileF2' height='75' /><br />";
		} else {
			echo "<img src='images/silhouette.png' height='75' /><br />";
		}

		echo "$userStar2 $memberno2 - $first_name2 $last_name2</a><br /><a href='add-aval.php?aval=1&user_id=$user_id&twoavals'>[{$lang['change']}]</a>";
	
		

	
		echo "</div>";
		
		echo "</center>";
		
	} else if ($friend > 0) {
		
		// 1 aval
		
		// Aval #1
		$friendDetails1 = "SELECT starCat, memberno, first_name, last_name, photoext FROM users WHERE user_id = $friend";
			
		$friendResult1 = mysql_query($friendDetails1)
			or handleError($lang['error-avalload'],"Error loading user: " . mysql_error());
			
		$row1 = mysql_fetch_array($friendResult1);
			$starCat1 = $row1['starCat'];
			$memberno1 = $row1['memberno'];
			$first_name1 = $row1['first_name'];
			$last_name1 = $row1['last_name'];
			$photoext1 = $row1['photoext'];
		
		if ($starCat1 == 1) {
	   		$userStar1 = "<img src='images/star-yellow.png' width='15' />";
		} else if ($starCat1 == 2) {
	   		$userStar1 = "<img src='images/star-black.png' width='15' />";
		} else if ($starCat1 == 3) {
	   		$userStar1 = "<img src='images/star-green.png' width='15' />";
		} else if ($starCat1 == 4) {
	   		$userStar1 = "<img src='images/star-red.png' width='15' />";
		} else {
	   		$userStar1 = "";
		}
		
		$fileF1 = 'images/members/' . $friend . '.' . $photoext1;
					
 		echo "<div style='width: 50%; float: left;'><a href='aval-details.php?user_id=$friend&chain=true' class='white'>";
		
		if (file_exists($fileF1)) {
			echo "<img src='$fileF1' height='75' /><br />";
		} else {
			echo "<img src='images/silhouette.png' height='75' /><br />";
		}

		echo "$userStar1 $memberno1 - $first_name1 $last_name1</a><br /><a href='add-aval.php?aval=1&user_id=$user_id'>[{$lang['change']}]</a>";
	
		

	
		echo "</div>";
		
 		echo "<div style='width: 50%; float: left;'>";
			echo "<img src='images/silhouette.png' height='75' /><br />";

		echo "{$lang['no-avalista']} #2<br /><a href='add-aval.php?aval=2&user_id=$user_id&twoavals'>[{$lang['global-add']}]</a>";
	
		echo "</div>";
		
		
	} else {
		
		// 0 aval
 		echo "<div style='width: 50%; float: left;'>";
			echo "<img src='images/silhouette.png' height='75' /><br />";

		echo "{$lang['no-avalista']} #1<br /><a href='add-aval.php?aval=1&user_id=$user_id'>[{$lang['global-add']}]</a>";
	
		echo "</div>";
 		echo "<div style='width: 50%; float: left;'>";
			echo "<img src='images/silhouette.png' height='75' /><br />";

		echo "{$lang['no-avalista']} #2<br />";
	
		echo "</div>";
		
		if ($interview == 0) {
			$interviewed = "<span class='negative'><strong>{$lang['global-no']}</strong></span>";
		} else {
			$interviewed = "<strong style='color: #005c0b;'>{$lang['global-yes']}</strong>";
		}
		
		echo "<br />&nbsp;<br />{$lang['interviewed-member']}: $interviewed";
		
	}
		echo "</center>";
		echo "</div>";
		
?>
 

 <div id="detailedinfo">
 

  <div id="leftpane">
<h4><?php echo $lang['member-personal']; ?></h4>
<?php echo $nationality; ?><br />
<?php echo $lang['global-birthday'] . ": " . $birthday; ?><br />
<?php
		echo "<span style='display: block; margin-bottom: 3px;'>{$lang['dni-or-passport']}: " . $dni . "</span>";
?>


<?php	
	if (file_exists($file)) {
		echo "<a href='images/ID/" . $user_id . "-front." . $dniext1 . "'><img src='images/dni-iconbig.png' style='margin-top: 10px;'/></a> <a href='new-id-scan-front.php?user_id=$user_id'><img src='images/edit-dnibig.png' style='display: inline-block; margin-left: 5px; margin-bottom: 2px;' /></a> <br />";
	} else {
		echo "<img src='images/dni-icon-nabig.png' style='margin-top: 10px;' /> <a href='new-id-scan-front.php?user_id=$user_id'><img src='images/edit-dnibig.png' style='display: inline-block; margin-left: 8px; margin-bottom: 2px;' /></a> <br />";
	}
			
	if (file_exists($file2)) {
		echo "<a href='images/ID/" . $user_id . "-back." . $dniext2 . "'><img src='images/dni-icon-2big.png' style='margin-top: 10px;' /></a> <a href='new-id-scan-back.php?user_id=$user_id'><img src='images/edit-dnibig.png' style='display: inline-block; margin-left: 8px; margin-bottom: 2px;' /></a> <br />";
	} else {
		echo "<img src='images/dni-icon-na-2big.png' style='margin-top: 10px;' /> <a href='new-id-scan-back.php?user_id=$user_id'><img src='images/edit-dnibig.png' style='display: inline-block; margin-left: 5px; margin-bottom: 2px;' /></a> <br />";
	}
		
	if (file_exists($file3)) {
		echo "<a href='images/sigs/" . $user_id . ".png'><img src='images/sig-iconbig.png' style='margin-top: 10px;' /></a> <a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType'><img src='images/edit-dnibig.png' style='display: inline-block; margin-left: 5px; margin-bottom: 2px;' /></a> <br />";
	} else {
		echo "<img src='images/sig-icon-nabig.png' style='margin-top: 10px;'/> <a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType'><img src='images/edit-dnibig.png' style='display: inline-block; margin-left: 8px; margin-bottom: 2px;' /></a> <br />";
	}
?>


	<br />

<h4><?php echo $lang['member-usage']; ?></h4>
<?php echo $lang['global-type']; ?>: 
<?php
	if ($usageType == 1) {
		echo $lang['member-medicinal'];
	} else {
		echo $lang['member-recreational'];
	}
?><br />
<?php echo $lang['member-monthcons']; ?>: <?php echo $mconsumption; ?> g.<br /><br />
  </div> <!-- END LEFTPANE -->
  <div id="rightpane">

<h4><?php echo $lang['member-contactdetails']; ?></h4>
<?php echo $telephone; ?><br />
<a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a><br /><br />
<?php echo $street . " " . $streetnumber . " " . $flat; ?><br />
      <?php echo $postcode; ?> <?php echo $city; ?><br />
      <?php echo $country; ?><br /><br />
<!--<h4>System specifics</h4>
User ID: <?php echo $user_id; ?><br />
Signup source: <?php echo $signupsource; ?><br />
Card ID: <?php echo $cardid; ?><br />-->

<h4><?php echo $lang['discounts']; ?></h4>
<?php echo $lang['member-discountD']; ?>: <?php echo $discount; ?>%<br />
<?php echo $lang['member-discountBar']; ?>: <?php echo $discountBar; ?>%<br />
  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->

  <div id="userPreferences">
  <div id="leftpane">
<h4><?php echo $lang['member-preferences']; ?></h4>
<?php if ($favouriteCategory != '') { echo $lang['global-category']; ?>: <?php echo " Flor (" . number_format($percentage,0) . "%)"; } ?><br />
<?php if ($favourite1 != '') { ?> #1: <?php echo $favourite1 . " (" . number_format($quantity1,0) . " g)"; ?><br /> <?php } ?>
<?php if ($favourite2 != '') { ?> #2: <?php echo $favourite2 . " (" . number_format($quantity2,0) . " g)"; ?><br /> <?php } ?>
<?php if ($favourite3 != '') { ?>#3: <?php echo $favourite3 . " (" . number_format($quantity3,0) . " g)"; ?><br /> <?php } ?>
<?php if ($favourite4 != '') { ?>#4: <?php echo $favourite4 . " (" . number_format($quantity4,0) . " g)"; ?><br /> <?php } ?>
<?php if ($favourite5 != '') { ?>#5: <?php echo $favourite5 . " (" . number_format($quantity5,0) . " g)"; ?> <?php } ?>
	<br /><br />


  </div> <!-- END LEFTPANE -->
  <div id="rightpane">

<h4><?php echo $lang['member-weeklyavgs']; ?></h4>
<?php echo $lang['global-dispenses']; ?>: <?php echo number_format($totalDispensesPerWeek,0); ?><br />
<?php echo $lang['member-spenditure']; ?>: <?php echo number_format($totalAmountPerWeek,0); ?> &euro;<br /><br />

  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->

  </div> <!-- END LEFTPROFILE -->
 
 
 <div id="statistics">
  <h4><?php echo $lang['member-dispensehistory']; ?></h4>
  <table class="default memberStats">
   <tr>
    <td class="first"><?php echo $lang['dispensary-thisweek']; ?>:</td>
    <td><?php echo number_format($quantityWeek,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsWeek,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeek,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo $lang['dispensary-lastweek']; ?>:</td>
    <td><?php echo number_format($quantityWeekMinusOne,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsWeekMinusOne,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeekMinusOne,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo $lang['dispensary-twoweeksago']; ?>:</td>
    <td><?php echo number_format($quantityWeekMinusTwo,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsWeekMinusTwo,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeekMinusTwo,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date('F'); ?>:</td>
    <td class="<?php echo $monthClass;?>"><?php echo number_format($quantityMonth,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonth,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonth,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("first day of last month")); ?>:</td>
    <td class="<?php echo $monthMinusOneClass;?>"><?php echo number_format($quantityMonthMinus1,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus1,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-1 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusTwoClass;?>"><?php echo number_format($quantityMonthMinus2,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus2,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-2 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusThreeClass;?>"><?php echo number_format($quantityMonthMinus3,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus3,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus3,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   

   <tr>
    <td class="first"><?php echo date("F", strtotime("-3 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusFourClass;?>"><?php echo number_format($quantityMonthMinus4,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus4,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus4,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-4 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusFiveClass;?>"><?php echo number_format($quantityMonthMinus5,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus5,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus5,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-5 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusSixClass;?>"><?php echo number_format($quantityMonthMinus6,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus6,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus6,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-6 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusSevenClass;?>"><?php echo number_format($quantityMonthMinus7,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus7,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus7,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-7 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusEightClass;?>"><?php echo number_format($quantityMonthMinus8,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus8,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus8,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-8 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusNineClass;?>"><?php echo number_format($quantityMonthMinus9,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus9,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus9,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-9 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusTenClass;?>"><?php echo number_format($quantityMonthMinus10,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus10,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus10,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-10 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusElevenClass;?>"><?php echo number_format($quantityMonthMinus11,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus11,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus11,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-11 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusTwelveClass;?>"><?php echo number_format($quantityMonthMinus12,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus12,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus12,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
  </table>

 </div>
 </div> <!-- END PROFILEWRAPPER -->
 <br /><br />
 
 	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th>&euro;</th>
	    <th>Tot. g</th>
	    <th>Tot. u</th>
	    <th>Tot. &euro;</th>
	    <th><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th><?php echo $lang['dispense-newcredit']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
while ($sale = mysql_fetch_array($result)) {
	
		$formattedDate = date("d M H:i", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$type = $sale['Type'];
		$donatedTo = $sale['donatedTo'];
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		$userResult = mysql_query($userLookup)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
			
	    $row = mysql_fetch_array($userResult);
		$first_name = $row['first_name'];
		$memberno = $row['memberno'];
		
		
		
			// Make unpaid rows red and donation rows green:
			// Make unpaid rows red:
			if ($type == 'donation') {
				echo "<tr class='green'>";
			} else {
				echo "<tr>";
			}

			
// Separate methodologies and row displays (linkage) for donations vs sales. Change Credit first:
			if ($type == 'donation' && $donatedTo == 3) {
		echo "
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}' colspan='6'>{$lang['changed-credit']}</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'><strong>{$amount} <span class='smallerfont'>&euro;</span></strong></td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$credit} &euro;</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$newcredit} &euro;</td>

		";
				
// Separate methodologies and row displays (linkage) for donations vs sales. Donations next:
			} else if ($type == 'donation') {
		echo "
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}' colspan='6'>{$lang['donation-donation']}</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'><strong>{$amount} <span class='smallerfont'>&euro;</span></strong></td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$credit} &euro;</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$newcredit} &euro;</td>

		";
				
			} else if ($type == 'memberpayment') {
		echo "
  	   <td class='clickableRow'>{$formattedDate}</td>
  	   <td class='clickableRow' colspan='6'>{$lang['membership-payments']}</td>
		<td class='clickableRow right'><strong>{$amount} <span class='smallerfont'>&euro;</span></strong></td>
		<td class='clickableRow right'>$credit</td>
		<td class='clickableRow right'>$newCredit</td>

		";
				
// Separate methodologies and row displays (linkage) for donations vs sales. Sales next:
			}else if ($type == 'sale') {
				
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		$onesaleResult = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult2 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult3 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult4 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());

	   
		echo "
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult)) {
			if ($onesale['category'] == 1) {
				$category = 'Flower';
			} else if ($onesale['category'] == 2) {
				$category = 'Extract';
			} else {
				
				// Query to look for category
				$categoryDetails = "SELECT name, type FROM categories WHERE id = {$onesale['category']}";
				
				$resultCat = mysql_query($categoryDetails)
					or handleError($lang['error-errorloadingflower'],"Error loading flower: " . mysql_error());
				
				$row = mysql_fetch_array($resultCat);
					$category = $row['name'];
					$catType = $row['type'];
			}
				
			echo $category . "<br />";
		}
		echo "</td><td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult2)) {
			
			$productid = $onesale['productid'];
			
	// Determine product type, and assign query variables accordingly
	if ($onesale['category'] == 1) {
		$purchaseCategory = 'Flower';
		$queryVar = ', breed2';
		$prodSelect = 'flower';
		$prodJoin = 'flowerid';
	} else if ($onesale['category'] == 2) {
		$purchaseCategory = 'Extract';
		$queryVar = '';
		$prodSelect = 'extract';
		$prodJoin = 'extractid';
	} else if ($onesale['category'] > 2) {
		$purchaseCategory = $category;
		$queryVar = '';
		$prodSelect = 'products';
		$prodJoin = "productid";
	}
	
		$selectProduct = "SELECT name{$queryVar} FROM {$prodSelect} WHERE ({$prodJoin} = {$productid})";
		$productResult = mysql_query($selectProduct)
			or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
			
	    $row = mysql_fetch_array($productResult);
		
		if ($row['breed2'] != '') {
			$name = $row['name'] . " x " . $row['breed2'];
		} else {
			$name = $row['name'];
		}


			echo $name . "<br />";
		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult3)) {
			
			if ($onesale['category'] > 2) {
				
				// Query to look for category
				$categoryDetailsC = "SELECT name, type FROM categories WHERE id = {$onesale['category']}";
				
				$resultC = mysql_query($categoryDetailsC)
					or handleError($lang['error-errorloadingflower'],"Error loading flower: " . mysql_error());
				
				$rowC = mysql_fetch_array($resultC);
					$category = $rowC['name'];
					$type = $rowC['type'];
			}

			if ($onesale['category'] < 3 || $type == 1) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
			}		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult4)) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		$units = number_format($units,1);
		echo "
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} <span class='smallerfont'>&euro;</span></strong></td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$credit} &euro;</td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$newcredit} &euro;</td>

		";
		
		
		// And finally, bar
	} else {
		
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM b_salesdetails d, b_sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		$onesaleResult6 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult7 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult8 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult9 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
	   
		echo "
  	   <td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult6)) {
			
			// Look up bar category
			$selectBarCat = "SELECT name FROM b_categories WHERE id = {$onesale['category']}";
		
			$resultBarCat = mysql_query($selectBarCat)
				or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
				
		  	$barRow = mysql_fetch_array($resultBarCat); // how to reset array datapointer? Better than using resultflower and resultflower2
		   		$category = $barRow['name'];
			
			
			echo $category . "<br />";
		}
		echo "</td><td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult7)) {
			
			$productid = $onesale['productid'];
			
		$selectProduct = "SELECT name FROM b_products WHERE productid = $productid";
		$productResult = mysql_query($selectProduct)
			or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
			
	    $row = mysql_fetch_array($productResult);
			$name = $row['name'];


			echo $name . "<br />";
		}
		echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult8)) {
			echo number_format($onesale['quantity'],0) . "<br />";
		}
		echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult9)) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		$units = number_format($units,1);
		echo "
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'></td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$units} u</strong></td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$amount} <span class='smallerfont'>&euro;</span></strong></td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>{$credit} &euro;</td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>{$newcredit} &euro;</td>

		";
		
	}

}
?>

<script>

	$("#adminComment").hover(function () {
	$("#commentText").css("display", "block");
	},function () {
	$("#commentText").css("display", "none");
	});	
	
	$("#minimizeMemberBox").click(function () {
	$("#hiddenSummary").css("display", "block");
	$("#memberbox").css("display", "none");
	});	
	
	$("#minimizeSummaryBox").click(function () {
	$("#memberbox").css("display", "block");
	$("#hiddenSummary").css("display", "none");
	});	
	
function toggleDiv(divId) {
   $("#"+divId).toggle();
}	
</script>

<br /><br /><br />

<div id='productoverview'>
 
 <table style="display: inline-block; vertical-align: top; <?php if ($category == '2') { echo 'margin-top: 9px;'; } ?>">
  <tr>
   <td><?php echo $lang['purchases']; ?>:</td>
   <td class='yellow fat right'><?php echo number_format($totalPurchased,2); ?> &euro;</td>
  </tr>
  <tr>
   <td style='border-bottom: 1px solid white;'><?php echo $lang['payments']; ?>:</td>
   <td class='yellow fat right' style='border-bottom: 1px solid white;'><?php echo number_format($totalPaid,2); ?> &euro;</td>
  </tr>
  <tr>
   <td><?php echo $lang['global-credit']; ?>:</td>
   <td class='yellow fat right'><?php echo number_format($totCredit,2); ?> &euro;</td>
  </tr>
 </table>
</div>

 <div id="providerbox">

  <tr>
   <td><?php echo $lang['provider']; ?>:</td>
   <td class='yellow fat'>#<?php echo $providernumber; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-name']; ?>:</td>
   <td class='yellow fat'><?php echo $name; ?></td>
  </tr>
 </div>

 <br /><br />
	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th>Precio</th>
	    <th>Pagado</th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
<?php
$i = 0;
while ($sale = mysql_fetch_array($resultPurchases)) {
	
	
		$i++;
	
		$formattedDate = date("d M H:i", strtotime($sale['purchaseDate'] . "+$offsetSec seconds"));
		$purchaseid = $sale['purchaseid'];
		$category = $sale['category'];
		$productid = $sale['productid'];
		$purchaseQuantity = $sale['purchaseQuantity'];
		$purchasePrice = $sale['purchasePrice'];
		$paid = $sale['paid'];
		$type = $sale['type'];
		$price = $sale['purchasePrice'];
		$comment = $sale['comment'];
		
			
			
		
		if ($comment != '') {
			
			$commentRead = "
			                <img src='images/comments.png' id='comment$i' /><div id='helpBox$i' class='helpBox'>{$comment}</div>
			                <script>
			                  	$('#comment$i').on({
							 		'mouseover' : function() {
									 	$('#helpBox$i').css('display', 'block');
							  		},
							  		'mouseout' : function() {
									 	$('#helpBox$i').css('display', 'none');
								  	}
							  	});
							</script>
			                ";
			
		} else {
			
			$commentRead = "";
			
		}
	
	
		if ($type == 'purchase') {
				
				$selectProduct = "SELECT name FROM b_products WHERE productid = $productid";
				
				$productResult = mysql_query($selectProduct)
					or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
					
			    $row = mysql_fetch_array($productResult);
					$name = $row['name'];
				
			
			$price = $purchaseQuantity * $purchasePrice;
			
		  	    
			if ($type == 1) {
				
				$provRow = sprintf("
				<tr>
		  	    <td>%s</td>
		  	    <td class='left'>{$lang['global-purchase']}</td>
		  	    <td class='left'>%s</td>
		  	    <td class='right'>%0.02f u.</td>
		  	    <td class='right'>%0.02f &euro;</td>
		  	    <td class='right'>%0.02f &euro;</td>
	   			<td class='relative'>$commentRead</td>
		  	    </tr>",
		  	    $formattedDate, $name, number_format($purchaseQuantity,2), $price, $paid);
		  	    
	  	    } else {
		  	    
				$provRow = sprintf("
				<tr>
		  	    <td>%s</td>
		  	    <td class='left'>{$lang['global-purchase']}</td>
		  	    <td class='left'>%s</td>
		  	    <td class='right'>%0.02f u.</td>
		  	    <td class='right'>%0.02f &euro;</td>
		  	    <td class='right'>%0.02f &euro;</td>
	   			<td class='relative'>$commentRead</td>
		  	    </tr>",
		  	    $formattedDate, $name, number_format($purchaseQuantity,2), $price, $paid);
		  	    
	  	    }

	  	    
	
			
		} else if ($type == 'reload') {
			
			$selectProduct = "SELECT productid, category FROM b_purchases WHERE purchaseid = $purchaseid";
			
			$productResult = mysql_query($selectProduct)
				or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
				
		    $row = mysql_fetch_array($productResult);
				$productid = $row['productid'];
				$category = $row['category'];
			
				
			$selectProduct = "SELECT name FROM b_products WHERE productid = '$productid'";
			
			
			$productResult = mysql_query($selectProduct)
				or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
					
		    $row = mysql_fetch_array($productResult);
					$name = $row['name'];

					

				$provRow = sprintf("
				<tr>
		  	    <td>%s</td>
		  	    <td class='left'>{$lang['reload']}</td>
		  	    <td class='left'>%s</td>
		  	    <td class='right'>%0.02f u.</td>
		  	    <td class='right'>%0.02f &euro;</td>
		  	    <td class='right'>%0.02f &euro;</td>
	   			<td class='relative'>$commentRead</td>
		  	    </tr>",
		  	    $formattedDate, $name, number_format($purchaseQuantity,2), $price, $paid);
		  	    
			
		} else {
			
			$provRow = sprintf("
			<tr class='green'>
	  	    <td>%s</td>
		  	<td class='left'>{$lang['payment']}</td>
	  	    <td class='left'></td>
	  	    <td class='right'></td>
	  	    <td class='right'></td>
	  	    <td class='right'>%0.02f &euro;</td>
	   		<td class='relative'>$commentRead</td>
	  	    </tr>",
	  	    $formattedDate, $paid);
	  	    
			
		}
		
		echo $provRow;	 
		
	}

	echo "</table>";
displayFooter(); ?>