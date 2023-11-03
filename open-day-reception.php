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
				
		$checkClosing = "SELECT closingid, recOpened, recOpenedBy FROM closing ORDER by closingtime DESC LIMIT 1";
		
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
			$closingid = $row['closingid'];
			$recOpened = $row['recOpened'];
			$recOpenedBy = $row['recOpenedBy'];
			
		if ($recOpened == '2' && (!isset($_GET['redo']))) {
			pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['open-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

			echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>
		{$lang['reception-opened']}
 </div>
</div>

EOD;
			exit();

		} else if ($recOpened == '1' && (!isset($_GET['redo']))) {
			
			// Look up user details
			$userDetails = "SELECT memberno, first_name FROM users WHERE user_id = '{$recOpenedBy}'";
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
			{$lang['reception-open-inprogress-1']}$memberno $first_name{$lang['reception-open-inprogress-2']}
	 </div>
	</div>
	
EOD;
			exit();
	
		} else if (isset($_GET['redo'])) {
		
		
			$openingtime = date('Y-m-d H:i:s');
			$_SESSION['openingtime'] = $openingtime;
			
			$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
			$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));
		
		
		}
	
		// Write to DB Opening table: RecOpening is in process
		$updateOpening = sprintf("UPDATE closing SET recOpened = '1', recOpenedBy = '%d' WHERE closingid = '%d';",
			$responsible,
			$closingid
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
			
	} else if ($_SESSION['firstOpening'] == 'true') {
		
		$selectUsers = "SELECT COUNT(openingid) FROM opening";
		$rowCount = $pdo3->query("$selectUsers")->fetchColumn();
		
		// First ever opening, and no Closing to compare to, so let's write to new variables in Opening table (if row doesn't exist, create it!)
		$checkClosing = "SELECT firstRecOpen, firstRecOpenBy FROM opening";
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
	
		
			
		if ($rowCount == 0) {
			
			
			// No opening exists, so let's create a new row.
			$insertOpening = "INSERT INTO opening (firstRecOpen, firstRecOpenBy) VALUES (1, {$_SESSION['user_id']})";
		try
		{
			$result = $pdo3->prepare("$insertOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
		} else {
			
			// Someone already started the first ever opening:
			$row = $result->fetch();
				$recOpened = $row['firstRecOpen'];
				$recOpenedBy = $row['firstRecOpenBy'];
			
			if ($recOpened == '2' && (!isset($_GET['redo']))) {
				pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['open-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
				echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>
		{$lang['reception-opened']}
 </div>
</div>

EOD;
				exit();
	
			} else if ($recOpened == '1' && (!isset($_GET['redo']))) {
				
				// Look up user details
				$userDetails = "SELECT memberno, first_name FROM users WHERE user_id = '{$recOpenedBy}'";
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
			{$lang['reception-open-inprogress-1']}$memberno $first_name{$lang['reception-open-inprogress-2']}
	 </div>
	</div>
	
EOD;
				exit();
	
			} else if (isset($_GET['redo'])) {
			
			
				// Write to DB Opening table: RecOpening is in process
				$updateOpening = sprintf("UPDATE opening SET firstRecOpen = '1', firstRecOpenBy = '%d';",
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
					
				$openingtime = date('Y-m-d H:i:s');
				$_SESSION['openingtime'] = $openingtime;
				
				$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
		
			}
		}
		
		// Write to DB Opening table: RecOpening is in process
		$updateOpening = sprintf("UPDATE opening SET firstRecOpen = '1', firstRecOpenBy = '%d';",
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

	
	$confirmLeave = <<<EOD
    $(document).ready(function() {
	    
var userSubmitted=false;

$('#registerForm').submit(function() {
userSubmitted = true;
});

$('#skipCount').click(function() {
userSubmitted = true;
});

window.onbeforeunload = function() {
    if(!userSubmitted)
        return 'Are you sure that you want to leave this page?';
};
  }); // end ready
EOD;


	pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "step1 dev-align-center", $lang['openday-rec-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	if ($_SESSION['noCompare'] != 'true') {
		echo "<a href='open-day-reception-2.php?skipCount' id='skipCount' class='cta1' style='width: auto;padding: 5px 10px;'' >{$lang['dont-count']}</a>";
	}
	
?>
   <script>
    $(document).ready(function() {

   function compute() {
          var a = $('#oneCent').val();
          var b = $('#twoCent').val();
          var c = $('#fiveCent').val();
          var d = $('#tenCent').val();
          var e = $('#twentyCent').val();
          var f = $('#fiftyCent').val();
          var g = $('#oneEuro').val();
          var h = $('#twoEuro').val();
          var i = $('#fiveEuro').val();
          var j = $('#tenEuro').val();
          var k = $('#twentyEuro').val();
          var l = $('#fiftyEuro').val();
          var m = $('#hundredEuro').val();
          var total = (+a * 0.01) + (+b * 0.02) + (+c * 0.05) + (+d * 0.1) + (+e * 0.2) + (+f * 0.5) + (+g) + (+h * 2);
          var total2 = (+i * 5) + (+j * 10) + (+k * 20) + (+l * 50) + (+m * 100);
          var total3 = (+a * 0.01) + (+b * 0.02) + (+c * 0.05) + (+d * 0.1) + (+e * 0.2) + (+f * 0.5) + (+g) + (+h * 2) + (+i * 5) + (+j * 10) + (+k * 20) + (+l * 50) + (+m * 100);
          $('#coinsTot').val(total.toFixed(2));
          $('#notesTot').val(total2.toFixed(2));
          $('#tillTot').val(total3.toFixed(2));
        }
        
$(document).on('click keypress keyup blur', function () {
    compute();
});

  }); // end ready

        </script>

<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="open-day-reception-1.php" method="POST">
	<div class="actionbox-np2">
		<div class="boxcontent">
			<input type="hidden" name="step1" value="complete" />
			<br /><strong><?php echo $lang['closeday-coins']; ?></strong><br />
			<div class="mainboxheader"></div>
			<input type="number" lang="nb" name="oneCent" id="oneCent" class="fourDigit defaultinput" placeholder="1c" />
			<input type="number" lang="nb" name="twoCent" id="twoCent" class="fourDigit defaultinput" placeholder="2c" /> 
			<input type="number" lang="nb" name="fiveCent" id="fiveCent" class="fourDigit defaultinput" placeholder="5c" /> 
			<input type="number" lang="nb" name="tenCent" id="tenCent" class="fourDigit defaultinput" placeholder="10c" /> 
			<input type="number" lang="nb" name="twentyCent" id="twentyCent" class="fourDigit defaultinput" placeholder="20c" /> 
			<input type="number" lang="nb" name="fiftyCent" id="fiftyCent" class="fourDigit defaultinput" placeholder="50c" /> 
			<input type="number" lang="nb" name="oneEuro" id="oneEuro" class="fourDigit defaultinput" placeholder="1<?php echo $_SESSION['currencyoperator'] ?>" /> 
			<input type="number" lang="nb" name="twoEuro" id="twoEuro" class="fourDigit defaultinput" placeholder="2<?php echo $_SESSION['currencyoperator'] ?>" /><br /><br />
			<strong><?php echo $lang['closeday-notes']; ?></strong><br />
			<div class="mainboxheader"></div>
			<input type="number" lang="nb" name="fiveEuro" id="fiveEuro" class="fourDigit defaultinput" placeholder="5<?php echo $_SESSION['currencyoperator'] ?>" />
			<input type="number" lang="nb" name="tenEuro" id="tenEuro" class="fourDigit defaultinput" placeholder="10<?php echo $_SESSION['currencyoperator'] ?>" /> 
			<input type="number" lang="nb" name="twentyEuro" id="twentyEuro" class="fourDigit defaultinput" placeholder="20<?php echo $_SESSION['currencyoperator'] ?>" /> 
			<input type="number" lang="nb" name="fiftyEuro" id="fiftyEuro" class="fourDigit defaultinput" placeholder="50<?php echo $_SESSION['currencyoperator'] ?>" /> 
			<input type="number" lang="nb" name="hundredEuro" id="hundredEuro" class="fourDigit defaultinput" placeholder="100<?php echo $_SESSION['currencyoperator'] ?>" />
			<br /><br />
			<table class="purchasetable">
			 <tr>
			  <td><?php echo $lang['closeday-totcoins']; ?>
			  	<input type="number" lang="nb" name="coinsTot" id="coinsTot" class="fourDigit purchaseNumber" readonly /></td>
			  	<td><?php echo $lang['closeday-totnotes']; ?>
			  	<input type="number" lang="nb" name="notesTot" id="notesTot" class="fourDigit purchaseNumber" readonly /></td>
			  	 <td><?php echo $lang['closeday-tottill']; ?>
			  	<input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit purchaseNumber" readonly /></td>
			 </tr>
			</table>
			

			
		</div>
	</div>
	<br />
	 <button name='oneClick' class="cta1" style="width: auto;padding: 5px 10px;" type="submit"><?php echo $lang['closeday-calculate']; ?></button>
</form>


<?php displayFooter();
