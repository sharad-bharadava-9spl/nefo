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

	$domain = $_SESSION['domain'];
	
	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - Statutes", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
	echo "<form>";
	
	$file = "_club/_$domain/contract.php";
	
	if (file_exists($file)) {
		include $file;
	}
	
?>



<br />
<center>
	<a class='cta' href='sign.php?domain=<?php echo $domain; ?>' style='background-color: green;' ><?php echo $lang['sign-contract'] ?></a>
	<a class='cta red' href='sign-index.php?domain=<?php echo $domain; ?>' style='background-color: red;' ><?php echo $lang['reject'] ?></a>
</center>
<?php displayFooter();
