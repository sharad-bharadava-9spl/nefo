<?php


	require_once '../cOnfig/connection-master.php';
	require_once '../cOnfig/languages/common.php';

    session_start();

    if(!empty($_POST['club_email'])){
    	try
		{
			$result = $pdo2->prepare("SELECT COUNT(id) FROM customers WHERE email = :clubemail");
			$result->bindValue(':clubemail', $_POST['club_email']);
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching club: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $result->fetch();
		$clubcount = $row['COUNT(id)'];
		if($clubcount>0){
			echo "false";
		}else{
			echo "true";
		}
    }else{
    	echo "false";
    }