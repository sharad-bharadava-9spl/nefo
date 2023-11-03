<?php
// Begin code by sagar

require_once '../cOnfig/connection.php';

$user_id = $_SESSION['user_id'];
if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
}
if(isset($_POST["name"]))
{

    $access_code = md5($_POST['name'].date("Y-m-d H:i:s"));

    try {
        $query = "
            INSERT INTO calendar 
            (cale_name,cale_color,cale_user_id,access_code) 
            VALUES (:cale_name,:cale_color,:cale_user_id,:access) ";
            $statement = $pdo3->prepare($query);
            $statement->execute(
                array(
                    ':cale_name'    => $_POST['name'],
                    ':cale_user_id' => $user_id,
                    ':cale_color'   => $_POST['color'],
                    ':access'       => $access_code,
                )
            );
    } catch (PDOException $e) {
        $error = 'Error insert calendar table: ' . $e->getMessage();
                echo $error;
                exit();
    }
    
}
$last_id = $pdo3->lastInsertId();
// add user/usergroups access levels
if(!empty($_POST['usergroup_list_visibility']) || !empty($_POST['users_list_visiblity'])){
       
        $access_level = 1;
       $visible_members = '';
      $queryParam = http_build_query(array("calendar"=> array($last_id)));
     
       $calender_access_url = $siteroot."calendar/calendar-access-view.php?user_id=".$user_id."&access=".$access_code."&".$queryParam;

        if(!empty($_POST['choose_access'])){
           $access_level = $_POST['choose_access']; 
       }

       if(!empty($_POST['users_list_visiblity'])){

            $visible_members = implode(",", $_POST['users_list_visiblity']);

       }

        try{
            $query = "
            INSERT INTO calendar_access
            (user_id,calendar_id,calendar_url,access_level,member_id,usergroups,created_at) 
            VALUES (:user_id,:calendar_id,:calendar_url,:access_level,:member_id,:usergroups,:created_at) ";
            $member = $pdo3->prepare($query);
            $member->execute(
                array(
                    ':user_id'           => $user_id,
                    ':calendar_id'       => $last_id,
                    ':calendar_url'      => $calender_access_url,
                    ':access_level'      => $access_level,
                    ':member_id'         => $visible_members,
                    ':usergroups'        => $_POST['usergroup_list_visibility'],
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

header("Location:".$_SERVER['HTTP_REFERER']."");
die;
 
?>