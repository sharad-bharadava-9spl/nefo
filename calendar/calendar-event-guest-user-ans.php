<?php

 require_once 'connection-calendar-view.php';
session_start();
if(isset($_POST["reply_user"]))
{


// get event access code 

    $getAccess = "SELECT access,user_id from events where id =".$_POST['reply_event'];

    try {
        $statement_access = $pdo3->prepare($getAccess);
        $statement_access->execute();
    }catch (PDOException $e){
            $error = 'Error update events_members: ' . $e->getMessage();
            echo $error;
            exit();
    }
    $result_access = $statement_access->fetch();
        $access_code = $result_access['access'];
        $event_user_id = $result_access['user_id'];

if(isset($_REQUEST['domain'])){
    $domain = $_REQUEST['domain'];
}

    $calender_url = $siteroot."calendar/calendar-event-view.php?user_id=".$event_user_id."&event_id=".$_POST['reply_event']."&access=".$access_code."&domain=".$domain."&reply_by=".$_POST['reply_user'];



    $query = "
    UPDATE events_guest_members 
    SET ans=:ans
    WHERE user_id=:user_id AND event_id=:event_id
    ";
    try {
        $statement = $pdo3->prepare($query);
        $statement->execute(
            array(
                ':ans'       => $_POST['reply_ans'],
                ':user_id'   => $_POST['reply_user'],
                ':event_id'  => $_POST['reply_event'],
            )
        );
    }catch (PDOException $e){
            $error = 'Error update events_members: ' . $e->getMessage();
            echo $error;
            exit();
    }
/*$statement->debugDumpParams();

die;*/

    // Send user bell notification
/*    try{
        $query = "
        INSERT INTO notification_contact_update 
        (cust_id,notification_type,is_read,created_at,reject_reason) 
        VALUES (:cust_id,:notification_type,:is_read,:created_at,:reject_reason) ";
        $member = $pdo->prepare($query);
        $member->execute(
            array(
                ':cust_id'           => $_POST['reply_event_user_id'],
                ':notification_type' => '4',
                ':is_read'           => '0',
                ':created_at'        => date("Y-m-d H:i:s"),
                ':reject_reason'     => 'User : '.$_SESSION['first_name'].' Event : '.$_POST['title'].' Reply : '.$_POST['reply_ans'].'',
            )
        );
    }
    catch (PDOException $e)
    {
            $error = 'Error Insert user notification: ' . $e->getMessage();
            echo $error;
            exit();
    }*/


/*    if (!empty($_POST['reply_event_user_id'])) {
        $query = "SELECT * FROM users WHERE user_id = ".$_POST['reply_event_user_id']."";
        try {
            $statement = $pdo3->prepare($query);
            $statement->execute();
            $result = $statement->fetch();
        }
        catch (PDOException $e){
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
        }
        
        require '../PHPMailerAutoload.php';

        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'html';
        $mail->isSMTP();
        $mail->Host = "cannabisclub.systems";
        $mail->SMTPAuth = true;
        $mail->Username = "tienda@cannabisclub.systems";
        $mail->Password = "8r4Vt4Cvg5E";
        $mail->SMTPSecure = 'ssl'; 
        $mail->Port = 465;
        $mail->setFrom('tienda@cannabisclub.systems', 'CCS Tienda');
        $mail->addAddress("".$result['email']."");
        $mail->Subject = "Event Invitation!";
        $mail->isHTML(true);
        $mail->Body = '<p>  User : '.$_SESSION['first_name'].'</p><p>Event : '.$_POST['title'].'</p><p> Reply : '.$_POST['reply_ans'].'  </p>';
        $mail->send();
    }*/
}

$_SESSION['successMessage'] = "THank you for your response !";
header("Location:".$_SERVER['HTTP_REFERER']."");
die;

?>