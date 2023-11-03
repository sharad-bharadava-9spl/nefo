<?php

	session_start();
	
	if (isset($_GET['domain'])) {
		
		if ($_GET['domain'] == 'thegreenhousetenerife') {
			$_SESSION['domain'] = 'greenhousetenerife';
		} else {
			$_SESSION['domain'] = $_GET['domain'];
		}
		
	}
	
	require_once 'cOnfig/connection-tablet.php';
	require_once 'cOnfig/view-nohead.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	
	
	// Send domain as GET
	// Look up pwd for domain
	// Set db name, db pwd, db user based on the above
	// ALl best suited for a separate connection file methinks: all in _club/_domain
	// Go go and test
	
	unset($_SESSION['tempNo']);

	if (isset($_GET['signed'])) {
		
		$_SESSION['successMessage'] = $lang['signature-saved'];
		
		$deleteTemp = "DELETE FROM newscan WHERE type = 99";
		try
		{
			$result = $pdo3->prepare("$deleteTemp")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
					
	}
	
	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - Statutes", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>

<center>
<img src="images/logo.png" />
 <span class="ctalinks">
  <a href="view-contract.php?domain=<?php echo $domain; ?>"><span id="contractCTA"></span><br /><?php echo $lang['read-contract']; ?></a>
  <a href="sign.php?domain=<?php echo $domain; ?>"><span id="signCTA"></span><br /><?php echo $lang['sign-contract']; ?></a>
 </span>
</center>


<?php displayFooter(); ?>
