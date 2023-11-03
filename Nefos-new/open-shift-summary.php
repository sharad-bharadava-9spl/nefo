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
	
	if (isset($_GET['oid'])) {
		$closingid = $_GET['cid'];
		$openingid = $_GET['oid'];
	} else {
		echo $lang['opening-not-specified'];
		exit();
	}
	
	// Check first! And ask if it's in process
	$checkClosing = "SELECT shiftOpened, shiftOpenedBy, shiftOpenedNo FROM shiftclose ORDER by closingtime DESC LIMIT 1";
	
	$result = mysql_query($checkClosing)
		or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$dayOpened = $row['shiftOpened'];
		$dayOpenedBy = $row['shiftOpenedBy'];
		$dayOpenedNo = $row['shiftOpenedNo'];
		
		
	if ($dayOpened == '1' && (!isset($_GET['redo']))) {
		
		// Look up user details
		$userDetails = "SELECT memberno, first_name FROM users WHERE user_id = $dayOpenedBy";
			
		$result = mysql_query($userDetails)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$memberno = $row['memberno'];
			$first_name = $row['first_name'];
	
			
			pageStart($lang['start-shift'], NULL, $validationScript, "pcloseday", "step2", $lang['open-shift-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
		echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['summary-inprogress-1']} $memberno $first_name!<br /><a href='?redo&cid=$closingid&oid=$openingid' class='yellow'> {$lang['summary-inprogress-2']}
	 </div>
	</div>
	
EOD;
		exit();
		
	}
	
		// Write to DB Closing table: dayOpening is in process
		$updateOpening = "UPDATE shiftclose SET shiftOpened = 1, shiftOpenedBy = {$_SESSION['user_id']} WHERE closingid = $closingid";
		
		mysql_query($updateOpening)
			or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
		



	$openingLookup = "SELECT openingid, openingtime, tillComment, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, tillBalance, tillDelta, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract FROM shiftopen WHERE openingid = $openingid";
	
	$result = mysql_query($openingLookup)
		or handleError($lang['error-closingload'],"Error loading closing from db: " . mysql_error());

	$row = mysql_fetch_array($result);
		$openingtime = $row['openingtime'];
		$oneCent = $row['oneCent'];
		$twoCent = $row['twoCent'];
		$fiveCent = $row['fiveCent'];
		$tenCent = $row['tenCent'];
		$twentyCent = $row['twentyCent'];
		$fiftyCent = $row['fiftyCent'];
		$oneEuro = $row['oneEuro'];
		$twoEuro = $row['twoEuro'];
		$fiveEuro = $row['fiveEuro'];
		$tenEuro = $row['tenEuro'];
		$twentyEuro = $row['twentyEuro'];
		$fiftyEuro = $row['fiftyEuro'];
		$hundredEuro = $row['hundredEuro'];
		$coinsTot = $row['coinsTot'];
		$notesTot = $row['notesTot'];
		$tillTot = $row['tillBalance'];
		$tillDelta = $row['tillDelta'];
		$stockDelta = $row['stockDelta'];	
		$prodStock = $row['prodStock'];	
		$prodStockFlower = $row['prodStockFlower'];	
		$prodStockExtract = $row['prodStockExtract'];	
		$stockDeltaFlower = $row['stockDeltaFlower'];	
		$stockDeltaExtract = $row['stockDeltaExtract'];	

		$cashintillYday = $tillTot - $tillDelta;
	
		// Determine colour of till balance field
		if ($tillDelta < 0) {
			$deltaColour = 'negative';
		} else if ($tillDelta > 0) {
			$deltaColour = 'positive';
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

	pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "step5", $lang['openshift-conf'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>
		 <strong><?php echo $lang['closeday-coins']; ?></strong><br />
		 <input type="number" lang="nb" name="oneCent" id="oneCent" class="fourDigit" value="<?php echo $oneCent; ?>" readonly />
		 <input type="number" lang="nb" name="twoCent" id="twoCent" class="fourDigit" value="<?php echo $twoCent; ?>" readonly /> 
		 <input type="number" lang="nb" name="fiveCent" id="fiveCent" class="fourDigit" value="<?php echo $fiveCent; ?>" readonly /> 
		 <input type="number" lang="nb" name="tenCent" id="tenCent" class="fourDigit" value="<?php echo $tenCent; ?>" readonly /> 
		 <input type="number" lang="nb" name="twentyCent" id="twentyCent" class="fourDigit" value="<?php echo $twentyCent; ?>" readonly /> 
		 <input type="number" lang="nb" name="fiftyCent" id="fiftyCent" class="fourDigit" value="<?php echo $fiftyCent; ?>" readonly /> 
		 <input type="number" lang="nb" name="oneEuro" id="oneEuro" class="fourDigit" value="<?php echo $oneEuro; ?>" readonly /> 
		 <input type="number" lang="nb" name="twoEuro" id="twoEuro" class="fourDigit" value="<?php echo $twoEuro; ?>" readonly />
		 <strong><input type="number" lang="nb" name="coinsTot" id="coinsTot" class="fourDigit" value="<?php echo $coinsTot; ?>" readonly /></strong><br /><br />
		 <strong><?php echo $lang['closeday-notes']; ?></strong><br />
		 <input type="number" lang="nb" name="fiveEuro" id="fiveEuro" class="fourDigit" value="<?php echo $fiveEuro; ?>" readonly />
		 <input type="number" lang="nb" name="tenEuro" id="tenEuro" class="fourDigit" value="<?php echo $tenEuro; ?>" readonly /> 
		 <input type="number" lang="nb" name="twentyEuro" id="twentyEuro" class="fourDigit" value="<?php echo $twentyEuro; ?>" readonly /> 
		 <input type="number" lang="nb" name="fiftyEuro" id="fiftyEuro" class="fourDigit" value="<?php echo $fiftyEuro; ?>" readonly /> 
		 <input type="number" lang="nb" name="hundredEuro" id="hundredEuro" class="fourDigit" value="<?php echo $hundredEuro; ?>" readonly />
		 <strong><input type="number" lang="nb" name="notesTot" id="notesTot" class="fourDigit" value="<?php echo $notesTot; ?>" readonly /></strong><br /><br />
		 <table>
		  <tr>
		   <td><?php echo $lang['tillbalyesterday']; ?>:</td>
		   <td><strong><input type="number" lang="nb" name="tillYday" id="tillYday" class="fiveDigit" value="<?php echo number_format($cashintillYday,2,'.',''); ?>" readonly /></strong></td>
		  </tr>
		  <tr>
		   <td><?php echo $lang['tillbalnow']; ?>:</td>
		   <td><strong><input type="number" lang="nb" name="tillTot" id="tillTot" class="fiveDigit" value="<?php echo number_format($tillTot,2,'.',''); ?>" readonly /></strong></td>
		  </tr>
		  <tr>
		   <td><?php echo $lang['closeday-tilldelta']; ?>:</td>
		   <td><strong><input type="number" lang="nb" name="tillDelta" id="tillDelta" class="fiveDigit<?php if (isset($deltaColour)) { echo " " . $deltaColour; } ?>" value="<?php echo $tillDelta; ?>" readonly /></strong></td>
		  </tr>
		 </table>
	
	<br /><br />
<?php

		// Look up opening product weights
		$openingLookup = "SELECT d.category, d.productid, d.purchaseid, d.weight, d.weightDelta, d.prodOpenComment FROM shiftopendetails d, shiftopen c WHERE d.openingid = c.openingid AND c.openingid = '$openingid' ORDER BY d.category ASC";

		$resultOpening = mysql_query($openingLookup)
			or handleError($lang['error-closingload'],"Error loading closing from db: " . mysql_error());
		
		$f = 0;
		$e = 0;
		while ($prodOpen = mysql_fetch_array($resultOpening)) {
			$category = $prodOpen['category'];
			$productid = $prodOpen['productid'];
			$purchaseid = $prodOpen['purchaseid'];
			$weight = $prodOpen['weight'];
			$weightDelta = $prodOpen['weightDelta'];
			$prodopencomment = $prodOpen['prodopencomment'];
			
			if ($category == 1) {
				
				if ($f == 0) {
					echo "<h3 class='title'>{$lang['global-flowerscaps']}</h3>";
				}
			
				$selectFlower = "SELECT g.name, g.breed2, p.growType FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.productid = $productid";
				
				$resultFlower = mysql_query($selectFlower)
					or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
					
				$rowF = mysql_fetch_array($resultFlower);
					$name = $rowF['name'];
					$breed2 = $rowF['breed2'];
					$growType = $rowF['growType'];
					
				if ($breed2 != '') {
					$name = $name . " x " . $breed2;
				} else {
					$name = $name;
				}

	
				// Look up growtype
				$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = '$growType'";
				
				$result = mysql_query($growDetails)
					or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
					
				$row = mysql_fetch_array($result);
					$growtype = $row['growtype'];
					
				if ($growtype != '') {
					$name = $name . " ($growtype)";
				} else {
					$name = $name;
				}
					
				$f++;

			} else if ($category == 2) {
				
				if ($e == 0) {
					echo "<h3 class='title'>{$lang['global-extractscaps']}</h3>";
				}
			
				$selectExtract = "SELECT name FROM extract WHERE extractid = $productid";
				
				$resultExtract = mysql_query($selectExtract)
					or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
					
				$rowE = mysql_fetch_array($resultExtract);
					$name = $rowE['name'];
					
				$e++;
				
			}
			
			if ($weightDelta > 0) {
				$deltaColour = ' positive';
			} else if ($weightDelta < 0) {
				$deltaColour = ' negative';
			} else {
				$deltaColour = '';
			}
			
			echo "<div class='productbox'>";
			echo "<h3>$name</h3>";
			
?>
		 
		 <table>
		  <tr>
		   <td><?php echo $lang['closeday-yourweight']; ?>:</td>
		   <td><input type="number" lang="nb" name="weight" id="weight" class="fourDigit" value="<?php echo number_format($weight,2,'.',''); ?>" readonly /></td>
		  </tr>
		  <tr>
		   <td><?php echo $lang['weightdelta']; ?>:</td>
		   <td><input type="number" lang="nb" name="weightDelta" id="weightDelta" class="fourDigit<?php echo $deltaColour; ?>" value="<?php echo number_format($weightDelta,2,'.',''); ?>" readonly /></td>
		  </tr>
		 </table><br />
		 <?php echo $lang['global-comment']; ?>:<br />
		 <em><?php echo $prodopencomment; ?></em>
		 </div>
<?php 		
		// End loop for each product
		}

echo "<br /><a href='uTil/confirm-open-shift.php?oid=$openingid&cid=$closingid&closer={$_SESSION['user_id']}' class='cta'>{$lang['start-shift']}</a><br />";

displayFooter();