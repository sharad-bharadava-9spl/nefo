<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page resubmit with a form?
	if ($_POST['pwdaction'] == 'yes') {
			$newpw = crypt($_POST['password'], $_POST['email']);
			$user_id = $_POST['user_id'];
			$email = $_POST['email'];

			
		// Edit user here. Save their new password. Their usergoup has already been updated
		$updateUser = sprintf("UPDATE users SET email = '%s', userPass = '%s' WHERE user_id = '%d';",
$email,
$newpw,
$user_id
);

		try
		{
			$result = $pdo3->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['confirm-passemailupdated'];
		header("Location: profile.php?user_id=" . $user_id);
		exit();

	}

	
	// Get user ID
	if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nomember'],"");
	}
	
	// Query to look for user
	$userDetails = "SELECT u.memberno, u.first_name, u.last_name, u.email, u.photoExt, ug.userGroup, ug.groupName FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'";
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
	$userGroup = $row['userGroup'];
	$groupName = $row['groupName'];
	$first_name = $row['first_name'];
	$last_name = $row['last_name'];
	$email = $row['email'];
	$photoExt = $row['photoExt'];



		pageStart($lang['title-newoperator'], NULL, NULL, "pdebt", "index", $lang['newoperator'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
	echo "
	<center>
<div id='profilearea'>
<span class='profilefirst'><img src='images/members/$user_id.$photoExt' class='salesPagePic' /> #$memberno $first_name</span>
</div>
</center>";


?><br /><br />
<center>
<div id="passwordspan">
   <?php echo $lang['emailandpasstoencrypt']; ?>:<br /><br />
   <form id="registerForm" action="" method="POST">
     <input type="hidden" name='pwdaction' value='yes'>
     <input type="hidden" name='user_id' value='<?php echo $user_id; ?>'>
     <label for="email" class="fakelabel"><?php echo $lang['member-email']; ?>:</label>
     <input type="email" name="email" value="<?php echo $email; ?>"/><br />
     <label for="password" class="fakelabel"><?php echo $lang['index-password']; ?>:</label>
     <input type="password" name="password" /><br /><br />
     <button name='oneClick' type="submit"><?php echo $lang['encrypt']; ?></button>

   </form>
</center>
</div>
<?php

 displayFooter();


?>
