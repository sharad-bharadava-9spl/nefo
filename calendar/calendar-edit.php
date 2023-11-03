<?php 

require_once '../cOnfig/connection.php';

$user_id = $_SESSION['user_id'];
if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
}

if(isset($_POST["calendar_id"]) && !empty($_POST["calendar_id"]))
{

    $access_code = md5($_POST['name'].date("Y-m-d H:i:s"));

    $query = "
    UPDATE calendar 
    SET cale_name=:cale_name, cale_color=:cale_color, access_code = :access
    WHERE cale_id=:cale_id
    ";
    try {
        $statement = $pdo3->prepare($query);
        $statement->execute(
            array(
                ':cale_name'   => $_POST['name'],
                ':cale_color'  => $_POST['color'],
                ':access'      => $access_code,
                ':cale_id'     => $_POST['calendar_id'],
            )
        );
    }catch (PDOException $e){
			$error = 'Error update events: ' . $e->getMessage();
			echo $error;
			exit();
	}
    
}
// add user/usergroups access levels
if(!empty($_POST['edit_usergroup_list_visibility']) || !empty($_POST['edit_users_list_visiblity'])){
        $access_level = 1;

        if(!empty($_POST['choose_access_edit'])){
           $access_level = $_POST['choose_access_edit']; 
       }
       $visible_members = '';

       if(!empty($_POST['edit_users_list_visiblity'])){

            $visible_members = implode(",", $_POST['edit_users_list_visiblity']);

       }
      $queryParam = http_build_query(array("calendar"=> array($_POST['calendar_id'])));

       $calender_access_url = $siteroot."calendar/calendar-access-view.php?user_id=".$user_id."&access=".$access_code."&".$queryParam; 

        try{
            $selectQuery = "SELECT * from calendar_access WHERE calendar_id =".$_POST['calendar_id'];
            $calendarResult = $pdo3->prepare($selectQuery);
            $calendarResult->execute();
        }
        catch (PDOException $e)
        {
                $error = 'Error Insert user access: ' . $e->getMessage();
                echo $error;
                exit();
        }
        $calendar_accessCount = $calendarResult->rowCount(); 

        if($calendar_accessCount == 0){

            try{
                $query = "
                INSERT INTO calendar_access
                (user_id,calendar_id,calendar_url,access_level,member_id,usergroups,created_at) 
                VALUES (:user_id,:calendar_id,:calendar_url,:access_level,:member_id,:usergroups,:created_at) ";
                $member = $pdo3->prepare($query);
                $member->execute(
                    array(
                        ':user_id'           => $user_id,
                        ':calendar_id'       => $_POST['calendar_id'],
                        ':calendar_url'      => $calender_access_url,
                        ':access_level'      => $access_level,
                        ':member_id'         => $visible_members,
                        ':usergroups'        => $_POST['edit_usergroup_list_visibility'],
                        ':created_at'        => date("Y-m-d H:i:s")
                    )
                );
            }
            catch (PDOException $e)
            {
                    $error = 'Error Insert user access: ' . $e->getMessage();
                    echo $error;
                    exit();
            }
        }else{
            try{
                $query = "UPDATE calendar_access 
                SET user_id=:user_id, calendar_url=:calendar_url, access_level=:access_level, member_id=:member_id, usergroups=:usergroups, created_at = :created_at
                WHERE calendar_id=:calendar_id";
                $member = $pdo3->prepare($query);
                $member->execute(
                    array(
                        ':user_id'           => $user_id,
                        ':calendar_id'       => $_POST['calendar_id'],
                        ':calendar_url'      => $calender_access_url,
                        ':access_level'      => $access_level,
                        ':member_id'         => $visible_members,
                        ':usergroups'        => $_POST['edit_usergroup_list_visibility'],
                        ':created_at'        => date("Y-m-d H:i:s")
                    )
                );
            }
            catch (PDOException $e)
            {
                    $error = 'Error Insert user access: ' . $e->getMessage();
                    echo $error;
                    exit();
            }

        }

}


if (!empty($_GET['calendar_delete_id'])) {
    try {
        $query = "
                DELETE from calendar WHERE cale_id=:cale_id ";
        $statement = $pdo3->prepare($query);
        $statement->execute(
         array(
          ':cale_id' => $_GET['calendar_delete_id']
         )
        );
    } catch (PDOException $th) {
        $error = 'Error Delete calendar: ' . $th->getMessage();
        echo $error;
        exit();
    }

    try {
        $query = "
                DELETE from events WHERE event_cale_id=:event_cale_id ";
        $statement = $pdo3->prepare($query);
        $statement->execute(
         array(
          ':event_cale_id' => $_GET['calendar_delete_id']
         )
        );
    } catch (PDOException $th) {
        $error = 'Error Delete calendar: ' . $th->getMessage();
        echo $error;
        exit();
    }
}
header("Location:".$_SERVER['HTTP_REFERER']."");
die;

?>