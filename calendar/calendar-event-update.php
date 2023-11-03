<?php

require_once '../cOnfig/connection.php';
require '../PHPMailerAutoload.php';
$mail = new PHPMailer();
$file_name = $_POST['attachment_old_name']; 
$fileNames = array_filter($_FILES['attachments']['name']);

$maximum_files = $_POST['max_files'];

$user_id = $_SESSION['user_id'];
if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
}

$data = array(
   $_POST['edit_calendar_id']
);

$calendar_query = http_build_query(array('calendar' => $data));



if (!empty($_POST['remove_attachments']) || count($fileNames) > 0) {

       // delete attachments
        $id= $_POST['id'];
        $getAttachments = "SELECT file_name from event_attachments where event_id = ".$id;
                try
                {
                    $delete_attach_results = $pdo3->prepare("$getAttachments");
                    $delete_attach_results->execute();
                }
                catch (PDOException $e)
                {
                        $error = 'Error fetching attachment: ' . $e->getMessage();
                        echo $error;
                        exit();
                }
                $attachCount = $delete_attach_results->rowCount();
                if($attachCount > 0){
                    while($deattachRow = $delete_attach_results->fetch()){
                        
                        unlink($deattachRow['file_name']); 
                            
                    }
                    $deleteEventAttach = "DELETE FROM event_attachments where event_id = $id";
                    try
                    {
                        $results = $pdo3->prepare("$deleteEventAttach");
                        $results->execute();
                    }
                    catch (PDOException $e)
                    {
                            $error = 'Error fetching user: ' . $e->getMessage();
                            echo $error;
                            exit();
                    }
                }
   /* $file_name = null;
    if (file_exists('images/event_images/'.$_POST['attachment_old_name'])) {
        unlink('images/event_images/'.$_POST['attachment_old_name']);
    }
    if (!empty($_FILES['attachments']['name'])) {
        $target_dir = "images/event_images/";
        $target_file = $target_dir . basename($_FILES["attachments"]["name"]);
        
        if (move_uploaded_file($_FILES['attachments']['tmp_name'], $target_file)) {
            $file_name = $_FILES['attachments']['name'];
        }
    }*/
}

$max_file = 8;
$not_Allowed_extention = array("exe","js","php","java","sql","mp3","xml","ogg","css","html","json","msu","msi","graphql","pif","application","gadget","msp","com","scr","hta","cpl","jar","bat","cmd","vb","vbs","vbe","jse","ws","wsf","wsc","wsh","ps1","ps1xml","ps2","ps2xml","psc1","psc2","msh","msh1","msh2","mshxml","msh1xml","msh2xml","scf","lnk","inf","reg");
$not_allowed = array("application/javascript", "application/json", "application/x-www-form-urlencoded", "application/xml", "application/sql", "application/graphql", "application/ld+json", "audio/mpeg", "audio/ogg", "text/css", "text/html", "text/xml", "application/vnd.api+json", "application/octet-stream", "text/javascript", "application/x-msdownload");
   $event_upload_dir = "images/event_images";     // The directory for the video to be saved in

    if(!is_dir($event_upload_dir)){
      mkdir($event_upload_dir, 0777);
    }
    $event_upload_dir = "images/event_images";   
    $event_upload_path = $event_upload_dir."/";      
    $event_prefix = "event_";      
    $event_name = $event_prefix.strtotime(date('Y-m-d H:i:s'));
    $event_location = $event_upload_path.$event_name; 

        $insertValuesSQL = '';
        $count_attach_files = count($fileNames);
        if(!empty($fileNames)){ 
            if(count($fileNames) <= $maximum_files){
                foreach($_FILES['attachments']['name'] as $key=>$val){ 
                    // File upload path 
                   
                    $event_name = $_FILES['attachments']['name'][$key];
                    $event_tmp = $_FILES['attachments']['tmp_name'][$key];
                    $event_size = $_FILES['attachments']['size'][$key];
                    $event_type = $_FILES['attachments']['type'][$key];
                    $filename = basename($_FILES['attachments']['name'][$key]);
                    $file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
                                  //check if the file size is above the allowed limit
                   // echo $event_type."<br>";
                   if(in_array($file_ext, $not_Allowed_extention)){
                         $_SESSION['errorMessage']  = "Please upload valid file types only !";
                         header("Location: calendar.php");
                         die;
                     }
                    $mimetype = mime_content_type($event_tmp); 
                    if(in_array($mimetype, $not_allowed)){
                         $_SESSION['errorMessage']  = "Please upload valid files !";
                         header("Location: calendar.php");
                         die;
                      }
                    if ($event_size > ($max_file*1048576)) {
                        $_SESSION['errorMessage'] = "file must be under ".$max_file."MB in size";
                        header("Location: calendar.php");
                        die;
                      }

                       
                       $event_path = $event_location.$key.".".$file_ext;

                            move_uploaded_file($event_tmp, $event_path); 
                            chmod($event_path, 0777);
                     
                    // Check whether file type is valid 
                  
                            // Image db insert sql 
                            $insertValuesSQL .= "('event_id', '".$event_path."', NOW()),"; 
                            // add attachment
                            $mail->AddAttachment($event_path);
                       
                }
            }else{
                $_SESSION['errorMessage']  = "Sorry, you can upload maximum ".$maximum_files." files !";
                header("Location: calendar.php");
                die;
            }
        }
     
         $insertValuesSQL = trim($insertValuesSQL, ','); 
        if($insertValuesSQL != ''){
            $insertValuesSQL = str_replace("event_id", $_POST['id'], $insertValuesSQL);
             $insertevent_attach = "INSERT INTO event_attachments (event_id, file_name, uploaded_on) VALUES $insertValuesSQL";  
            try
            {
                $attach_result = $pdo3->prepare("$insertevent_attach")->execute();
            }
            catch (PDOException $e)
            {
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
            }
        } 

$usergroups = '';
if(!empty($_POST['usergroup_list'])){
    $usergroups = implode(",", $_POST['usergroup_list']);
}
if(isset($_POST["id"]))
{
     // generate access code for view
     $access_code = md5($_POST['edit_title'].$_POST['start']); 

       $edit_invite_email = '';
   if(!empty($_POST['edit_invite_email'])){
        $edit_invite_email = implode(",", $_POST['edit_invite_email']);
    }

    $end = NULL;

    if(!empty($_POST['edit_end_recurr'])){
        $end_date = $_POST['edit_end_recurr'];
    }else{
        if($_POST['end'] != ''){
            $end_date = $_POST['end'];
        }else{
            $end_date = NULL;
        }
    }

    $doamin = $_SESSION['domain'];

$calender_url = $siteroot."calendar/calendar-event-view.php?user_id=".$user_id."&event_id=".$_POST['id']."&access=".$access_code."&domain=".$domain;

    $query = "
    UPDATE events 
    SET title=:title, start_event=:start_event, end_event=:end_event, usergroups=:usergroups, location=:location, description=:description, recurring_time = :recurring_time, invite_email= :invite_email, access= :access
    WHERE id=:id
    ";
    try {
        $statement = $pdo3->prepare($query);
        $statement->execute(
            array(
                ':title'         => $_POST['edit_title'],
                ':location'      => $_POST['location'],
                ':invite_email'  => $edit_invite_email,
                ':description'   => $_POST['description'],
                ':recurring_time'=> $_POST['recurring_time'],
                ':start_event'   => $_POST['start'],
                ':end_event'     => $end_date,
                ':usergroups'    => $usergroups,
                ':id'            => $_POST['id'],
                ':access'       => $access_code
            )
        );
    }catch (PDOException $e){
			$error = 'Error update events: ' . $e->getMessage();
			echo $error;
			exit();
	}
    
}

if (isset($_POST['users_list']) && !empty($_POST['id'])) {
    try {
        $query = "
                DELETE from events_members WHERE event_id=:id ";
        $statement = $pdo3->prepare($query);
        $statement->execute(
         array(
          ':id' => $_POST['id']
         )
        );
    } catch (PDOException $th) {
        $error = 'Error Delete events_memebers: ' . $th->getMessage();
        echo $error;
        exit();
    }

    foreach ($_POST['users_list'] as $key ) {
        try{
            $query = "
            INSERT INTO events_members 
            (user_id,event_id) 
            VALUES (:user_id,:event_id) ";
            $member = $pdo3->prepare($query);
            $member->execute(
                array(
                    ':user_id'  => $key,
                    ':event_id' => $_POST['id'],
                )
            );
        }
        catch (PDOException $e)
        {
                $error = 'Error Insert events_members: ' . $e->getMessage();
                echo $error;
                exit();
        }
    }

    // update notifications

        try{
            $query = "
            INSERT INTO notifications
            (customer,user_id,type,url,msgread,time,notification,source) 
            VALUES (:cust_id,:user_id,:notification_type,:url,:msgread,:created_at,:notification,:source) ";
            $member = $pdo3->prepare($query);
            $member->execute(
                array(
                    ':cust_id'           => $key,
                    ':user_id'           => $user_id,
                    ':notification_type' => '4',
                    ':url'               => $calender_url,
                    ':msgread'           => '0',
                    ':created_at'        => date("Y-m-d H:i:s"),
                    ':notification'      => $_POST['edit_title'],
                    ':source'            => '2'
                )
            );
        }
        catch (PDOException $e)
        {
                $error = 'Error Insert user notification: ' . $e->getMessage();
                echo $error;
                exit();
        }


}


if (isset($_POST['usergroup_list']) && !empty($_POST['id'])) {
    try {
        $query = "
                DELETE from events_usergroups WHERE event_id=:id ";
        $statement = $pdo3->prepare($query);
        $statement->execute(
         array(
          ':id' => $_POST['id']
         )
        );
    } catch (PDOException $th) {
        $error = 'Error Delete events_usergroups: ' . $th->getMessage();
        echo $error;
        exit();
    }

    foreach ($_POST['usergroup_list'] as $key ) {
        try{
            $query = "
            INSERT INTO events_usergroups 
            (usergroup,event_id) 
            VALUES (:usergroup,:event_id) ";
            $member = $pdo3->prepare($query);
            $member->execute(
                array(
                    ':usergroup'  => $key,
                    ':event_id' => $_POST['id'],
                )
            );
        }
        catch (PDOException $e)
        {
                $error = 'Error Insert events_usergroups: ' . $e->getMessage();
                echo $error;
                exit();
        }
    }
        // update notifications

      try{
            $query = "
            INSERT INTO notifications
            (usergroup,user_id,type,url,msgread,time,notification,source) 
            VALUES (:usergroup,:user_id,:notification_type,:url,:msgread,:created_at,:notification,:source) ";
            $member = $pdo3->prepare($query);
            $member->execute(
                array(
                    ':usergroup'           => $key,
                    ':user_id'           => $user_id,
                    ':notification_type' => '4',
                    ':url'               => $calender_url,
                    ':msgread'           => '0',
                    ':created_at'        => date("Y-m-d H:i:s"),
                    ':notification'      => $_POST['edit_title'],
                    ':source'            => '2'
                )
            );
        }
        catch (PDOException $e)
        {
                $error = 'Error Insert user notification: ' . $e->getMessage();
                echo $error;
                exit();
        }
}

if (isset($_POST['edit_notifi_mini']) && !empty($_POST['id'])) {
    try {
        $query = "
                DELETE from event_notification_time WHERE en_event_id=:id ";
        $statement = $pdo3->prepare($query);
        $statement->execute(
         array(
          ':id' => $_POST['id']
         )
        );
    } catch (PDOException $th) {
        $error = 'Error Delete event_notification_time: ' . $th->getMessage();
        echo $error;
        exit();
    }

    foreach ($_POST['edit_notifi_mini'] as $key ) {
        try{
            $query = "
            INSERT INTO event_notification_time 
            (en_minutes,en_event_id,en_event_start_date,en_event_end_date) 
            VALUES (:en_minutes,:en_event_id,:en_event_start_date,:en_event_end_date) ";
            $member = $pdo3->prepare($query);
            $member->execute(
                array(
                    ':en_minutes'          => $key,
                    ':en_event_id'         => $_POST['id'],
                    ':en_event_start_date' => $_POST['start'],
                    ':en_event_end_date'   => $_POST['end'],
                )
            );
        }
        catch (PDOException $e)
        {
                $error = 'Error Insert  event_notification_time table: ' . $e->getMessage();
                echo $error;
                exit();
        }
    }
}




if (!empty($_POST['edit_invite_email'])) {
    $addresses = $_POST['edit_invite_email'];
    if (!empty($addresses)) {
        // delete guest users
            try {
                $query = "
                        DELETE from events_guest_members WHERE event_id=:id ";
                $statement = $pdo3->prepare($query);
                $statement->execute(
                 array(
                  ':id' => $_POST['id']
                 )
                );
            } catch (PDOException $th) {
                $error = 'Error Delete events_memebers: ' . $th->getMessage();
                echo $error;
                exit();
            }
        foreach ($addresses as $key) {

                try{
                    $query = "
                    INSERT INTO events_guest_members 
                    (user_id,event_id) 
                    VALUES (:user_id,:event_id) ";
                    $guest_member = $pdo3->prepare($query);
                    $guest_member->execute(
                        array(
                            ':user_id'  => $key,
                            ':event_id' => $_POST['id'],
                        )
                    );
                }
                catch (PDOException $e)
                {
                        $error = 'Error Insert events_members: ' . $e->getMessage();
                        echo $error;
                        exit();
                }

                $visitor_calendar_url = $calender_url."&reply_by=".base64_encode($key);


                $file_to_attach = 'images/event_images/';

               // $mail = new PHPMailer(true);
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
                
                    $mail->addAddress("".$key."");
                
                $mail->Subject = "Event Invitation!";
               
                $mail->isHTML(true);
                $mail->Body = '<p> Invitation from '.$_SESSION['first_name'].'</p><p> Title : '.$_POST['edit_title'].'  </p><p> Location : '.$_POST['location'].'  </p> <p>Description : '.$_POST['description'].' </p><p>Event Details : <a href='.$visitor_calendar_url.'>Visit Event</a></p>';
                $mail->send();

                 $mail->clearAddresses();
        }
    }
}

if(isset($_GET["delete_id"]))
{
 
 $query = "
 DELETE from events WHERE id=:id
 ";
 $statement = $pdo3->prepare($query);
 $statement->execute(
  array(
   ':id' => $_GET['delete_id']
  )
 ); 

 $query = "
 DELETE from event_attachments WHERE event_id=:id
 ";
 $statement = $pdo3->prepare($query);
 $statement->execute(
  array(
   ':id' => $_GET['delete_id']
  )
 );

 $query = "
 DELETE from events_members WHERE event_id=:id
 ";
 $statement = $pdo3->prepare($query);
 $statement->execute(
  array(
   ':id' => $_GET['delete_id']
  )
 ); 


 $query = "
 DELETE from events_members WHERE event_id=:id
 ";
 $statement = $pdo3->prepare($query);
 $statement->execute(
  array(
   ':id' => $_GET['delete_id']
  )
 ); 

 $query = "
 DELETE from events_usergroups WHERE event_id=:id
 ";
 $statement = $pdo3->prepare($query);
 $statement->execute(
  array(
   ':id' => $_GET['delete_id']
  )
 );

 $query = "
 DELETE from event_notification_time WHERE en_event_id=:id
 ";
 $statement = $pdo3->prepare($query);
 $statement->execute(
  array(
   ':id' => $_GET['delete_id']
  )
 );
}

header("Location:".$_SERVER['HTTP_REFERER']."");
die;

?>