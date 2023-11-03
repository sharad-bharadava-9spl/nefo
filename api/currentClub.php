<?php

require('connectionM_club.php');

if(!empty($_POST['language'])){
	$lang = $_POST['language'];
}else{
	$lang = ""; 
}

if(isset($_POST['member_id']) && isset($_POST['macAddress'])){

	$selectMember = "SELECT current_club FROM members WHERE id =".$_POST['member_id'];
	$result = $pdo->prepare("$selectMember");
	$result->execute();
	$row = $result->fetch();
	$currentClub = $row['current_club'];
	if(empty($currentClub)){
		if($lang=='es')
		{	
			$response = array('flag'=>'0', 'message' => "¡No se ha encontrado ningún club activo!");
		}else{
			$response = array('flag'=>'0', 'message' => "No active club found!");
		}
		//$response = array('flag'=>'0', 'message' => "No active club found!");
		echo json_encode($response); 
		die();
	}
	if($lang=='es')
	{	
		$response = array("flag" => '1', 'message' => '¡Club encontrado con éxito!', 'club_name' => $currentClub);
	}else{
		$response = array("flag" => '1', 'message' => 'Club found successfull!', 'club_name' => $currentClub);
	}
	//$response = array("flag" => '1', 'message' => 'Club found successfull!', 'club_name' => $currentClub);
	echo json_encode($response); 
	die();

}else{
	if($lang=='es')
	{	
		$response = array('flag'=>'0', 'message' => "Todos los campos son obligatorios.");
	}else{
		$response = array('flag'=>'0', 'message' => "All fields are mendatory.");
	}
	//$response = array('flag'=>'0', 'message' => "All fields are mendatory");
	echo json_encode($response); 
}