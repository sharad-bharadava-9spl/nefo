<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	$accessLevel = 3;
	$station = 'reception';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	authorizeStation($station);
	
	getSettings();
	
	if (isset($_GET['id'])) {
		
		$wsid = $_GET['id'];
		$_SESSION['workstation'] = $wsid;
		
	} else if ($_SESSION['workstation'] == '' || $_SESSION['workstation'] == 0) {
		
		echo "ERROR: No has elegido un puesto!";
		exit();
		
	}
	
	// Query to look up workstations
	$selectEmails = "SELECT name, dispensary, bar, reception FROM workstations WHERE id = {$_SESSION['workstation']}";

	$result = mysql_query($selectEmails)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());

	$row = mysql_fetch_array($result);
		$dispensary = $row['dispensary'];
		$reception = $row['reception'];
		$bar = $row['bar'];
	
	// Check if user arrived here by scanning card
	if (isset($_POST['cardid'])) {
		
		$cardid = $_POST['cardid'];
		
		// Query to look up user
		$userDetails = "SELECT user_id FROM users WHERE cardid = '{$cardid}'";
		
		// Does user ID exist?
		$userCheck = mysql_query($userDetails);
		
		if(mysql_num_rows($userCheck) == 0) {
	   		handleError($lang['error-keyfob'],"");
		}
		
		$result = mysql_query($userDetails)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
		
		if ($result) {
			$row = mysql_fetch_array($result);
				$user_id = $row['user_id'];
		}
			// On success: redirect.
			header("Location: mini-profile.php?user_id={$user_id}");
			exit();
	}
			
	pageStart("CCS", NULL, $testinput, "pindex", "loggedIn", $lang['reception'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
<script>
$(document).ready(function() {
    $("#focus").focus().bind('blur', function() {
        $(this).focus();
    }); 

    $("html").click(function() {
        $("#focus").val($("#focus").val()).focus();
    });

    //disable the tab key
    $(document).keydown(function(objEvent) {
        if (objEvent.keyCode == 9) {  //tab pressed
            objEvent.preventDefault(); // stops its action
       }
    })      
});
</script>
<br /><br />
<center>
<span class="ctalinks">

<?php if ($_SESSION['userGroup'] == 1) { ?>

 <a href="new-dispense.php"><span id="dispenseCTA"></span><br /><?php echo $lang['index-dispense']; ?></a>
 <a href="menu.php"><span id="productsCTA"></span><br /><?php echo $lang['index-menu']; ?></a>
 <a href="bar-new-sale.php"><span id="barCTA"></span><br /><?php echo $lang['barC']; ?></a><br />
 <a href="scan-profile.php"><span id="profileCTA"></span><br /><?php echo $lang['member-profilecaps']; ?></a>
 <a href="members.php"><span id="membersCTA"></span><br /><?php echo $lang['index-membersC']; ?></a>
 <a href="new-member-0.php"><span id="newmemberCTA"></span><br /><?php echo $lang['index-newmember']; ?></a>
 
<?php } else { 
	
if ($dispensary == 1) { ?>
	
 <a href="new-dispense.php"><span id="dispenseCTA"></span><br /><?php echo $lang['index-dispense']; ?></a>
 <a href="menu.php"><span id="productsCTA"></span><br /><?php echo $lang['index-menu']; ?></a>
 
<?php	}
if ($bar == 1) { ?>
	
 <a href="bar-new-sale.php"><span id="barCTA"></span><br /><?php echo $lang['barC']; ?></a>
 
<?php	}
	
if ($reception == 1) { ?>
	
 <br /><a href="members.php"><span id="membersCTA"></span><br /><?php echo $lang['index-membersC']; ?></a>
 <a href="new-member-0.php"><span id="newmemberCTA"></span><br /><?php echo $lang['index-newmember']; ?></a>		
 
<?php	} ?>

<a href="scan-profile.php"><span id="profileCTA"></span><br /><?php echo $lang['member-profilecaps']; ?></a>

<?php
 
 } ?>

</span>
</center>

<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="" autocomplete="off" method="POST">
 <input type="text" name="cardid" id="focus" maxlength="30" autofocus value="" /><br />
<button name='oneClick' type="submit"><?php echo $lang['form-accept']; ?></button>
</form>
