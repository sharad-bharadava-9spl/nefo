<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$user_id = $_GET['user_id'];
	
	// Delete old sig file if it exists
	$file = "images/_{$_SESSION['domain']}/sigs/$user_id.png";

	$object_exist = object_exist($google_bucket, $google_root_folder.$file);

	if ($object_exist) {
		//unlink($file);
		delete_object($google_bucket, $google_root_folder.$file);		
	}
	
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

	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes dev-align-center", $lang['member-newmembercaps'] . " - Firma", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
		if ($_GET['aval'] == 'true') {
			
?>
<script>
setInterval(function()
{
$.get('<?php echo $google_root; ?>images/_<?php echo $_SESSION['domain']; ?>/sigs/<?php echo $user_id; ?>.png')
    .done(function() { 
	        window.location.replace("aval-details.php?newSig&user_id=" + "<?php echo $user_id; ?>");			
    }).fail(function() { 
        // not exists code
    })
}, 3000);
</script>

<?php	} else { ?>
<script>
setInterval(function()
{
$.get('<?php echo $google_root; ?>images/_<?php echo $_SESSION['domain']; ?>/sigs/<?php echo $user_id; ?>.png')
    .done(function() { 
	        window.location.replace("profile.php?newSig&user_id=" + "<?php echo $user_id; ?>");			
    }).fail(function() { 
        // not exists code
    })
}, 3000);
</script>

<?php } ?>


<br />
<div class='actionbox-np2'>
	 <div class='mainboxheader'><?php echo $lang['awaiting-signature']; ?></div>
	<div class='boxcontent'>	
		 

		<img src="images/signature.gif" /><br /><br />

		<a class='skipbutton' href='profile.php?user_id=<?php echo $user_id; ?>'><?php echo $lang['skip'] ?></a>

	</div>
</div>






<?php 

	displayFooter(); ?>
