<?php


	require_once '../cOnfig/connection-master.php';
	require_once '../cOnfig/languages/common.php';

    session_start();

    if(!empty($_POST['official_club'])){
    	try
		{
			$result = $pdo2->prepare("SELECT COUNT(id) FROM customers WHERE longName = :clubname");
			$result->bindValue(':clubname', $_POST['official_club']);
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