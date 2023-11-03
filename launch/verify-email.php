<?php


	require_once '../cOnfig/connection-master.php';
	require_once '../cOnfig/languages/common.php';

    session_start();

    if(!empty($_GET['nts_key'])){
    	try
		{
			$update_id = str_replace("'","\'",str_replace('%', '&#37;', trim($_GET['nts_key'])));
			$result = $pdo2->prepare("UPDATE temp_customers SET nts_email_verify=1 WHERE nts_key= :update_id");
			$result->bindValue(':update_id', base64_decode($update_id));
			$result->execute();

			$selectDetails = "SELECT * from temp_customers WHERE nts_key='".base64_decode($update_id)."'"; 
            $user_result = $pdo2->prepare("$selectDetails");
            $user_result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching club: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$id = $user_result->fetch()['id'];
		$nts_language = $user_result->fetch()['nts_language'];
		$_SESSION['temp_id'] = $id;
		$_SESSION['club_step'] = "step2";
		$_SESSION['lang'] = $nts_language;
		header("location:new-club-2.php");
    }else{
    	echo "false";
	}