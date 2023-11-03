<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did the page resubmit? Then let's go!
	if ($_POST['connected'] == 'yes') {
		
		// First we download the file
		
		// connect and login to FTP server
		$ftp_server = FTP_SERVER;
		$ftp_username = FTP_USERNAME;
		$ftp_userpass = FTP_PASSWORD;
		$ftp_conn = ftp_connect($ftp_server) or die($lang['backup-couldnotconnect']);
		$login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);

		$file = "db-backup.sql.gz";

		// Download file
		if (ftp_get($ftp_conn, $file, "/public_ftp/backupsA/".$file, FTP_BINARY))
  		{
  		}
		else
  		{
  		echo $lang['backup-downloaderror'];
  		}

		// close connection
		ftp_close($ftp_conn);

		// Next step - we uncompress the file
		gzUncompressFile("db-backup.sql.gz");
		
		// Finally, we apply the backup, overwriting the current database
		restoreBackup();
		
		// On success: redirect to Online page
		$_SESSION['successMessage'] = $lang['backup-restoresuccess'];
		header("Location: admin.php");
		exit();
		
	}
	
	$testinput = <<<EOD
	function testInput(){
		
		var r = confirm("{$lang['backup-areyousure']}");
		
		if (r == true) {
    		return true;
		} else {
    		return false;
		} 

	}
EOD;

	pageStart($lang['title-restorebackup'], NULL, $testinput, "padmin", "index admin", $lang['admin-restore'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<?php echo $lang['backup-pleaseconnect']; ?>...


<form action='' method='POST' onsubmit="return testInput()">
 <input type='hidden' name='connected' value='yes'>
 <button type="submit"><?php echo $lang['backup-imconnected']; ?></button>
</form>	

<?php

 displayFooter();


?>