<?php

	require_once 'cOnfig/connection-master.php';
	require_once 'cOnfig/view-loggedout.php';
	require_once 'cOnfig/languages/common.php';
	

	session_start();

	if(!isset($_REQUEST['auth'])){
		header("Location: index.php");
		exit();
	}

	$authURL = base64_decode($_REQUEST['auth']);

	$authData = explode(",", $authURL);

/*	$email = $_REQUEST['email'];
	$password_match = $_REQUEST['auth'];*/

	$email = $authData[0];
	$password_match = $authData[1];

// Lookup domain
try
{
	$result = $pdo->prepare("SELECT domain FROM users WHERE email = :email AND password = :userPass");
	$result->bindValue(':email', $email);
	//$result->bindValue(':userPass', crypt($password, $email));
	$result->bindValue(':userPass', $password_match);
	$result->execute();
}
catch (PDOException $e)
{
		$error = 'Error fetching user: ' . $e->getMessage();
		echo $error;
		exit();
}

$row = $result->fetch();
	$domain = $row['domain'];
	
	
try
{
	$result = $pdo->prepare("SELECT db_pwd FROM db_access WHERE domain = :domain");
	$result->bindValue(':domain', $domain);
	$result->execute();
}
catch (PDOException $e)
{
		$error = 'Error fetching user: ' . $e->getMessage();
		echo $error;
		exit();
}

$row = $result->fetch();
	$db_pwd = $row['db_pwd'];

$db_name = "ccs_" . $domain;
$db_user = $db_name . "u";

// for local
/*$db_pwd = "";
$db_user = "root";*/

$_SESSION['domain'] = $domain;
$_SESSION['db_name'] = $db_name;
$_SESSION['db_user'] = $db_user;
$_SESSION['db_pwd'] = $db_pwd;

try	{
		$pdo2 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
		$pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo2->exec('SET NAMES "utf8"');
}
catch (PDOException $e)	{
		$output = 'Unable to connect to the database server: ' . $e->getMessage();

		echo $output;
		exit();
}
try
{


	// check for strong password

	$checkPassClub = sprintf("SELECT userPass2 FROM users WHERE email = '%s'", $email);
	try
	{
		$resultPassClub = $pdo2->prepare("$checkPassClub");
		$resultPassClub->execute();
		$dataPassClub = $resultPassClub->fetch();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user1: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$userPassClub = $dataPassClub['userPass2'];

	$change_password_club = 0;
	if($userPassClub != ''){
		$change_password_club = 0;
		$password_match_club = $password_match;
		$password_match_club_column = "userPass2";
	}else{
		$change_password_club = 1;
		$password_match_club = $password_match;
		$password_match_club_column = "userPass";
	}

	$result = $pdo2->prepare("SELECT first_name, user_id, memberno, email, userGroup, domain, workStation FROM users WHERE email = :email AND $password_match_club_column = :password;");
	$result->bindValue(':email', $email);
	//$result->bindValue(':password', crypt($password, $email));
	$result->bindValue(':password', $password_match_club);
	$result->execute();
	$data = $result->fetchAll(PDO::FETCH_ASSOC);

}
catch (PDOException $e)
{
		$error = 'Error fetching user: ' . $e->getMessage();
		echo $error;
		exit();
}

// Check if user is registered in club's db - not just in masterdb
if (!$data) {
	$_SESSION['errorMessage'] = "User not recognised in club database!";
	header("Location: index.php");
	die();
} else {

	// Check if user has permissions to log in (extra layer of security)
	if ($data[0]['userGroup'] > 3) {
		$_SESSION['errorMessage'] = "User does not have the required access level!";
		header("Location: index.php");
	    die();
	} else {

			
		$_SESSION['user_id'] = $data[0]['user_id'];
		$_SESSION['username'] = $data[0]['email'];
		$_SESSION['memberno'] = $data[0]['memberno'];
		$_SESSION['first_name'] = $data[0]['first_name'];
		$_SESSION['userGroup'] = $data[0]['userGroup'];
		$_SESSION['workStationAccess'] = $data[0]['workStation'];
		$_SESSION['domain'] = $data[0]['domain'];
		$_SESSION['cloud'] = 'ccsnubev2';
		$authCookie = md5($data[0]['email'].$password_match_club);
		setcookie( "auth_login", "$authCookie", time() + (10 * 365 * 24 * 60 * 60) );
		$_SESSION['successMessage'] = $lang['index-loggedin'];
		if($change_password_club == 1){
			header("Location: new-password.php?user_id=".$data[0]['user_id']);
		}else{
			header("Location: main.php?login");
		}
		exit();
	}
}