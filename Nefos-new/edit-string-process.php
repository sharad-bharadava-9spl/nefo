<?php
	// created by konstant for task-15060600 on 24-03-2022
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);


	if (isset($_POST['string_id'])) {
		
	    $string_slug = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['string_slug']))); 
	    $string_en = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['string_en']))); 
	    $string_es = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['string_es']))); 
	    $string_ca = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['string_ca']))); 
	    $string_fr = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['string_fr']))); 
	    $string_nl = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['string_nl']))); 
	    $string_it = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['string_it']))); 
	    $string_id = $_POST['string_id'];

 		$updateTime = date("Y-m-d H:i:s");

 		// check if this string is exist

		$checkString = "SELECT string_slug FROM language_strings WHERE string_slug = '$string_slug' AND id !=$string_id";

		try
		{
			$chk_result = $pdo3->prepare("$checkString");
			$chk_result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$check_count = $chk_result->rowCount();

		if($check_count > 0){
			$_SESSION['errorMessage'] = "This string is already exist!";
			header("Location: new-string.php");
			exit();
		}

		$updateString = "UPDATE language_strings SET string_slug = '$string_slug', string_en = '$string_en',string_es = '$string_es',string_ca = '$string_ca',string_fr = '$string_fr',string_nl = '$string_nl',string_it = '$string_it', created_at = '$updateTime' WHERE id=$string_id";  

				try
				{
					$result = $pdo3->prepare("$updateString")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}		

		$_SESSION['successMessage'] = "String updated successfully!";
		header("Location: language-strings.php");
		exit();
	}