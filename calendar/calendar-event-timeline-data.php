<?php
	// Begin code by sagar
	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings(); 
	
	$selectUsers = "SELECT logins.*,events.id as event_id,events.title,events.type,events.start_event,events.end_event,events.user_id as ev_user_id FROM logins INNER JOIN events ON events.user_id=logins.user_id AND events.type = 'ideal' WHERE time BETWEEN '" . $_GET["start"] . "' AND  '" . $_GET["end"] . "' ORDER BY `id`  DESC";
	
		
	try
	{
		$results = $pdo3->prepare("$selectUsers");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
    $user_list = array();
	while ($user = $results->fetch()) {
        $user_list[] = array(
            'resourceId' => $user['user_id'], 
            'title' 	 => 'Work time'.'( '.date('H:m:s',strtotime($user['time'])).' to '.date('H:m:s',strtotime($user['email'])).')', 
            'start' 	 => $user['time'], 
            'color' 	 => '#6ca542', 
            'end' 		 => $user['email'], 
        );
        $user_list[] = array(
            'resourceId' => $user['user_id'], 
            'id' 		 => $user['event_id'], 
            'title' 	 => $user['title'].'( '.date('H:m:s',strtotime($user['start_event'])).' to '.date('H:m:s',strtotime($user['end_event'])).')', 
            'start' 	 => $user['start_event'], 
            'color' 	 => '#aa0083', 
            'end' 		 => $user['end_event'], 
        );
	}
    echo json_encode($user_list);
    die;
// End code by sagar
?>