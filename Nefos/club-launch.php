<?php
session_start();
//ini_set("display_errors", "on");
$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	if (isset($_POST['club_id'])) {
		$club_id = $_POST['club_id'];
	} else if (isset($_GET['club_id'])) {
		$club_id = $_GET['club_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}


	function randomPassword($char) {
	    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	    $pass = array(); //remember to declare $pass as an array
	    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	    for ($i = 0; $i < $char; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass); //turn the array into a string
	}

function folder_newname($path){
    $newpath = $path;
    $counter = 1;
    while (file_exists($newpath)) {
          // $newname = $name .'_'. $counter . $ext;
           $newpath = $path.$counter;
           $counter++;
     }
     $new_folder_name = str_replace("../_club/_", "", $newpath);
    return $new_folder_name;
}
	function custom_copy($src, $dst) {  
  
	    // open the source directory 
	    $dir = opendir($src);  
	  
	    // Make the destination directory if not exist 
	    @mkdir($dst);  
	  
	    // Loop through the files in source directory 
	    while( $file = readdir($dir) ) {  
	  
	        if (( $file != '.' ) && ( $file != '..' ) && $file != ".htaccess" && $file != ".gitignore" && !is_dir($src . '/' . $file)) {  
	        	   
	               copy($src . '/' . $file, $dst . '/' . $file);  
	        }  
	    }  
	  
	    closedir($dir); 
	}  
// get club
$getClub = "SELECT shortName,logo_path,member_contract,original_path,email from customers WHERE id = '$club_id'";
	try
	{
		$result = $pdo3->prepare("$getClub");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$clubRow = $result->fetch();
	$shortName = slugify($clubRow['shortName']);
	$dirPath = "../_club/_".$shortName;
	$clubname = folder_newname($dirPath); 
    $clublogo = $clubRow['logo_path']; 
    $cluboriginallogo = $clubRow['original_path']; 
    $clubContract = $clubRow['member_contract'];
    $clubEmail = $clubRow['email'];
    if(!is_dir("../_club")){
    	mkdir("../_club",0777);
    } 
	$newDir = "../_club/_".$clubname;
	$imgDir = "../images/_".$clubname;
	$sourceDir = "../";
	// crete club folders
	if(!is_dir($newDir)){
    	mkdir($newDir, 0777);
	}
	if(!is_dir($imgDir)){
    	mkdir($imgDir, 0777);
    	mkdir($imgDir."/barprodtemp",0777);
    	mkdir($imgDir."/bar-products",0777);
    	mkdir($imgDir."/old_signature_pad",0777);
    	mkdir($imgDir."/dispensesigs",0777);
    	mkdir($imgDir."/expenses",0777);
    	mkdir($imgDir."/ID",0777);
    	mkdir($imgDir."/members",0777);
    	mkdir($imgDir."/memberstemp",0777);
    	mkdir($imgDir."/purchases",0777);
    	mkdir($imgDir."/purchasestemp",0777);
    	mkdir($imgDir."/sigs",0777);
	}

	// Move logo to club folder
	if(!empty($clublogo) || $clublogo != ''){
		rename("../".$clublogo, $imgDir."/logo.png");
		rename("../".$cluboriginallogo, $imgDir."/original_logo.png");
		
		// Update logo path in db
		$newLogoPath = "images/_".$clubname."/logo.png";
		$newRealLogoPath = "images/_".$clubname."/original_logo.png";
		$updateLogo = "UPDATE customers SET logo_path = '$newLogoPath',original_path = '$newRealLogoPath'  WHERE id = '$club_id'";
		try
		{
			$result = $pdo3->prepare("$updateLogo");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching image: ' . $e->getMessage();
				echo $error;
				exit();
		}
	}
	if(!empty($clubContract) || $clubContract != ''){
		rename("../contract.php", $newDir."/contract.php");
	}
	// update launch date
       $launchTime = date("Y-m-d H:i:s");
		$updateLaunchDate = "UPDATE customers SET launchdate = '$launchTime' WHERE id = '$club_id'";
		try
		{
			$launch_result = $pdo3->prepare("$updateLaunchDate");
			$launch_result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching lauch date: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	/*$fp = fopen($newDir."/contract.php","wb");
	fwrite($fp,$clubContract);
	fclose($fp);
	unlink("../".$contr)*/
	
	//custom_copy($sourceDir, $newDir);

	//
		$query = "select max(number) from customers";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$nextMemberNo = $row['0'] + 1;

		// Update Clun number

		$update_club = "UPDATE customers SET number = '$nextMemberNo' where id = '$club_id'";
		try
		{
			$result = $pdo3->prepare("$update_club");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching club: ' . $e->getMessage();
				echo $error;
				exit();
		}
       

	// Crete database & structure
	$get_dbuser = "select user from mysql.user";

  	try
	{
		$result = $pdo->prepare("$get_dbuser");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
   while( $db_users = $result->fetch()){
   		$db_userarr[] = $db_users['user'];
   }

 	$db_connectionStatus = $pdo->getAttribute(constant("PDO::ATTR_CONNECTION_STATUS"));

	$db_connectionStatus  = explode(" " , $db_connectionStatus);
	$db_server = $db_connectionStatus[0];

   $club_domain = $clubname;
   $db_user =  "ccs_".$clubname."u";
   $db_pass = randomPassword(13);
   $dbName = "ccs_".$clubname;

   //  create db_launch
   if(!in_array($db_user, $db_userarr)){
	    $create_dbuser = "create user '".$db_user."'@'' identified by '".$db_pass."'";   

	  	try
		{
			$result = $pdo->prepare("$create_dbuser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching create user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		// crete Db 
	   $create_database = "create database $dbName"; 
		try
		{
			$result = $pdo->prepare("$create_database");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error creating database: ' . $e->getMessage();
				echo $error;
				exit();
		}

     // grant priviledges to dtabse
		$grant_permission = "GRANT ALL PRIVILEGES ON $dbName.* TO '$db_user'@'';";
		try
		{
			$result = $pdo->prepare("$grant_permission");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error creting database: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$hash = generateRandomStringCapitals(10);
		// insert into db_access table
		$addDb = sprintf("INSERT INTO db_access (domain, db_pwd, customer, hash) VALUES ('%s', '%s', '%s', '%s')",$club_domain, $db_pass, $nextMemberNo, $hash);

		try
		{
			$result = $pdo->prepare("$addDb")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}		
		// insert into users table
		$addDbUser = sprintf("INSERT INTO users (email, password, domain) VALUES ('%s', '%s', '%s')",$clubEmail, $db_pass, $club_domain);

		try
		{
			$result = $pdo->prepare("$addDbUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}		
		// insert demo user into users table

		$demoEmail = 'demo@user.com';
		$simplePass = simpleRandomPassword(8);
		$cryptPass = crypt($simplePass, $clubEmail);

		$addDemoUser = sprintf("INSERT INTO users (email, password, domain) VALUES ('%s', '%s', '%s')",$demoEmail, $cryptPass, $club_domain);

		try
		{
			$result = $pdo->prepare("$addDemoUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		// Name of the file
		$filename = 'cOnfig/new-club.sql';

		try	{
	 		$newPDO = new PDO('mysql:host='.$db_server.';dbname='.$dbName, $db_user, $db_pass);
	 		$newPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$newPDO->exec('SET NAMES "utf8"');
		}
		catch (PDOException $e)	{
	  		$output = 'Unable to connect to the database server: ' . $e->getMessage();

	 		echo $output;
	 		exit();
		}

		// Temporary variable, used to store current query
		$templine = '';

		// Read in entire file
		$lines = file($filename);
		// Loop through each line
		foreach ($lines as $line) {
		// Skip it if it's a comment
		    if (substr($line, 0, 2) == '--' || $line == '')
		        continue;

		// Add this line to the current segment
		    $templine .= $line;
		// If it has a semicolon at the end, it's the end of the query
		    if (substr(trim($line), -1, 1) == ';') {
		        // Perform the query
		      	try
				{
					 $newPDO->prepare("$templine")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching details: ' . $e->getMessage();
						echo $error;
						exit();
				}
		        // Reset temp variable to empty
		        $templine = '';
		    }
		}
		// Update domain in systemsetting and users

		try	{
	 		$newPDO2 = new PDO('mysql:host='.$db_server.';dbname='.$dbName, $db_user, $db_pass);
	 		$newPDO2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$newPDO2->exec('SET NAMES "utf8"');
		}
		catch (PDOException $e)	{
	  		$output = 'Unable to connect to the database server2: ' . $e->getMessage();

	 		echo $output;
	 		exit();
		}
	  $sysUpdate = "UPDATE systemsettings SET domain = '$club_domain'";

		try
		{
			$stmt = $newPDO2->prepare($sysUpdate);
			//$stmt->bindValue(":club_domain", $club_domain);
			$stmt->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
     // update club user
	  $upClubUser = "UPDATE users SET userPass = '$cryptPass' , oneTimePassword = '$simplePass' WHERE email = '$demoEmail'";

		try
		{
			$stmt = $newPDO2->prepare($upClubUser);
			//$stmt->bindValue(":club_domain", $club_domain);
			$stmt->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}		

	}
	
	   $maiAdmin = "info@cannabisclub.systems";
	   $email = $clubEmail;
	   $subject = "CCS Club Status";
		$adminmail = new PHPMailer();
		$adminmail->isSMTP();
		$usermail = new PHPMailer();
		$usermail->isSMTP();
		$body = "Hello <b>Admin</b><br>
					<p>The club <b>$club_domain</b> has been approved !</p>";
		sendEmail($adminmail, $maiAdmin, $body, $subject);
		$userMessage = "Hello <b>$club_domain</b><br>
							<p>Congrats ! your club request has been approved. Our representative will contact you soon.</p><br>Thanks & Regards,<br><b>CCS</b>";
		sendEmail($usermail, $email, $userMessage, 'CCS Club Status');

		/*// UPDATE CONTRACT FOR NEW CLUB
		if(!empty($clubContract) || $clubContract != ''){
			$clubContractFile = $newDir."/contract.php";
			file_put_contents($clubContractFile, $clubContract);
		}else{
			file_put_contents($clubContractFile, '');
		}*/
		

   $_SESSION['successMessage'] = "Status Updated !";   
echo "<script type='text/javascript'>window.location.href = 'club.php?club_id=".$club_id."';</script>";
 //  header("location:club.php?club_id=".$club_id);
   die;
