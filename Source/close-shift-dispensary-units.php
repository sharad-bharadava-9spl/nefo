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
		
	$openingtime = $_SESSION['openingtime'];
	$closingtime = $_SESSION['closingtime'];
	$openingid = $_SESSION['openingid'];
	$responsible = $_SESSION['user_id'];
	$responsibleName = $_SESSION['first_name'];
	
	// Check first! And ask if it's in process
	if ($_SESSION['type'] == 'opening') {
		
		$checkOpening = "SELECT dis2ShiftClosed AS dis2Closed, dis2ShiftClosedBy AS dis2ClosedBy, shiftClosedNo FROM opening WHERE openingid = $openingid";
		
	} else {

		$checkOpening = "SELECT dis2Closed, dis2ClosedBy, shiftClosedNo FROM shiftopen WHERE openingid = $openingid";
		
	}
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
		$dis2Closed = $row['dis2Closed'];
		$dis2ClosedBy = $row['dis2ClosedBy'];
		$dayClosedNo = $row['shiftClosedNo'];		
		
	if ($dis2Closed == '2' && (!isset($_GET['redo']))) {
		pageStart($lang['close-shift'], NULL, $validationScript, "pcloseday", "step2", $lang['close-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);

		echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>
		{$lang['dispensary-closed']}
 </div>
</div>

EOD;
		exit();

	} else if ($dis2Closed == '1' && (!isset($_GET['redo']))) {
		
		// Look up user details
		$userDetails = "SELECT memberno, first_name FROM users WHERE user_id = '{$dis2ClosedBy}'";
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
			$memberno = $row['memberno'];
			$first_name = $row['first_name'];
	
			
			pageStart($lang['close-shift'], NULL, $validationScript, "pcloseday", "step2", $lang['close-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['dispensary-inprogress-1']}$memberno $first_name!{$lang['dispensary-inprogress-2']}
	 </div>
	</div>
	
EOD;
	exit();

	} else if (isset($_GET['redo'])) {
		
		

	// Find cosing id in opening table
	// If exist (above 0), delete from closingdetails
	
	if ($dayClosedNo > 0) {
		$query = "DELETE from shiftclosedetails WHERE closingid = $dayClosedNo AND categoryType = 1";
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
	
	
	// Write to DB Opening table: disClosing is in process
	if ($_SESSION['type'] == 'opening') {
		
		$updateOpening = sprintf("UPDATE opening SET dis2ShiftClosed = '1', dis2ShiftClosedBy = '%d' WHERE openingid = '%d';",
			$responsible,
			$openingid
		);
		
	} else {

		$updateOpening = sprintf("UPDATE shiftopen SET dis2Closed = '1', dis2ClosedBy = '%d' WHERE openingid = '%d';",
			$responsible,
			$openingid
		);
		
	}
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
		
	$closingtime = date('Y-m-d H:i:s');
	$_SESSION['closingtime'] = $closingtime;
	
	$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
	$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));

	// Determine shift duration	
	$datetime1 = new DateTime($openingtime);
	$datetime2 = new DateTime($closingtime);
	$interval = $datetime1->diff($datetime2);
	
	$noOfDays = $interval->format('%d');
	$noOfHours = $interval->format('%h');
	$noOfMins = $interval->format('%i');
	
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
<div class="textInset">
 <center><strong>{$lang['close-day-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['day-opened']}:</td>
   <td style='text-align: left;'>{$openingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['day-closed']}:</td>
   <td style='text-align: left;'>{$closingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['day-duration']}:</td>
   <td style='text-align: left;'>{$shiftDuration}</td>
  </tr>
 </table>
</div>
EOD;

	$_SESSION['pageHeader'] = $pageHeader;
		
	}
	
	// Write to DB Opening table: disClosing is in process
	if ($_SESSION['type'] == 'opening') {
		
		$updateOpening = sprintf("UPDATE opening SET dis2ShiftClosed = '1', dis2ShiftClosedBy = '%d' WHERE openingid = '%d';",
			$responsible,
			$openingid
		);
		
	} else {

		$updateOpening = sprintf("UPDATE shiftopen SET dis2Closed = '1', dis2ClosedBy = '%d' WHERE openingid = '%d';",
			$responsible,
			$openingid
		);
		
	}
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

		
			
	$confirmLeave = <<<EOD
    $(document).ready(function() {

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

	$selectCats = "SELECT DISTINCT id FROM categories WHERE type = 0";
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
		
	
	pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['closeday-dis-one'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	echo $_SESSION['pageHeader'];



?>

	<form onsubmit='oneClick.disabled = true; return true;' id="skipWeightForm" action="close-shift-dispensary-units-1.php" method="POST">

<?php

	echo "<br /><button name='oneClick' type='submit' class='buttonCTA' formnovalidate='formnovalidate'>{$lang['dont-count']}</button>";
	
	echo "<span style='display: none;'>";
	
	// First look up categories. For each cat, type 1, run header, then look up products.
	$selectCats = "SELECT DISTINCT category FROM purchases WHERE category > 2 AND ((closedAt IS NULL) OR (closingDate BETWEEN '$openingtime' AND '$closingtime'))";
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
		
		// Look up category, and then only run the below if type = 1
		$selectCat = "SELECT name, type FROM categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$selectCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$catName = $row['name'];
			$catType = $row['type'];
			
		if ($catType == 0) {
			
			$catNo++;
			
			$catArray[] = $category;
			
			echo "<h3 class='title'>$catName</h3><div class='productboxwrap'>";
	
			$selectFlower = "SELECT g.productid, g.name, p.purchaseid, p.closedAt, p.inMenu FROM products g, purchases p WHERE p.category = $category AND p.productid = g.productid AND ((p.closedAt IS NULL) OR (p.closingDate BETWEEN '$openingtime' AND '$closingtime')) ORDER BY g.name;";
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
	
		while ($flower = $resultFlower->fetch()) {
	
				if ($flower['closedAt'] != NULL) {
					
					$flower_row = sprintf("
		<div class='productbox'>
	<h3>%s (%s)</h3>
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' value='0' disabled required style='color: red;' /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][categoryName]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='yes' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' /></div>",
	  				$flower['name'], $flower['purchaseid'], $i, $i, $flower['name'], $i, $category, $i, $catName, $i, $flower['productid'], $i, $flower['purchaseid'], $i, $i, $flower['inMenu']
	  				);
	  				
				} else {
					
					$flower_row = sprintf("
		<div class='productbox'>
	<h3>%s (%s)</h3>
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' required /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][categoryName]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='no' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' /></div>",
	  				$flower['name'], $flower['purchaseid'], $i, $i, $flower['name'], $i, $category, $i, $catName, $i, $flower['productid'], $i, $flower['purchaseid'], $i, $i, $flower['inMenu']
	  				);
				}

				$i++;
	
		  		echo $flower_row;
	  		
  			}
  			
			echo "</div>";
		
		}
		
	}
  
	foreach($catArray as $value) {
		echo '<input type="hidden" name="catArray[]" value="'. $value. '">';
	}
?>
  
  
</span>
	 <input type='hidden' name='noWeighing' value='yes' />
	 
</form>









	<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="close-shift-dispensary-units-1.php" method="POST">

<?php
	
	// First look up categories. For each cat, type 1, run header, then look up products.
	$selectCats = "SELECT DISTINCT category FROM purchases WHERE category > 2 AND ((closedAt IS NULL) OR (closingDate BETWEEN '$openingtime' AND '$closingtime'))";
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
		
		// Look up category, and then only run the below if type = 1
		$selectCat = "SELECT name, type FROM categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$selectCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$catName = $row['name'];
			$catType = $row['type'];
			
		if ($catType == 0) {
			
			$catNo++;
			
			$catArray2[] = $category;
			
			echo "<h3 class='title'>$catName<br /><input type='checkbox' style='width: 10px;' id='auto$category' /><span style='font-size: 15px;'>{$lang['calculate-automatically']}</span></h3><div class='productboxwrap'>";
	
			$selectFlower = "SELECT g.productid, g.name, p.purchaseid, p.closedAt, p.inMenu FROM products g, purchases p WHERE p.category = $category AND p.productid = g.productid AND ((p.closedAt IS NULL) OR (p.closingDate BETWEEN '$openingtime' AND '$closingtime')) ORDER BY g.name;";
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
	
		while ($flower = $resultFlower->fetch()) {

				if ($flower['closedAt'] != NULL) {
					
					$flower_row = sprintf("
		<div class='productbox'>
	<h3>%s (%s)</h3>
	<input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' value='0' disabled required style='color: red;' /><br />
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][categoryName]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='yes' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' /></div>",
	  				$flower['name'], $flower['purchaseid'], $i, $i, $flower['name'], $i, $category, $i, $catName, $i, $flower['productid'], $i, $flower['purchaseid'], $i, $i, $flower['inMenu']
	  				);
	  				
				} else {
					
					$flower_row = sprintf("
		<div class='productbox'>
	<h3>%s (%s)</h3><input type='number' lang='nb' name='daycloseProduct[%d][weight]' class='fourDigit prequired$category' placeholder='u' step='0.01' id='required%d' required /><br />
	   <input type='checkbox' style='width: 10px;' name='daycloseProduct[%d][auto]' id='pauto%d' class='pcat$category' value='1' />{$lang['calculate-automatically']}
  	   <input type='hidden' name='daycloseProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][category]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][categoryName]' value='%s' />
  	   <input type='hidden' name='daycloseProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='daycloseProduct[%d][closed]' value='no' />
  	   <input type='hidden' name='daycloseProduct[%d][inMenu]' value='%d' /></div>",
	  				$flower['name'], $flower['purchaseid'], $i, $i, $i, $i, $i, $flower['name'], $i, $category, $i, $catName, $i, $flower['productid'], $i, $flower['purchaseid'], $i, $i, $flower['inMenu']
	  				);
				}

				$i++;
	
		  		echo $flower_row;
	  		
  			}
  			
			echo "</div>";
		
		}
		
	}
  
	foreach($catArray2 as $value) {
		echo '<input type="hidden" name="catArray2[]" value="'. $value. '">';
	}
?>
  
  
	 <input type='hidden' name='closingConfirm' value='yes' />
 	 <br /><button name='oneClick' type="submit" class="customButton"><?php echo $lang['global-nextstep']; ?> &raquo;</button>
	 
</form>


<?php displayFooter();