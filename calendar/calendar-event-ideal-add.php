<?php
// Begin code by sagar

require_once '../cOnfig/connection.php';

$user_id = $_POST['user_id'];

if(isset($_POST["title"]) && !empty($_POST['user_id']))
{
    try {
        $query = "
            INSERT INTO events 
            (user_id,title,start_event, end_event,type) 
            VALUES (:user_id,:title,:start_event, :end_event, :type)";
            $statement = $pdo3->prepare($query);
            $statement->execute(
                array(
                    ':user_id'      => $user_id,
                    ':title'        => $_POST['title'],
                    ':start_event'  => $_POST['start'],
                    ':end_event'    => $_POST['end'],
                    ':type'         => 'ideal',
                )
            );
    } catch (PDOException $e) {
        $error = 'Error insert events table: ' . $e->getMessage();
                echo $error;
                exit();
    }
    
}

header("Location:".$_SERVER['HTTP_REFERER']."");
die;
// End code by sagar
?>