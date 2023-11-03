<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$user_id = $_GET['user_id'];
	
	// Delete old sig file if it exists
	$file = "images/_{$_SESSION['domain']}/sigs/$user_id.png";
	if (file_exists($file)) {
		unlink($file);		
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

	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - Firma", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
		if ($_GET['aval'] == 'true') {
			
?>
<script>
setInterval(function()
{
$.get('images/_<?php echo $_SESSION['domain']; ?>/sigs/<?php echo $user_id; ?>.png')
    .done(function() { 
	        window.location.replace("aval-details.php?newSig&user_id=" + <?php echo $user_id; ?>);			
    }).fail(function() { 
        // not exists code
    })
}, 3000);
</script>

<?php	} else { ?>
<script>
setInterval(function()
{
$.get('images/_<?php echo $_SESSION['domain']; ?>/sigs/<?php echo $user_id; ?>.png')
    .done(function() { 
	        window.location.replace("profile.php?newSig&user_id=" + <?php echo $user_id; ?>);			
    }).fail(function() { 
        // not exists code
    })
}, 3000);
</script>

<?php } ?>


<br />
<center>
<h1><?php echo $lang['awaiting-signature']; ?></h1><br />

<img src="images/signature.gif" /><br /><br />

<a class='cta red' href='profile.php?user_id=<?php echo $user_id; ?>' style='background-color: red;'><?php echo $lang['skip'] ?></a>

</center>
</div>






<?php 

	displayFooter(); ?>
