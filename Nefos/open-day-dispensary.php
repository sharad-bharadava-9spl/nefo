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
	
	$closingtime = $_SESSION['closingtime'];
	$closingid = $_SESSION['closingid'];
	$responsible = $_SESSION['user_id'];
	
	// Check first! And ask if it's in process
	if ($_SESSION['noCompare'] != 'true') {
		
		$checkClosing = "SELECT closingid, disOpened, disOpenedBy, dayOpenedNo FROM closing ORDER by closingtime DESC LIMIT 1";
		
		$result = mysql_query($checkClosing)
			or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$closingid = $row['closingid'];
			$disOpened = $row['disOpened'];
			$disOpenedBy = $row['disOpenedBy'];
			$dayOpenedNo = $row['dayOpenedNo'];
			
		if ($disOpened == '2' && (!isset($_GET['redo']))) {
			pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step2", $lang['open-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
			echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>
		{$lang['dispensary-opened']}
 </div>
</div>

EOD;
			exit();
	
		} else if ($disOpened == '1' && (!isset($_GET['redo']))) {
			
			// Look up user details
			$userDetails = "SELECT memberno, first_name FROM users WHERE user_id = '{$disOpenedBy}'";
	
			$result = mysql_query($userDetails)
				or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$memberno = $row['memberno'];
				$first_name = $row['first_name'];
		
			pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step2", $lang['open-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['dispensary-open-inprogress-1']}$memberno $first_name{$lang['dispensary-open-inprogress-2']}
	 </div>
	</div>

EOD;
			exit();
	
		} else if (isset($_GET['redo'])) {
		
		
			// If exist (above 0), delete from openingdetails
			if ($dayOpenedNo > 0) {
				$query = "DELETE from openingdetails WHERE openingid = '$dayOpenedNo'";
				
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
			}
			
			if ($_SESSION['firstOpening'] == 'true') {
				
				$checkClosing = "SELECT openingid FROM opening";
				
				$result = mysql_query($checkClosing)
					or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());

				$row = mysql_fetch_array($result);
					$openingid = $row['openingid'];

				$query = "DELETE from openingdetails WHERE openingid = '$openingid'";
				
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
					
			}
			


		
				
			$openingtime = date('Y-m-d H:i:s');
			$_SESSION['openingtime'] = $openingtime;
			
			$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
			$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));
	
		}
		
		// Write to DB Opening table: disOpening is in process
		$updateOpening = sprintf("UPDATE closing SET disOpened = '1', disOpenedBy = '%d' WHERE closingid = '%d';",
			mysql_real_escape_string($responsible),
			mysql_real_escape_string($closingid)
		);
		
		mysql_query($updateOpening)
			or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
		
	} else if ($_SESSION['firstOpening'] == 'true') {
		
		// First ever opening, and no Closing to compare to, so let's write to new variables in Opening table (if row doesn't exist, create it!)
		$checkClosing = "SELECT openingid, firstDisOpen, firstDisOpenBy FROM opening";
		
		$result = mysql_query($checkClosing)
			or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
			
		if (mysql_num_rows($result) == 0) {
			
			// No opening exists, so let's create a new row.
			$insertOpening = "INSERT INTO opening (firstDisOpen, firstDisOpenBy) VALUES (1, {$_SESSION['user_id']})";
			
			mysql_query($insertOpening)
				or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
				
			$openingid = mysql_insert_id();
				
		} else {
			
			// Someone already started the first ever opening:
			$row = mysql_fetch_array($result);
				$openingid = $row['openingid'];
				$disOpened = $row['firstDisOpen'];
				$disOpenedBy = $row['firstDisOpenBy'];
				
			if ($disOpened == '2' && (!isset($_GET['redo']))) {
				pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step2", $lang['open-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
				echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>
		{$lang['dispensary-opened']}
 </div>
</div>

EOD;
				exit();
	
			} else if ($disOpened == '1' && (!isset($_GET['redo']))) {
			
				// Look up user details
				$userDetails = "SELECT memberno, first_name FROM users WHERE user_id = '{$disOpenedBy}'";
		
				$result = mysql_query($userDetails)
					or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
			
				$row = mysql_fetch_array($result);
					$memberno = $row['memberno'];
					$first_name = $row['first_name'];
			
				pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step2", $lang['open-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
				echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['dispensary-open-inprogress-1']}$memberno $first_name{$lang['dispensary-open-inprogress-2']}
	 </div>
	</div>

EOD;
				exit();
	
			} else if (isset($_GET['redo'])) {
			
			
				// If exist (above 0), delete from openingdetails
				$query = "DELETE from openingdetails WHERE openingid = '$openingid'";
				
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
		
				// Write to DB Opening table: disOpening is in process
				$updateOpening = sprintf("UPDATE opening SET firstDisOpen = '1', firstDisOpenBy = '%d';",
					mysql_real_escape_string($responsible)
				);
			
				mysql_query($updateOpening)
					or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
					
				$openingtime = date('Y-m-d H:i:s');
				$_SESSION['openingtime'] = $openingtime;
				
				$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
		
			}
		}
			
		// Write to DB Opening table: disOpening is in process
		$updateOpening = sprintf("UPDATE opening SET firstDisOpen = '1', firstDisOpenBy = '%d';",
			mysql_real_escape_string($responsible)
		);
		
		mysql_query($updateOpening)
			or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());				
					
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

	pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "step1", $lang['openday-dis-one'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

	<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="open-day-dispensary-1.php" method="POST">

<?php
		
	$selectFlower = "SELECT g.flowerid, g.breed2, g.name, p.productid, p.purchaseid, p.purchaseQuantity, p.growType, p.tupperWeight FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL;";
	$resultFlower = mysql_query($selectFlower)
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

		
	$i++;

	$flower_row = sprintf("
		<div class='productbox'>
	<h3>%s %s</h3>
	%s<br />
	<input type='number' lang='nb' name='dayopenProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' required /><br />
  	   <input type='hidden' name='dayopenProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='dayopenProduct[%d][breed2]' value='%s' />
  	   <input type='hidden' name='dayopenProduct[%d][category]' value='1' />
  	   <input type='hidden' name='dayopenProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='dayopenProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='dayopenProduct[%d][growtype]' value='%s' />
  	   <input type='hidden' name='dayopenProduct[%d][breed2]' value='%s' />
  	   <input type='hidden' name='dayopenProduct[%d][tupperWeight]' value='%f' /></div>",
	  $flower['name'], $flower['breed2'], $growtype, $i, $i, $flower['name'], $i, $flower['breed2'], $i, $i, $flower['productid'], $i, $flower['purchaseid'], $i, $growtype, $i, $flower['breed2'], $i, $flower['tupperWeight']
	  );
	  echo $flower_row;
  }
  
  // AND NOW THE H TABLE:
  	$selectExtract = "SELECT h.extractid, h.name, p.productid, p.purchaseid, p.purchaseQuantity, p.tupperWeight FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL;";
	$resultExtract = mysql_query($selectExtract)
		or handleError($lang['error-prodprices'],"Error loading extract prices from db: " . mysql_error());
		echo "</div><h3 class='title'>{$lang['global-extractscaps']}</h3><div class='productboxwrap'>";
while ($extract = mysql_fetch_array($resultExtract)) {
	$i++;

	$extract_row =	sprintf("
		<div class='productbox'>
	<h3>%s</h3>
	   <input type='number' lang='nb' name='dayopenProduct[%d][weight]' class='fourDigit' placeholder='g' step='0.01' required /><br />
  	   <input type='hidden' name='dayopenProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='dayopenProduct[%d][category]' value='2' />
  	   <input type='hidden' name='dayopenProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='dayopenProduct[%d][purchaseid]' value='%d' />  	   
  	   <input type='hidden' name='dayopenProduct[%d][tupperWeight]' value='%f' /></div>",
	  $extract['name'], $i, $i, $extract['name'], $i, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $extract['tupperWeight']
	  );
	  echo $extract_row;
  }
  
  ?>
  
 </div>

	 <input type='hidden' name='closingConfirm' value='yes' /><br />
 <button name='oneClick' type="submit"><?php echo $lang['global-save']; ?></button>
</form>



<?php displayFooter(); ?>
