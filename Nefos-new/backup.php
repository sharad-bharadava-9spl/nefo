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
		
		// Perform local database backup
		backup_tables();
		
		// Compress the backup file
		gzCompressFile("db-backup.sql");

		// connect and login to FTP server
		/*$ftp_server = FTP_SERVER;
		$ftp_username = FTP_USERNAME;
		$ftp_userpass = FTP_PASSWORD;
		$ftp_conn = ftp_connect($ftp_server) or die($lang['backup-couldnotconnect']);
		$login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);

		$file = "db-backup.sql.gz";

		// upload file
		if (ftp_put($ftp_conn, "/public_ftp/backupsA/".$file, $file, FTP_BINARY))
  		{
  		echo $lang['backup-uploadsuccess'];
  		}
		else
  		{
  		echo $lang['backup-uploaderror'];
  		}

		// close connection
		ftp_close($ftp_conn);
		*/
		// On successful upload, redirect
		$_SESSION['successMessage'] = $lang['backup-backupdone'];
		header("Location: admin.php");
		exit();
	}
	

	pageStart($lang['title-takebackup'], NULL, NULL, "padmin", "index admin", $lang['backup-createandupload'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
echo $lang['backup-pleaseconnect'];
?>

...


<form action='' method='POST' onsubmit="return testInput()">
 <input type='hidden' name='connected' value='yes'>
 <button type="submit"><?php echo $lang['backup-imconnected']; ?></button>
</form>	

<?php

 displayFooter();


?>