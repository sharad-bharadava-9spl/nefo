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
	
	$checkClosing = "SELECT closingid, closingtime FROM recshiftclose ORDER by closingtime DESC LIMIT 1";
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
		$closingtime = $row['closingtime'];
		
		
	$_SESSION['closingtime'] = $row['closingtime'];
	$_SESSION['closingid'] = $row['closingid'];

	$responsible = $_SESSION['user_id'];
			


	
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


	pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "step1", $lang['openday-rec-two'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	if ($_SESSION['noCompare'] != 'true') {
		// echo "<a href='open-day-reception-2.php?skipCount' id='skipCount' class='cta' style='width: 400px;'>{$lang['dont-count']}</a>";
	}
	
?>


<center>
<div class="actionbox-np2">
	<div class="boxcontent">
		<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="open-reception-shift-1.php" method="POST">
		<input type="hidden" name="step1" value="complete" />
		<table>
		 <tr>
		  <td><?php echo $lang['closeday-tottill']; ?>:</td>
		  <td><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit defaultinput" step="0.01" /></td>
		 </tr>
		</table>
		<br /><br />

		 <button name='oneClick' class="cta4" type="submit"><?php echo $lang['closeday-calculate']; ?></button>
		</form>
	</div>
</div>
</center>


<?php displayFooter();
