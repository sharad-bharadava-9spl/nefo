<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	// Generate random temporary expense number, to use throughout the process.
	$tempNo = "_" . generateRandomString();
	$_SESSION['tempNo'] = $tempNo;
	
	
		
	pageStart($lang['title-newexpense'], NULL, $validationScript, "pexpenses", "admin", $lang['upload-receiptC'] . " CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
	if (isset($_GET['expenseid'])) {
		
		$expenseid = $_GET['expenseid'];
		echo <<<EOD
<center>
 <a class='cta' href='new-receipt-upload.php?expenseid=$expenseid'>{$lang['upload-photo']}</a>
 <a class='cta' href='new-receipt-photo.php?expenseid=$expenseid'>{$lang['use-webcam']}</a>
</center>
EOD;
		
	} else if (isset($_GET['closeday'])) {
		
		echo <<<EOD
<center>
 <a class='cta' href='new-receipt-upload.php?closeday=true'>{$lang['upload-photo']}</a>
 <a class='cta' href='new-receipt-photo.php?closeday=true'>{$lang['use-webcam']}</a>
 <a class='cta' href='new-expense-1.php?skipFoto&closeday=true' style='background-color: red;'>{$lang['skip']}</a>
</center>
EOD;

	} else if (isset($_GET['closeshift'])) {
		
		echo <<<EOD
<center>
 <a class='cta' href='new-receipt-upload.php?closeshift=true'>{$lang['upload-photo']}</a>
 <a class='cta' href='new-receipt-photo.php?closeshift=true'>{$lang['use-webcam']}</a>
 <a class='cta' href='new-expense-1.php?skipFoto&closeshift=true' style='background-color: red;'>{$lang['skip']}</a>
</center>
EOD;

	} else if (isset($_GET['closeshiftandday'])) {
		
		echo <<<EOD
<center>
 <a class='cta' href='new-receipt-upload.php?closeshiftandday=true'>{$lang['upload-photo']}</a>
 <a class='cta' href='new-receipt-photo.php?closeshiftandday=true'>{$lang['use-webcam']}</a>
 <a class='cta' href='new-expense-1.php?skipFoto&closeshiftandday=true' style='background-color: red;'>{$lang['skip']}</a>
</center>
EOD;

	} else {
		
		echo <<<EOD
<center>
 <a class='cta' href='new-receipt-upload.php'>{$lang['upload-photo']}</a>
 <a class='cta' href='new-receipt-photo.php'>{$lang['use-webcam']}</a>
 <a class='cta' href='new-expense-1.php?skipFoto' style='background-color: red;'>{$lang['skip']}</a>
</center>
EOD;

}