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
	if ($_SESSION['noCompare'] != 'true') {
		
		
		$checkClosing = "SELECT dayOpened, dayOpenedBy, dayOpenedNo FROM closing ORDER by closingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$checkClosing");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$dayOpened = $row['dayOpened'];
			$dayOpenedBy = $row['dayOpenedBy'];
			$dayOpenedNo = $row['dayOpenedNo'];
			
			
		if ($dayOpened == '1' && (!isset($_GET['redo']))) {
			
			// Look up user details
			$userDetails = "SELECT memberno, first_name FROM users WHERE user_id = $dayOpenedBy";
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
		
				
				pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['open-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
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
		$updateOpening = "UPDATE closing SET dayOpened = 1, dayOpenedBy = {$_SESSION['user_id']} WHERE closingid = $closingid";
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
		
	} else {
		
		// First ever opening, and no Closing to compare to, so let's write to new variables in Opening table (if row doesn't exist, create it!)
		$checkClosing = "SELECT firstDayOpen, firstDayOpenBy FROM opening WHERE openingid = $openingid";
		try
		{
			$result = $pdo3->prepare("$checkClosing");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$dayOpened = $row['firstDayOpen'];
			$dayOpenedBy = $row['firstDayOpenBy'];
			
		if ($dayOpened == '1' && (!isset($_GET['redo']))) {
			
			// Look up user details
			$userDetails = "SELECT memberno, first_name FROM users WHERE user_id = $dayOpenedBy";
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
		
				pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['open-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
			echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['summary-inprogress-1']} $memberno $first_name!<br /><a href='?redo&oid=$openingid' class='yellow'> {$lang['summary-inprogress-2']}
	 </div>
	</div>
	
EOD;
			exit();
		
		}
	
		// Write to DB Closing table: dayOpening is in process
		$updateOpening = "UPDATE opening SET firstDayOpen = 1, firstDayOpenBy = {$_SESSION['user_id']} WHERE openingid = $openingid";
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


	$openingLookup = "SELECT openingid, openingtime, tillComment, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, tillBalance, tillDelta, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract FROM opening WHERE openingid = $openingid";
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

	pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "step5 dev-align-center", $lang['openday-conf'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>
<div class="actionbox-np2">
	 <div class="boxcontent">
		 <strong><?php echo $lang['closeday-coins']; ?></strong><br />
		 <div class="mainboxheader"></div>
		 <input type="number" lang="nb" name="oneCent" id="oneCent" class="fourDigit defaultinput" value="<?php echo $oneCent; ?>" readonly />
		 <input type="number" lang="nb" name="twoCent" id="twoCent" class="fourDigit defaultinput" value="<?php echo $twoCent; ?>" readonly /> 
		 <input type="number" lang="nb" name="fiveCent" id="fiveCent" class="fourDigit defaultinput" value="<?php echo $fiveCent; ?>" readonly /> 
		 <input type="number" lang="nb" name="tenCent" id="tenCent" class="fourDigit defaultinput" value="<?php echo $tenCent; ?>" readonly /> 
		 <input type="number" lang="nb" name="twentyCent" id="twentyCent" class="fourDigit defaultinput" value="<?php echo $twentyCent; ?>" readonly /> 
		 <input type="number" lang="nb" name="fiftyCent" id="fiftyCent" class="fourDigit defaultinput" value="<?php echo $fiftyCent; ?>" readonly /> 
		 <input type="number" lang="nb" name="oneEuro" id="oneEuro" class="fourDigit defaultinput" value="<?php echo $oneEuro; ?>" readonly /> 
		 <input type="number" lang="nb" name="twoEuro" id="twoEuro" class="fourDigit defaultinput" value="<?php echo $twoEuro; ?>" readonly />
		 <strong><input type="number" lang="nb" name="coinsTot" id="coinsTot" class="fourDigit defaultinput" value="<?php echo $coinsTot; ?>" readonly /></strong><br /><br />
		 <strong><?php echo $lang['closeday-notes']; ?></strong><br />
		 <div class="mainboxheader"></div>
		 <input type="number" lang="nb" name="fiveEuro" id="fiveEuro" class="fourDigit defaultinput" value="<?php echo $fiveEuro; ?>" readonly />
		 <input type="number" lang="nb" name="tenEuro" id="tenEuro" class="fourDigit defaultinput" value="<?php echo $tenEuro; ?>" readonly /> 
		 <input type="number" lang="nb" name="twentyEuro" id="twentyEuro" class="fourDigit defaultinput" value="<?php echo $twentyEuro; ?>" readonly /> 
		 <input type="number" lang="nb" name="fiftyEuro" id="fiftyEuro" class="fourDigit defaultinput" value="<?php echo $fiftyEuro; ?>" readonly /> 
		 <input type="number" lang="nb" name="hundredEuro" id="hundredEuro" class="fourDigit defaultinput" value="<?php echo $hundredEuro; ?>" readonly />
		 <strong><input type="number" lang="nb" name="notesTot" id="notesTot" class="fourDigit defaultinput" value="<?php echo $notesTot; ?>" readonly /></strong><br /><br />
		 <table class="purchasetable">
		  <tr>
		   <td><?php echo $lang['tillbalyesterday']; ?>
		   		<strong><input type="number" lang="nb" name="tillYday" id="tillYday" class="fiveDigit purchaseNumber" value="<?php echo number_format($cashintillYday,2,'.',''); ?>" readonly /></strong></td>
		
		   <td><?php echo $lang['tillbalnow']; ?>
		   	<strong><input type="number" lang="nb" name="tillTot" id="tillTot" class="fiveDigit purchaseNumber" value="<?php echo number_format($tillTot,2,'.',''); ?>" readonly /></strong></td>
		 
		   <td><?php echo $lang['closeday-tilldelta']; ?>
		   	<strong><input type="number" lang="nb" name="tillDelta" id="tillDelta" class="fiveDigit<?php if (isset($deltaColour)) { echo " " . $deltaColour; } ?> purchaseNumber" value="<?php echo $tillDelta; ?>" readonly /></strong></td>
		  </tr>
		 </table>
	</div>	 
</div>	
	<br /><br />
<?php

		// Look up opening product weights
		$openingLookup = "SELECT d.category, d.productid, d.purchaseid, d.weight, d.weightDelta, d.prodOpenComment FROM openingdetails d, opening c WHERE d.openingid = c.openingid AND c.openingid = '$openingid' ORDER BY d.category ASC";
		try
		{
			$resultOpening = $pdo3->prepare("$openingLookup");
			$resultOpening->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
		$e = 0;
		$f = 0;
		
		while ($prodOpen = $resultOpening->fetch()) {
			
			$category = $prodOpen['category'];
			$productid = $prodOpen['productid'];
			$purchaseid = $prodOpen['purchaseid'];
			$weight = $prodOpen['weight'];
			$weightDelta = $prodOpen['weightDelta'];
			$prodopencomment = $prodOpen['prodOpenComment'];
			
			if ($category == 1) {
				
				$weightNow = $lang['closeday-yourweight'];
				$deltaNow =	$lang['weightdelta'];	
				
				if ($f == 0) {
					echo "<h5>{$lang['global-flowerscaps']}</h5>";
				}
			
				$selectFlower = "SELECT g.name, g.breed2, p.growType FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.productid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowF = $result->fetch();
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
					
				if ($growtype != '') {
					$name = $name . " ($growtype)";
				} else {
					$name = $name;
				}
					
				$f++;

			} else if ($category == 2) {
				
				$weightNow = $lang['closeday-yourweight'];
				$deltaNow =	$lang['weightdelta'];	
				
				if ($e == 0) {
					echo "<br /><h5>{$lang['global-extractscaps']}</h5>";
				}
			
				$selectExtract = "SELECT name FROM extract WHERE extractid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectExtract");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowE = $result->fetch();
					$name = $rowE['name'];
					
				$e++;
				
			} else {
				
				// Look up categoryname
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
					$type = $row['type'];
					
				if ($type == 1) {
					$weightNow = $lang['closeday-yourweight'];
					$deltaNow =	$lang['weightdelta'];	
				} else {
					$weightNow = $lang['countnow'];
					$deltaNow =	$lang['countdelta'];	
				}
				
				if (${'header'.$category} != 'set') {
					echo "<br /><br /><h5>$catName</h5>";
					${'header'.$category} = 'set';
				}
			
				$selectExtract = "SELECT name FROM products WHERE productid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectExtract");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowE = $result->fetch();
					$name = $rowE['name'];
					
				$g++;
				
			}
			
			if ($weightDelta > 0) {
				$deltaColour = ' positive';
			} else if ($weightDelta < 0) {
				$deltaColour = ' negative';
			} else {
				$deltaColour = '';
			}

	if ($_SESSION['firstOpening'] == 'true') {
			
?>

		 <div class='actionbox-np2'>
			 <div class="mainboxheader"><?php echo $name . ' (' . $purchaseid . ')'; ?></div>
			 <div class="boxcontent">
				 <table class="purchasetable">
				  <tr>
				   <td><?php echo $weightNow; ?>
				   	<input type="number" lang="nb" name="weight" id="weight" class="fourDigit purchaseNumber" value="<?php echo number_format($weight,2,'.',''); ?>" readonly /></td>
				  </tr>
				 </table>
			</div>
			 <br />
			 <span class='usergrouptext2'><?php echo $lang['global-comment']; ?>:</span><br />
			 <em><?php echo $prodopencomment; ?></em>
		 </div>

<?php } else { ?>

		 <div class='actionbox-np2'>
			  <div class="mainboxheader"><?php echo $name . ' (' . $purchaseid . ')'; ?></div>
			   <div class="boxcontent">
					 <table class="purchasetable">
					  <tr>
					   <td><?php echo $weightNow; ?>
					   		<input type="number" lang="nb" name="weight" id="weight" class="fourDigit purchaseNumber" value="<?php echo number_format($weight,2,'.',''); ?>" readonly /></td>
					  </tr>
					  <tr>
					   <td><?php echo $deltaNow; ?>
					   	<input type="number" lang="nb" name="weightDelta" id="weightDelta" class="fourDigit purchaseNumber<?php echo $deltaColour; ?>" value="<?php echo number_format($weightDelta,2,'.',''); ?>" readonly /></td>
					  </tr>
					 </table>
				</div>
			 <br />
			 <span class='usergrouptext2'><?php echo $lang['global-comment']; ?>:</span><br />
			 <em><?php echo $prodopencomment; ?></em>
		 </div>

<?php }
	
		// End loop for each product
		}

echo <<<EOD
<br /><a href='uTil/confirm-open-day.php?oid=$openingid&cid=$closingid&closer={$_SESSION['user_id']}' class='cta1' id='hidecta'>{$lang['openday-main']}</a><br />
<img src='images/spinner.gif' id='showspinner' style='display: none; margin-top: -10px;' width='40' />
<script>
		$('#hidecta').click(function () {
		$("html, body").animate({ scrollTop: $(document).height() });
		$('#showspinner').css('display', 'inline-block');
		$('#hidecta').css('display', 'none');
		});	
</script>
EOD;

displayFooter();
