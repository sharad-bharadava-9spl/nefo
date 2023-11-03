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
		
	$openingid = $_SESSION['openingid'];
	$openingtime = $_SESSION['openingtime'];
	$dayopeningid = $_SESSION['dayopeningid'];
	$dayopeningtime = $_SESSION['dayopeningtime'];
	$responsible = $_SESSION['user_id'];
	
	$closingtime = $_SESSION['closingtime'];
	
	// Check if closing is already in progress
	$checkOpening = "SELECT disClosed, disClosedBy, dayClosedNo FROM opening WHERE openingid = $dayopeningid";

	$result = mysql_query($checkOpening)
		or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$disClosed = $row['disClosed'];
		$disClosedBy = $row['disClosedBy'];
		$dayClosedNo = $row['dayClosedNo'];

	$checkShiftOpening = "SELECT shiftClosedNo FROM shiftopen ORDER BY openingtime DESC LIMIT 1";

	$resultShift = mysql_query($checkShiftOpening)
		or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
		
	$row = mysql_fetch_array($resultShift);
		$shiftClosedNo = $row['shiftClosedNo'];

	if ($disClosed == '2' && (!isset($_GET['redo']))) {
		pageStart($lang['close-shift-and-day'], NULL, $validationScript, "pcloseday", "step2", $lang['closeday-dis-one'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);

		echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>
		{$lang['dispensary-closed']}
 </div>
</div>

EOD;
		exit();

	} else if ($disClosed == '1' && (!isset($_GET['redo']))) {
		
		// Look up user details
		$userDetails = "SELECT memberno, first_name FROM users WHERE user_id = '{$disClosedBy}'";
			
		$result = mysql_query($userDetails)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$memberno = $row['memberno'];
			$first_name = $row['first_name'];
	
			
			pageStart($lang['close-shift-and-day'], NULL, $validationScript, "pcloseday", "step2", $lang['closeday-dis-one'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['dispensary-inprogress-1']}$memberno $first_name!{$lang['dispensary-inprogress-2']}
	 </div>
	</div>
	
EOD;
	exit();

	} else if (isset($_GET['redo'])) {
		
		// Find closing id in opening tables
		// If exist (above 0), delete from closingdetails
		
		if ($dayClosedNo > 0) {
			
			$query = "DELETE from closingdetails WHERE closingid = $dayClosedNo";
				
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
				
		}
				
		if ($shiftClosedNo > 0) {
			
			$query = "DELETE from shiftclosedetails WHERE closingid = $shiftClosedNo";
				
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
				
		}
		
		$closingtime = date('Y-m-d H:i:s');
		$_SESSION['closingtime'] = $closingtime;
		
		$dayOpeningtimeView = date('d-m-Y H:i', strtotime($dayopeningtime . "+$offsetSec seconds"));
		$shiftOpeningtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
		$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));
	
		// Determine shift duration
		$datetime1 = new DateTime($openingtime);
		$datetime2 = new DateTime($closingtime);
		$interval = $datetime1->diff($datetime2);
		
		$noOfMonths = $interval->format('%m');
		$noOfDays = $interval->format('%d');
		$noOfHours = $interval->format('%h');
		$noOfMins = $interval->format('%i');
		
		if ($noOfMonths == 0) {
			
			if ($noOfDays == 0) {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else if ($noOfDays == 1) {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			}
			
		} else if ($noOfMonths == 1) {
			
			if ($noOfDays == 0) {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else if ($noOfDays == 1) {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			}
			
		} else {
			
			if ($noOfDays == 0) {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else if ($noOfDays == 1) {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			}
			
		}
	
		// Determine day duration
		$datetime1 = new DateTime($dayopeningtime);
		$datetime2 = new DateTime($closingtime);
		$interval = $datetime1->diff($datetime2);
		
		$noOfMonths = $interval->format('%m');
		$noOfDays = $interval->format('%d');
		$noOfHours = $interval->format('%h');
		$noOfMins = $interval->format('%i');
		
		if ($noOfMonths == 0) {
			
			if ($noOfDays == 0) {
				if ($noOfHours > 1) {
					$shiftDurationDay = "$noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDurationDay = "$noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else if ($noOfDays == 1) {
				if ($noOfHours > 1) {
					$shiftDurationDay = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDurationDay = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else {
				if ($noOfHours > 1) {
					$shiftDurationDay = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDurationDay = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			}
			
		} else if ($noOfMonths == 1) {
			
			if ($noOfDays == 0) {
				if ($noOfHours > 1) {
					$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else if ($noOfDays == 1) {
				if ($noOfHours > 1) {
					$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else {
				if ($noOfHours > 1) {
					$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			}
			
		} else {
			
			if ($noOfDays == 0) {
				if ($noOfHours > 1) {
					$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else if ($noOfDays == 1) {
				if ($noOfHours > 1) {
					$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else {
				if ($noOfHours > 1) {
					$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			}
			
		}
		
	
		$pageHeader = <<<EOD
<div class="textInset">
 <center><strong>{$lang['close-day-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['day-opened']}:</td>
   <td style='text-align: left;'>{$dayOpeningtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['shift-opened']}:</td>
   <td style='text-align: left;'>{$shiftOpeningtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['day-closed']}:</td>
   <td style='text-align: left;'>{$closingtimeView}</td>
  </tr>
  <tr>
   <td colspan='2'><br /></td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['shift-duration']}:</td>
   <td style='text-align: left;'>{$shiftDuration}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['day-duration']}:</td>
   <td style='text-align: left;'>{$shiftDurationDay}</td>
  </tr>
 </table>
</div>
EOD;

		$_SESSION['pageHeader'] = $pageHeader;
		
	}
	
	// Write to DB Opening table: disClosing is in process
	$updateOpening = sprintf("UPDATE opening SET disClosed = '1', disClosedBy = '%d' WHERE openingid = '%d';",
		mysql_real_escape_string($responsible),
		mysql_real_escape_string($dayopeningid)
	);
	
	mysql_query($updateOpening)
		or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
		
	$updateOpening = sprintf("UPDATE shiftopen SET disClosed = '1', disClosedBy = '%d' WHERE openingid = '%d';",
		mysql_real_escape_string($responsible),
		mysql_real_escape_string($openingid)
	);

	mysql_query($updateOpening)
		or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
		
			
	$confirmLeave = <<<EOD
    $(document).ready(function() {   	    
document.querySelector('button').addEventListener("click", function(){
    window.btn_clicked = true;      //set btn_clicked to true
});

$(window).bind('beforeunload', function(){
    if(!window.btn_clicked){
        return "{$lang['closeday-leavepage']}";
    }
});
  }); // end ready
EOD;
		
	
	pageStart($lang['close-shift-and-day'], NULL, $deleteExpenseScript, "pcloseday", "", $lang['closeday-dis-one'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	echo $_SESSION['pageHeader'];



?>

	<form onsubmit='oneClick.disabled = true; return true;' id="skipWeightForm" action="close-shift-and-day-dispensary-1.php" method="POST">

<?php

	echo "<br /><button name='oneClick' type='submit' class='buttonCTA' formnovalidate='formnovalidate'>{$lang['dont-weigh']}</button>";
	
	// Include closed products:
	$selectFlower = "SELECT g.flowerid, g.breed2, g.name, p.productid, p.purchaseid, p.purchaseQuantity, p.growType, p.closedAt, p.inMenu, p.tupperWeight FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND ((p.closedAt IS NULL) OR (p.closingDate BETWEEN '$dayopeningtime' AND '$closingtime'));";
	
	$resultFlower = mysql_query($selectFlower)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
	$resultFlower2 = mysql_query($selectFlower)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());

		
		$i = 0;
		echo "<span style='display: none;'><h3 class='title'>{$lang['global-flowerscaps']}</h3><div class='productboxwrap'>";
while ($flower = mysql_fetch_array($resultFlower)) {
	
	
	// Look up growtype
	$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = '{$flower['growType']}'";
	
	$result = mysql_query($growDetails)
		or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$growtype = $row['growtype'];

	if ($flower['closedAt'] != NULL) {
	$flower_row = sprintf("
		<div class='productbox'>
	<h3>%s %s</h3>
	%s<br />
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' value='0' disabled required style='color: red;' /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='1' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][growtype]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][breed2]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='yes' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div>",
	  $flower['name'], $flower['breed2'], $growtype, $i, $i, $flower['name'], $i, $i, $flower['productid'], $i, $flower['purchaseid'], $i, $growtype, $i, $flower['breed2'], $i, $i, $flower['inMenu'], $i, $flower['tupperWeight']
	  );
	} else {
	$flower_row = sprintf("
		<div class='productbox'>
	<h3>%s %s</h3>
	%s<br />
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' required /><br /><br />
	<strong>{$lang['global-shake']}:</strong><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='0' style='margin-left: 5px; width: 12px;' checked>0%%</input><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='25' style='margin-left: 5px; width: 12px;'>25%%</input><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='50' style='margin-left: 5px; width: 12px;'>50%%</input><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='75' style='margin-left: 5px; width: 12px;'>75%%</input>
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='1' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][growtype]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][breed2]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='no' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div>",
	  $flower['name'], $flower['breed2'], $growtype, $i, $i, $i, $i, $i, $i, $flower['name'], $i, $i, $flower['productid'], $i, $flower['purchaseid'], $i, $growtype, $i, $flower['breed2'], $i, $i, $flower['inMenu'], $i, $flower['tupperWeight']
	  );
	}

	$i++;

	  echo $flower_row;
  }
  
  // AND NOW THE H TABLE:
  	$selectExtract = "SELECT h.extractid, h.name, p.productid, p.purchaseid, p.purchaseQuantity, p.closedAt, p.inMenu, p.tupperWeight FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND ((p.closedAt IS NULL) OR (p.closingDate BETWEEN '$dayopeningtime' AND '$closingtime'));";
	$resultExtract = mysql_query($selectExtract)
		or handleError($lang['error-prodprices'],"Error loading extract prices from db: " . mysql_error());
		
		echo "</div><h3 class='title'>{$lang['global-extractscaps']}</h3><div class='productboxwrap'>";

while ($extract = mysql_fetch_array($resultExtract)) {
	
	if ($extract['closedAt'] != NULL) {
	$extract_row =	sprintf("
		<div class='productbox'>
	<h3>%s</h3><input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' value='0' disabled style='color: red;' required /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='2' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='yes' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div>",
	  $extract['name'], $i, $i, $extract['name'], $i, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $i, $extract['inMenu'], $i, $extract['tupperWeight']
	  );
	  
	} else {
	$extract_row =	sprintf("
		<div class='productbox'>
	<h3>%s</h3><input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' required /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='2' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='no' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div>",
	  $extract['name'], $i, $i, $extract['name'], $i, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $i, $extract['inMenu'], $i, $extract['tupperWeight']
	  );
	}
	$i++;

	  echo $extract_row;
  }
 

  
  
  ?>
  
  
</div>
</span>
	 <input type='hidden' name='noWeighing' value='yes' />
	 
</form>
































	<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="close-shift-and-day-dispensary-1.php" method="POST">

<?php
	
	// Include closed products:
	$selectFlower = "SELECT g.flowerid, g.breed2, g.name, p.productid, p.purchaseid, p.purchaseQuantity, p.growType, p.closedAt, p.inMenu, p.tupperWeight FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND ((p.closedAt IS NULL) OR (p.closingDate BETWEEN '$dayopeningtime' AND '$closingtime'));";
	
	$resultFlower = mysql_query($selectFlower)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
	$resultFlower2 = mysql_query($selectFlower)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());

		
		$i = 0;
		echo "<h3 class='title'>{$lang['global-flowerscaps']}</h3><div class='productboxwrap'>";
while ($flower = mysql_fetch_array($resultFlower)) {
	
	
	// Look up growtype
	$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = '{$flower['growType']}'";
	
	$result = mysql_query($growDetails)
		or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$growtype = $row['growtype'];

	if ($flower['closedAt'] != NULL) {
	$flower_row = sprintf("
		<div class='productbox'>
	<h3>%s %s</h3>
	%s<br />
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' value='0' disabled required style='color: red;' /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='1' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][growtype]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][breed2]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='yes' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div>",
	  $flower['name'], $flower['breed2'], $growtype, $i, $i, $flower['name'], $i, $i, $flower['productid'], $i, $flower['purchaseid'], $i, $growtype, $i, $flower['breed2'], $i, $i, $flower['inMenu'], $i, $flower['tupperWeight']
	  );
	} else {
	$flower_row = sprintf("
		<div class='productbox'>
	<h3>%s %s</h3>
	%s<br />
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' required /><br /><br />
	<strong>{$lang['global-shake']}:</strong><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='0' style='margin-left: 5px; width: 12px;' checked>0%%</input><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='25' style='margin-left: 5px; width: 12px;'>25%%</input><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='50' style='margin-left: 5px; width: 12px;'>50%%</input><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='75' style='margin-left: 5px; width: 12px;'>75%%</input>
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='1' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][growtype]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][breed2]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='no' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div>",
	  $flower['name'], $flower['breed2'], $growtype, $i, $i, $i, $i, $i, $i, $flower['name'], $i, $i, $flower['productid'], $i, $flower['purchaseid'], $i, $growtype, $i, $flower['breed2'], $i, $i, $flower['inMenu'], $i, $flower['tupperWeight']
	  );
	}

	$i++;

	  echo $flower_row;
  }
  
  // AND NOW THE H TABLE:
  	$selectExtract = "SELECT h.extractid, h.name, p.productid, p.purchaseid, p.purchaseQuantity, p.closedAt, p.inMenu, p.tupperWeight FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND ((p.closedAt IS NULL) OR (p.closingDate BETWEEN '$dayopeningtime' AND '$closingtime'));";
	$resultExtract = mysql_query($selectExtract)
		or handleError($lang['error-prodprices'],"Error loading extract prices from db: " . mysql_error());
		
		echo "</div><h3 class='title'>{$lang['global-extractscaps']}</h3><div class='productboxwrap'>";

while ($extract = mysql_fetch_array($resultExtract)) {
	
	if ($extract['closedAt'] != NULL) {
	$extract_row =	sprintf("
		<div class='productbox'>
	<h3>%s</h3><input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' value='0' disabled style='color: red;' required /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='2' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='yes' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div>",
	  $extract['name'], $i, $i, $extract['name'], $i, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $i, $extract['inMenu'], $i, $extract['tupperWeight']
	  );
	  
	} else {
	$extract_row =	sprintf("
		<div class='productbox'>
	<h3>%s</h3><input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' required /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='2' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='no' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div>",
	  $extract['name'], $i, $i, $extract['name'], $i, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $i, $extract['inMenu'], $i, $extract['tupperWeight']
	  );
	}
	$i++;

	  echo $extract_row;
  }
  
 /* 
  // AND NOW THE OTHER CATEGORIES:
  
	// Query to look up categories
	$selectCats = "SELECT id, name from categories ORDER by id ASC";

	$resultCats = mysql_query($selectCats)
		or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());

	while ($category = mysql_fetch_array($resultCats)) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		
		echo "</div><h3 class='title'>$name</h3><div class='productboxwrap'>";
		
		// For each cat, look up products
		$selectProducts = "SELECT pr.productid, pr.name, p.purchaseid, p.closedAt, p.inMenu FROM products pr, purchases p WHERE p.category = $categoryid AND pr.productid = p.productid AND ((p.closedAt IS NULL) OR (DATE(p.closingDate) = DATE(NOW()))) ORDER BY pr.name ASC;";
	
		$resultProducts = mysql_query($selectProducts)
			or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
	
		while ($product = mysql_fetch_array($resultProducts)) {
	

	if ($product['closedAt'] != NULL) {
	$product_row =	sprintf("
		<div class='productbox'>
	<h3>%s</h3><input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' value='0' disabled style='color: red;' required /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='2' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='yes' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' /></div>",
	  $product['name'], $i, $i, $product['name'], $i, $i, $product['productid'], $i, $product['purchaseid'], $i, $i, $product['inMenu']
	  );
	  
	} else {
	$product_row =	sprintf("
		<div class='productbox'>
	<h3>%s</h3><input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' required /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='2' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='no' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' /></div>",
	  $product['name'], $i, $i, $product['name'], $i, $i, $product['productid'], $i, $product['purchaseid'], $i, $i, $product['inMenu']
	  );
	}
	$i++;

	  echo $product_row;
			  
		}
		

	}*/

  
  
  ?>
</div>
	 <input type='hidden' name='closingConfirm' value='yes' />
	 <button name='oneClick' type="submit"><?php echo $lang['global-save']; ?></button>
</form>



<?php displayFooter(); ?>
