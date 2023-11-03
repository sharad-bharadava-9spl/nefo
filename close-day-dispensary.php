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
		
	if ($_SESSION['openAndClose'] == 2) {
		
		if ($_SESSION['noCompare'] != 'true') {
			
			// Closing only - WITH comparison
			$openingid = $_SESSION['openingid'];
			$openingtime = $_SESSION['openingtime'];
			$responsible = $_SESSION['user_id'];
			
			// Check to see if it's in progress
			$checkOpening = "SELECT disOpened, disOpenedBy, dayOpenedNo FROM closing WHERE closingid = $openingid";
		try
		{
			$result = $pdo3->prepare("$checkOpening");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$disOpened = $row['disOpened'];
				$disOpenedBy = $row['disOpenedBy'];
				$dayClosedNo = $row['dayOpenedNo'];
				
		} else {
			
			// Check to see if it's in progress
			$checkOpening = "SELECT closingid, disClosed, disClosedBy FROM closing ORDER by closingtime DESC";
			try
			{
				$result = $pdo3->prepare("$checkOpening");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			
			
				
		if (!$data) {
				
				$responsible = $_SESSION['user_id'];
				
				// Not in progress, so let's create the line!
				$query = "INSERT INTO closing (disClosed, disClosedBy, currentClosing) VALUES (1, $responsible, 1)";
				
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
				
			} else {
				
				$row = $data[0];
					$disOpened = $row['disClosed'];
					$disOpenedBy = $row['disClosedBy'];
					$dayClosedNo = $row['closingid'];
					
			}
				
		}

			
		if ($disOpened == '2' && (!isset($_GET['redo']))) {
			
			pageStart($lang['title-closeday'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['close-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
			echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>
		{$lang['dispensary-closed']}
 </div>
</div>

EOD;
			exit();
	
		} else if ($disOpened == '1' && (!isset($_GET['redo']))) {
			
			$closingOperator = getOperator($disOpenedBy);		
				
			pageStart($lang['title-closeday'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['close-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
			echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['dispensary-inprogress-1']} $closingOperator{$lang['dispensary-inprogress-2']}
	 </div>
	</div>
	
EOD;
			exit();
		
		} else if (isset($_GET['redo'])) {
		
			// If someone already closed, but want to re-do, delete from closingdetails
			if ($dayClosedNo > 0) {
				$query = "DELETE from closingdetails WHERE categoryType = 0 AND closingid = '$dayClosedNo'";
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
	
		if ($_SESSION['noCompare'] != 'true') {
			
			// Write to DB Opening table: disClosing is in process
			$updateOpening = sprintf("UPDATE closing SET disOpened = '1', disOpenedBy = '%d' WHERE closingid = '%d';",
				$responsible,
				$openingid
			);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			// Re-generate pageheader wtih current closing time
			$closingtime = date('Y-m-d H:i:s');
			$_SESSION['closingtime'] = $closingtime;
			
			$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
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
		
	$pageHeader = <<<EOD
		<div class="actionbox-np2">
		  <div class='mainboxheader'>{$lang['close-day-details']}</div>
		  <div class="boxcontent">
		 <table class='purchasetable'>
			 <tr>
				   <td class='biggerFont'><strong>{$lang['day-opened']}</strong>&nbsp;
				  		{$openingtimeView}
				   </td>
				</tr>
				<tr>   
				     <td class='biggerFont'><strong>{$lang['day-closed']}</strong>&nbsp;
				   			{$closingtimeView}
				   	</td>
				</tr>
				<tr>   	
				   	 <td class='biggerFont'><strong>{$lang['day-duration']}</strong>&nbsp;
				   		{$shiftDuration}
				   	</td>
			  </tr>
		 </table>
		 </div>
		</div>
EOD;
			$_SESSION['pageHeader'] = $pageHeader;
			
		} else {
			
			// Write to DB Closing table: disClosing is in process
			
			$responsible = $_SESSION['user_id'];

			$updateOpening = sprintf("UPDATE closing SET disClosed = '1', disClosedBy = '%d' WHERE currentClosing = 1;",
				$responsible
			);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		}
		
	} else {
		
			$openingid = $_SESSION['openingid'];
			$openingtime = $_SESSION['openingtime'];
			$responsible = $_SESSION['user_id'];
			
			// Check to see if it's in progress
			$checkOpening = "SELECT disClosed, disClosedBy, dayClosedNo FROM opening WHERE openingid = $openingid";
		try
		{
			$result = $pdo3->prepare("$checkOpening");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$disOpened = $row['disClosed'];
				$disOpenedBy = $row['disClosedBy'];
				$dayClosedNo = $row['dayClosedNo'];
			
		if ($disOpened == '2' && (!isset($_GET['redo']))) {
			
			pageStart($lang['title-closeday'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['close-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
			echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>
		{$lang['dispensary-closed']}
 </div>
</div>

EOD;
			exit();
	
		} else if ($disOpened == '1' && (!isset($_GET['redo']))) {
			
			$closingOperator = getOperator($disOpenedBy);		
				
			pageStart($lang['title-closeday'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['close-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
			echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['dispensary-inprogress-1']} $closingOperator{$lang['dispensary-inprogress-2']}
	 </div>
	</div>
	
EOD;
			exit();
		
		} else if (isset($_GET['redo'])) {
		
			// If someone already closed, but want to re-do, delete from closingdetails
			if ($dayClosedNo > 0) {
				$query = "DELETE from closingdetails WHERE categoryType = 0 AND closingid = '$dayClosedNo'";
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
	
		// Write to DB Opening table: disClosing is in process
		$updateOpening = sprintf("UPDATE opening SET disClosed = '1', disClosedBy = '%d' WHERE openingid = '%d';",
			$responsible,
			$openingid
		);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		// Re-generate pageheader wtih current closing time
		$closingtime = date('Y-m-d H:i:s');
		$_SESSION['closingtime'] = $closingtime;
		
		$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
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

			$pageHeader = <<<EOD
		<div class="actionbox-np2">
		  <div class='mainboxheader'>{$lang['close-day-details']}</div>
		  <div class="boxcontent">
		 <table class='purchasetable'>
			 <tr>
				   <td class='biggerFont'><strong>{$lang['day-opened']}</strong>&nbsp;
				  		{$openingtimeView}
				   </td>
			</tr>	
			<tr>   
				     <td class='biggerFont'><strong>{$lang['day-closed']}</strong>&nbsp;
				   			{$closingtimeView}
				   	</td>
				   	</tr>
				   	<tr>
				   	 <td class='biggerFont'><strong>{$lang['day-duration']}</strong>&nbsp;
				   		{$shiftDuration}
				   	</td>
				   	</tr>
		 </table>
		 </div>
		</div>
EOD;

		$_SESSION['pageHeader'] = $pageHeader;
		
	}

	
			
	$confirmLeave = <<<EOD
    $(document).ready(function() {

    $('#auto1').click(function() {
        if ($(this).is(':checked')) {
            $('.pcat1').prop('checked', true);
        } else {
            $('.pcat1').prop('checked', false);
        }
    });
    $('#auto2').click(function() {
        if ($(this).is(':checked')) {
            $('.pcat2').prop('checked', true);
        } else {
            $('.pcat2').prop('checked', false);
        }
    });
	    
var userSubmitted=false;

$('#registerForm').submit(function() {
userSubmitted = true;
});

$('#skipWeightForm').submit(function() {
userSubmitted = true;
});

window.onbeforeunload = function() {
    if(!userSubmitted)
        return 'Are you sure that you want to leave this page?';
};

EOD;

	$selectCats = "SELECT DISTINCT id FROM categories WHERE type = 1";
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
	
		while ($cat = $resultCats->fetch()) {
		
		
		$id = $cat['id'];
		
		$confirmLeave .= sprintf("
		
	    $('#auto%d').click(function() {
	        if ($(this).is(':checked')) {
	            $('.pcat%d').prop('checked', true);
	        } else {
	            $('.pcat%d').prop('checked', false);
	        }
	    });
		
		",$id, $id, $id);
		
	}
	
	// v When any checkbox clicked, check if .THIS:
	// v If ID begins with p (meaning it's a prod checkbox):
	// substring from char 6 (get the ID)
	// Add that substring to 'required'
	// Set that varibale to prop false
	
//	$('#required1').prop("required", false);
//reqid.prop("required", false);

    
	$confirmLeave .= <<<EOD

	    $('[type=checkbox]').click(function() {
		    
		    var pstart = $(this).attr('id').charAt(0);
		    var pvalue = $(this).attr('id');
		    
		    if (pstart == 'p') {
			    var pnumber = pvalue.substr(5);
			    var reqid = '#required' + pnumber;
			    
		        if ($(this).is(':checked')) {
		            $(reqid).prop("required", false);
		            $(reqid).prop("disabled", true);
		        } else {
		            $(reqid).prop("required", true);
		            $(reqid).prop("disabled", false);
	            }

        	} else {

	        	// means a category header was clicked
	        	// Find all checkboxes
		    	var pvalue = $(this).attr('id');
	       		var pnumber = pvalue.substr(4);
	       		var pclass = '.prequired' + pnumber;
	       		
		        if ($(this).is(':checked')) {
		            $(pclass).prop("required", false);
		            $(pclass).prop("disabled", true);
		        } else {
		            $(pclass).prop("required", true);
		            $(pclass).prop("disabled", false);
	            }
	        	
        	} 
        	
	    });
	
  }); // end ready
EOD;
	
	pageStart($lang['title-closeday'], NULL, $confirmLeave, "pcloseday", "dev-align-center", $lang['closeday-dis-one'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	echo $_SESSION['pageHeader'];
	if ($_SESSION['noCompare'] != 'true') {
		
?>

	<form onsubmit='oneClick.disabled = true; return true;' id="skipWeightForm" action="close-day-dispensary-1.php" method="POST">

<?php

	echo "<br /><button name='oneClick' type='submit' class='buttonCTA cta1' style='width: auto;' formnovalidate='formnovalidate'>{$lang['dont-weigh']}</button>";
	
	// Include closed products:
	$selectFlower = "SELECT g.flowerid, g.breed2, g.name, p.productid, p.purchaseid, p.purchaseQuantity, p.growType, p.closedAt, p.inMenu, p.tupperWeight FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND ((p.closedAt IS NULL) OR (p.closingDate BETWEEN '$openingtime' AND '$closingtime'));";
		try
		{
			$resultFlower = $pdo3->prepare("$selectFlower");
			$resultFlower->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
		$i = 0;
		echo "<span style='display: none;'><h5>{$lang['global-flowerscaps']}</h5>";
		
		while ($flower = $resultFlower->fetch()) {
	
	
	// Look up growtype
	$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = '{$flower['growType']}'";
		try
		{
			$result = $pdo3->prepare("$growDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$growtype = $row['growtype'];

	if ($flower['closedAt'] != NULL) {
	$flower_row = sprintf("
		<div class='actionbox-np2'>
	  <div class='mainboxheader'>%s %s (%d)</div>
	%s<br />
	  <div class='boxcontent'>
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit defaultinput' placeholder='g' step='0.01' value='0' disabled required style='color: red;' /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='1' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][growtype]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][breed2]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='yes' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div></div>",
	  $flower['name'], $flower['breed2'], $flower['purchaseid'], $growtype, $i, $i, $flower['name'], $i, $i, $flower['productid'], $i, $flower['purchaseid'], $i, $growtype, $i, $flower['breed2'], $i, $i, $flower['inMenu'], $i, $flower['tupperWeight']
	  );
	} else {
	$flower_row = sprintf("
		<div class='actionbox-np2'>
	<div class='mainboxheader'>%s %s (%d)</div>
	%s<br />
	<div class='boxcontent'>
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit defaultinput' placeholder='g' step='0.01' required /><br /><br />
	<strong>{$lang['global-shake']}:</strong><br />
    <!--<input type='radio' name='daycloseProduct[%d][shake]' value='0' style='margin-left: 5px; width: 12px;' checked>0%%</input><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='25' style='margin-left: 5px; width: 12px;'>25%%</input><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='50' style='margin-left: 5px; width: 12px;'>50%%</input><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='75' style='margin-left: 5px; width: 12px;'>75%%</input>-->
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='1' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][growtype]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][breed2]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='no' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div></div>",
	  $flower['name'], $flower['breed2'], $flower['purchaseid'], $growtype, $i, $i, $i, $i, $i, $i, $flower['name'], $i, $i, $flower['productid'], $i, $flower['purchaseid'], $i, $growtype, $i, $flower['breed2'], $i, $i, $flower['inMenu'], $i, $flower['tupperWeight']
	  );
	}

	$i++;

	  echo $flower_row;
  }
  
  // AND NOW THE H TABLE:
  	$selectExtract = "SELECT h.extractid, h.name, p.productid, p.purchaseid, p.purchaseQuantity, p.closedAt, p.inMenu, p.tupperWeight FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND ((p.closedAt IS NULL) OR (p.closingDate BETWEEN '$openingtime' AND '$closingtime'));";
		try
		{
			$resultExtract = $pdo3->prepare("$selectExtract");
			$resultExtract->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
			
		echo "<h5>{$lang['global-extractscaps']}</h5><div class='boxcontent'>";

		while ($extract = $resultExtract->fetch()) {
	
	if ($extract['closedAt'] != NULL) {
	$extract_row =	sprintf("
		<div class='actionbox-np2'>
		<div class='mainboxheader'%s (%d)</div>
		<div class='boxcontent'>
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' value='0' disabled style='color: red;' required /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='2' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='yes' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div></div>",
	  $extract['name'], $extract['purchaseid'], $i, $i, $extract['name'], $i, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $i, $extract['inMenu'], $i, $extract['tupperWeight']
	  );
	  
	} else {
	$extract_row =	sprintf("
		<div class='actionbox-np2'>
		<div class='mainboxheader'>%s (%d)</div>
		<div class='boxcontent'>
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit defaultinput' placeholder='g' step='0.01' required /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='2' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='no' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div></div>",
	  $extract['name'], $extract['purchaseid'], $i, $i, $extract['name'], $i, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $i, $extract['inMenu'], $i, $extract['tupperWeight']
	  );
	}
	$i++;

	  echo $extract_row;
  }
  	// AND NOW OTHER CATEGORIES (GRAM ONLY):
	// First look up categories. For each cat, type 1, run header, then look up products.
	$selectCats = "SELECT DISTINCT category FROM purchases WHERE category > 2 AND closedAt IS NULL";
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
	
		while ($cat = $resultCats->fetch()) {
		
		$category = $cat['category'];
		
		$checkCat = "SELECT name, type FROM categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$checkCat");
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
			$name = $row['name'];
		
		if ($type == 1) {
			
			$catArray[] = $category;
			
			echo "<h5>$name</h5>";
			
		  	$selectExtract = "SELECT h.productid, h.name, p.purchaseid, p.purchaseQuantity, p.closedAt, p.inMenu, p.tupperWeight FROM products h, purchases p WHERE p.category = $category AND p.productid = h.productid AND ((p.closedAt IS NULL) OR (p.closingDate BETWEEN '$openingtime' AND '$closingtime'));";
		try
		{
			$resultExtract = $pdo3->prepare("$selectExtract");
			$resultExtract->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($extract = $resultExtract->fetch()) {
	
	if ($extract['closedAt'] != NULL) {
	$extract_row =	sprintf("
		<div class='actionbox-np2'>
		<div class='mainboxheader'>%s (%d)</div>
		<div class='boxcontent'>
	  <input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit defaultinput' placeholder='g' step='0.01' value='0' disabled style='color: red;' required /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='yes' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div></div>",
	  $extract['name'], $extract['purchaseid'], $i, $i, $extract['name'], $i, $category, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $i, $extract['inMenu'], $i, $extract['tupperWeight']
	  );
	  
	} else {
	$extract_row =	sprintf("
		<div class='actionbox-np2'>
		<div class='mainboxheader'>%s (%d)</div>
		<div class='boxcontent'>
	   <input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit defaultinput' placeholder='g' step='0.01' required /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='no' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div></div>",
	  $extract['name'], $extract['purchaseid'], $i, $i, $extract['name'], $i, $category, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $i, $extract['inMenu'], $i, $extract['tupperWeight']
	  );
	}
	$i++;

	  echo $extract_row;
  }
}
}
  
	foreach($catArray as $value) {
		echo '<input type="hidden" name="catArray[]" value="'. $value. '">';
	}
  
?>
  
  
</div>
</span>
	 <input type='hidden' name='noWeighing' value='yes' />
	 
</form>

<?php } // End no weighing
?>









	<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="close-day-dispensary-1.php" method="POST">

<?php
	
	// Include closed products:
	$selectFlower = "SELECT g.flowerid, g.breed2, g.name, p.productid, p.purchaseid, p.purchaseQuantity, p.growType, p.closedAt, p.inMenu, p.tupperWeight FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND ((p.closedAt IS NULL) OR (p.closingDate BETWEEN '$openingtime' AND '$closingtime'));";
		try
		{
			$resultFlower = $pdo3->prepare("$selectFlower");
			$resultFlower->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$i = 0;
		
		echo "<h5>{$lang['global-flowerscaps']}<div class='fakeboxholder' style='float: right;''> <label class='control'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Calculate automatically<input type='checkbox' style='width: 10px;'' id='auto1'> <div class='fakebox'></div><label></label></label></div></h5><div class='productboxwrap'>";
		
		while ($flower = $resultFlower->fetch()) {
	
	
	// Look up growtype
	$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = '{$flower['growType']}'";
		try
		{
			$result = $pdo3->prepare("$growDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$growtype = $row['growtype'];

	if ($flower['closedAt'] != NULL) {
	$flower_row = sprintf("
		<div class='actionbox-np2'>
	<div class='mainboxheader'>%s %s (%d)</div>
	%s<br />
	<div class='boxcontent'>
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit defaultinput' placeholder='g' step='0.01' value='0' disabled required style='color: red;' /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='1' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][growtype]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][breed2]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='yes' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div></div>",
	  $flower['name'], $flower['breed2'], $flower['purchaseid'], $growtype, $i, $i, $flower['name'], $i, $i, $flower['productid'], $i, $flower['purchaseid'], $i, $growtype, $i, $flower['breed2'], $i, $i, $flower['inMenu'], $i, $flower['tupperWeight']
	  );
	} else {
	$flower_row = sprintf("
		<div class='actionbox-np2 fixed-width-box'>
		<div class='boxcontent'>
	<div class='mainboxheader'>%s %s (%d)</div>
	%s<br />
	<div class='dev_box'>
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit defaultinput prequired1' placeholder='g' step='0.01' id='required%d' required /><br />
	  <div class='fakeboxholder'><label class='control'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$lang['calculate-automatically']}<input type='checkbox' name='daycloseProduct[%d][auto]'' id='pauto%d' class='pcat1' value='1'> <div class='fakebox'></div> </label></div>
	<!--<strong>{$lang['global-shake']}:</strong><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='0' style='margin-left: 5px; width: 12px;' checked>0%%</input><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='25' style='margin-left: 5px; width: 12px;'>25%%</input><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='50' style='margin-left: 5px; width: 12px;'>50%%</input><br />
    <input type='radio' name='daycloseProduct[%d][shake]' value='75' style='margin-left: 5px; width: 12px;'>75%%</input>-->
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='1' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][growtype]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][breed2]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='no' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div></div></div>",
	  $flower['name'], $flower['breed2'], $flower['purchaseid'], $growtype, $i, $i, $i, $i, $i, $i, $i, $i, $i, $flower['name'], $i, $i, $flower['productid'], $i, $flower['purchaseid'], $i, $growtype, $i, $flower['breed2'], $i, $i, $flower['inMenu'], $i, $flower['tupperWeight']
	  );
	}

	$i++;

	  echo $flower_row;
  }
  
  // AND NOW THE H TABLE:
  	$selectExtract = "SELECT h.extractid, h.name, p.productid, p.purchaseid, p.purchaseQuantity, p.closedAt, p.inMenu, p.tupperWeight FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND ((p.closedAt IS NULL) OR (p.closingDate BETWEEN '$openingtime' AND '$closingtime'));";
		try
		{
			$resultExtract = $pdo3->prepare("$selectExtract");
			$resultExtract->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
		echo "</div><h5>{$lang['global-extractscaps']}<div class='fakeboxholder' style='float: right;''> <label class='control'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$lang['calculate-automatically']}<input type='checkbox' style='width: 10px;'' id='auto2'> <div class='fakebox'></div><label></label></label></div></h5><div class='productboxwrap'>";

		while ($extract = $resultExtract->fetch()) {
	
	if ($extract['closedAt'] != NULL) {
	$extract_row =	sprintf("
		<div class='actionbox-np2'>
	<div class='mainboxheader'>%s (%d)</div>
	<div class='boxcontent'>
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit defaultinput' placeholder='g' step='0.01' value='0' disabled style='color: red;' required /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='2' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='yes' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div></div>",
	  $extract['name'], $extract['purchaseid'], $i, $i, $extract['name'], $i, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $i, $extract['inMenu'], $i, $extract['tupperWeight']
	  );
	  
	} else {
	$extract_row =	sprintf("
		<div class='actionbox-np2 fixed-width-box'>
		
		<div class='boxcontent'>
		<div class='mainboxheader'> %s (%d)</div>
		<div class='dev_box'>
	  <input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit defaultinput prequired2' placeholder='g' step='0.01' id='required%d' required /><br />
	    <div class='fakeboxholder'><label class='control'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$lang['calculate-automatically']}<input type='checkbox' name='daycloseProduct[%d][auto]'' id='pauto%d' class='pcat2' value='1'> <div class='fakebox'></div> </label></div>
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='2' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='no' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div></div></div>",
	  $extract['name'], $extract['purchaseid'], $i, $i, $i, $i, $i, $extract['name'], $i, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $i, $extract['inMenu'], $i, $extract['tupperWeight']
	  );
	}
	$i++;

	  echo $extract_row;
  }
  
  	// AND NOW OTHER CATEGORIES (GRAM ONLY):
	// First look up categories. For each cat, type 1, run header, then look up products.
	$selectCats = "SELECT DISTINCT category FROM purchases WHERE category > 2 AND closedAt IS NULL";
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
	
		while ($cat = $resultCats->fetch()) {
		
		
		$category = $cat['category'];
		
		$checkCat = "SELECT name, type FROM categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$checkCat");
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
			$name = $row['name'];
		
		if ($type == 1) {
			
			$catArray2[] = $category;
			
			echo "</div><h5>$name<div class='fakeboxholder' style='float: right;''> <label class='control'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$lang['calculate-automatically']}<input type='checkbox' style='width: 10px;'' id='auto$category'> <div class='fakebox'></div><label></label></label></div></h5><div class='productboxwrap'>";

  // AND NOW THE H TABLE:
  	$selectExtract = "SELECT h.productid, h.name, p.purchaseid, p.purchaseQuantity, p.closedAt, p.inMenu, p.tupperWeight FROM products h, purchases p WHERE p.category = $category AND p.productid = h.productid AND ((p.closedAt IS NULL) OR (p.closingDate BETWEEN '$openingtime' AND '$closingtime'));";
		try
		{
			$resultExtract = $pdo3->prepare("$selectExtract");
			$resultExtract->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($extract = $resultExtract->fetch()) {
		
	
	if ($extract['closedAt'] != NULL) {
	$extract_row =	sprintf("
		<div class='actionbox-np2'>
		 <div class='mainboxheader'>%s (%d)</div>
		   <div class='boxcontent'>
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit defaultinput' placeholder='g' step='0.01' value='0' disabled style='color: red;' required /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='yes' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div></div>",
	  $extract['name'], $extract['purchaseid'], $i, $i, $extract['name'], $i, $category, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $i, $extract['inMenu'], $i, $extract['tupperWeight']
	  );
	  
	} else {
	$extract_row =	sprintf("
		<div class='actionbox-np2'>
		<div class='boxcontent'>
		 <div class='mainboxheader'>%s (%d)</div><br>
		 <div class='dev_box'>
	    <input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit defaultinput prequired$category' placeholder='g' step='0.01' id='required%d' required /><br />
	    <div class='fakeboxholder'><label class='control'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$lang['calculate-automatically']}<input type='checkbox' name='daycloseProduct[%d][auto]'' id='pauto%d' class='pcat$category' value='1'> <div class='fakebox'></div> </label></div>
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='no' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][tupperWeight]' value='%f' /></div></div></div>",
	  $extract['name'], $extract['purchaseid'], $i, $i, $i, $i, $i, $extract['name'], $i, $category, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $i, $extract['inMenu'], $i, $extract['tupperWeight']
	  );
	}
	$i++;

	  echo $extract_row;
  }
}
}
  
  
  
  
	foreach($catArray2 as $value) {
		echo '<input type="hidden" name="catArray2[]" value="'. $value. '">';
	}

?>

</div>
	 <input type='hidden' name='closingConfirm' value='yes' />
	 <button name='oneClick' class="cta1" type="submit"><?php echo $lang['global-save']; ?></button>
</form>



<?php displayFooter();
