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
	$checkClosing = "SELECT closingid, recOpened, recOpenedBy FROM shiftclose ORDER by closingtime DESC LIMIT 1";
	
	$result = mysql_query($checkClosing)
		or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$closingid = $row['closingid'];
		$recOpened = $row['recOpened'];
		$recOpenedBy = $row['recOpenedBy'];
		
	if ($recOpened == '2' && (!isset($_GET['redo']))) {
		
		pageStart($lang['start-shift'], NULL, $validationScript, "pcloseday", "step2", $lang['open-shift-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

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
				
			$result = mysql_query($userDetails)
				or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$memberno = $row['memberno'];
				$first_name = $row['first_name'];
		
			pageStart($lang['start-shift'], NULL, $validationScript, "pcloseday", "step2", $lang['open-shift-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
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
	
			// Write to DB table: RecOpening is in process
			$updateOpening = sprintf("UPDATE shiftclose SET recOpened = '1', recOpenedBy = '%d' WHERE closingid = '%d';",
				mysql_real_escape_string($responsible),
				mysql_real_escape_string($closingid)
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
        return 'Are you sure you want to leave this page?';
    }
});
  }); // end ready
EOD;


	pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "step1", $lang['openday-rec-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
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

<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="open-shift-reception-1.php" method="POST">
<input type="hidden" name="step1" value="complete" />
<br /><strong><?php echo $lang['closeday-coins']; ?></strong><br />
<input type="number" lang="nb" name="oneCent" id="oneCent" class="fourDigit" placeholder="1c" />
<input type="number" lang="nb" name="twoCent" id="twoCent" class="fourDigit" placeholder="2c" /> 
<input type="number" lang="nb" name="fiveCent" id="fiveCent" class="fourDigit" placeholder="5c" /> 
<input type="number" lang="nb" name="tenCent" id="tenCent" class="fourDigit" placeholder="10c" /> 
<input type="number" lang="nb" name="twentyCent" id="twentyCent" class="fourDigit" placeholder="20c" /> 
<input type="number" lang="nb" name="fiftyCent" id="fiftyCent" class="fourDigit" placeholder="50c" /> 
<input type="number" lang="nb" name="oneEuro" id="oneEuro" class="fourDigit" placeholder="1&euro;" /> 
<input type="number" lang="nb" name="twoEuro" id="twoEuro" class="fourDigit" placeholder="2&euro;" /><br /><br />
<strong><?php echo $lang['closeday-notes']; ?></strong><br />
<input type="number" lang="nb" name="fiveEuro" id="fiveEuro" class="fourDigit" placeholder="5&euro;" />
<input type="number" lang="nb" name="tenEuro" id="tenEuro" class="fourDigit" placeholder="10&euro;" /> 
<input type="number" lang="nb" name="twentyEuro" id="twentyEuro" class="fourDigit" placeholder="20&euro;" /> 
<input type="number" lang="nb" name="fiftyEuro" id="fiftyEuro" class="fourDigit" placeholder="50&euro;" /> 
<input type="number" lang="nb" name="hundredEuro" id="hundredEuro" class="fourDigit" placeholder="100&euro;" />
<br /><br />
<table>
 <tr>
  <td><?php echo $lang['closeday-totcoins']; ?>:</td>
  <td><input type="number" lang="nb" name="coinsTot" id="coinsTot" class="fourDigit" readonly /></td>
 </tr>
 <tr>
  <td><?php echo $lang['closeday-totnotes']; ?>:</td>
  <td><input type="number" lang="nb" name="notesTot" id="notesTot" class="fourDigit" readonly /></td>
 </tr>
 <tr>
  <td><?php echo $lang['closeday-tottill']; ?>:</td>
  <td><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit" readonly /></td>
 </tr>
</table>
<br />

 <button name='oneClick' type="submit"><?php echo $lang['closeday-calculate']; ?></button>
</form>


<?php displayFooter();