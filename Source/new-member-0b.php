<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$tempNo = $_SESSION['tempNo'];
	$_SESSION['tempNo2'] = $_SESSION['tempNo'];
	
	$deleteSig = "DELETE FROM newscan WHERE type = '99'";
		try
		{
			$result = $pdo3->prepare("$deleteSig")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
	// Write to newsig table with temp number
	$insertSig = "INSERT INTO newscan (chip, type) VALUES ('{$_SESSION['tempNo']}', 99)";
		try
		{
			$result = $pdo3->prepare("$insertSig")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - Firma", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
<script>
setInterval(function()
{ 
	$.ajax({
	    url:'images/_<?php echo $_SESSION['domain']; ?>/sigs/<?php echo $tempNo; ?>.png',
	    type:'HEAD',
	    error: function()
	    {
	    },
	    success: function()
	    {
	        window.location.replace("new-member-1.php");			
	    }
	});
}, 3000);
</script>


<br />
<center>
<h1><?php echo $lang['awaiting-signature']; ?></h1><br />

<img src="images/signature.gif" /><br /><br />

<a class='cta red' href='new-member-1.php?noSig' style='background-color: red;'><?php echo $lang['skip'] ?></a>

</center>
</div>






<?php displayFooter(); ?>
