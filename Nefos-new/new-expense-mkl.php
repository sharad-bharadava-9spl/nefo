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
	
	
		
	pageStart($lang['title-newexpense'], NULL, $validationScript, "pexpenses", "admin", $lang['upload-receiptC'] . " mkl", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
	if (isset($_GET['expenseid'])) {
		
		$expenseid = $_GET['expenseid'];
		echo <<<EOD
<center>
 <a class='cta' href='new-receipt-upload-mkl.php?expenseid=$expenseid'>{$lang['upload-photo']}</a>
 <a class='cta' href='new-receipt-photo-mkl.php?expenseid=$expenseid'>{$lang['use-webcam']}</a>
</center>
EOD;
		
	} else if (isset($_GET['closeday'])) {
		
		echo <<<EOD
<center>
 <a class='cta' href='new-receipt-upload-mkl.php?closeday=true'>{$lang['upload-photo']}</a>
 <a class='cta' href='new-receipt-photo-mkl.php?closeday=true'>{$lang['use-webcam']}</a>
 <a class='cta' href='new-expense-1-mkl.php?skipFoto&closeday=true' style='background-color: red;'>{$lang['skip']}</a>
</center>
EOD;

	} else if (isset($_GET['closeshift'])) {
		
		echo <<<EOD
<center>
 <a class='cta' href='new-receipt-upload-mkl.php?closeshift=true'>{$lang['upload-photo']}</a>
 <a class='cta' href='new-receipt-photo-mkl.php?closeshift=true'>{$lang['use-webcam']}</a>
 <a class='cta' href='new-expense-1-mkl.php?skipFoto&closeshift=true' style='background-color: red;'>{$lang['skip']}</a>
</center>
EOD;

	} else if (isset($_GET['closeshiftandday'])) {
		
		echo <<<EOD
<center>
 <a class='cta' href='new-receipt-upload-mkl.php?closeshiftandday=true'>{$lang['upload-photo']}</a>
 <a class='cta' href='new-receipt-photo-mkl.php?closeshiftandday=true'>{$lang['use-webcam']}</a>
 <a class='cta' href='new-expense-1-mkl.php?skipFoto&closeshiftandday=true' style='background-color: red;'>{$lang['skip']}</a>
</center>
EOD;

	} else {
		
		echo <<<EOD
<center>
 <a class='cta' href='new-receipt-upload-mkl.php'>{$lang['upload-photo']}</a>
 <a class='cta' href='new-receipt-photo-mkl.php'>{$lang['use-webcam']}</a>
 <a class='cta' href='new-expense-1-mkl.php?skipFoto' style='background-color: red;'>{$lang['skip']}</a>
</center>
EOD;

}