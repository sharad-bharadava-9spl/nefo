<?php

// Begin code by sagar
require_once '../cOnfig/connection.php';

$user_id = $_SESSION['user_id'];
if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
}

 $query = "SELECT events.*,events.id as event_id,calendar.* FROM events LEFT JOIN calendar ON events.event_cale_id=calendar.cale_id LEFT JOIN events_members ON events_members.event_id=events.id  WHERE calendar.cale_user_id= ".$user_id." OR  events_members.user_id=".$user_id." GROUP BY events.id  ORDER BY events.id ";
if (!empty($_GET['caledar_id'])) {
    $caledar_id = unserialize($_GET['caledar_id']);
    if (is_array($caledar_id) && !empty($caledar_id)) {
        $query = "SELECT events.*,events.id as event_id,calendar.* FROM events LEFT JOIN calendar ON events.event_cale_id=calendar.cale_id LEFT JOIN events_members ON events_members.event_id=events.id  WHERE event_cale_id IN (".implode(',',$caledar_id).") AND (calendar.cale_user_id= ".$user_id."  OR  events_members.user_id=".$user_id.")  GROUP BY events.id ORDER BY id ";
    }
}   
    try {
        $statement = $pdo3->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
    }
    catch (PDOException $e){
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
$i=0;
    foreach($result as $row)
    {
        $data[] = array(
        'id'          => $row["event_id"],
        'user_id'     => $row["user_id"],
        'cale_user_id'=> $row["cale_user_id"],
        'title'       => $row["title"],
        'location'    => $row["location"],
        'description' => $row["description"],
        'start'       => $row["start_event"],
        'color'       => $row["cale_color"],
        'invite_email'=> $row["invite_email"],
        'end'         => $row["end_event"]
        );



       $i++;
    }
       
echo json_encode($data);


// End code by sagar
?>