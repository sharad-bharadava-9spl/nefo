<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/functions.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	
	if (isset($_SESSION['userError'])) {
		$userError = $_SESSION['userError'];
	} else {
		$userError = $lang['error-somethingwrong'];
	}

		if (isset($_SESSION['systemError'])) {
		$systemError = $_SESSION['systemError'];
	} else {
		$systemError = $lang['error-nosystemerror'];
	}

	pageStart($lang['title-error'], NULL, NULL, "perror", NULL, "ERROR !!", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

  <h3><?php echo $lang['error-sorry']; ?></h3>
  <p><b><?php echo $lang['error-message']; ?>:</b> <?php echo $userError; ?></p>
  <?php debugPrint("<hr /><b>{$lang['error-systemmessage']}:</b> {$systemError}") ?>

<?php displayFooter(); ?>