<?php

require_once '../cOnfig/connection.php';
require '../PHPMailerAutoload.php';

$mail = new PHPMailer(true);
$user_id = $_SESSION['user_id'];
if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
}
$maximum_files = $_POST['max_files'];
$data = array(
   $_POST['calendar_id']
);
$calendar_query = http_build_query(array('calendar' => $data));
$calender_url = $siteroot."calendar/calendar.php?user_id=".$user_id."&".$calendar_query;
$file = null;
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

    $fileNames = array_filter($_FILES['attachments']['name']);
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
/*if (isset($_FILES['attachments']['name'])) {
    // Save the attachments and store  in db
    $target_dir = "images/event_images/";
    $target_file = $target_dir . basename($_FILES["attachments"]["name"]);
	
	if (move_uploaded_file($_FILES['attachments']['tmp_name'], $target_file)) {
		$file = $_FILES['attachments']['name'];
    }
}*/
$usergroups = '';
if(!empty($_POST['usergroup_list'])){
    $usergroups = implode(",", $_POST['usergroup_list']);
}
if(isset($_POST["title"]))
{
    try {
        $query = "
            INSERT INTO events 
            (user_id,title, start_event, end_event,location,description,event_cale_id, usergroups, invite_email) 
            VALUES (:user_id,:title, :start_event, :end_event, :location, :description,:event_cale_id, :usergroups, :invite_email) ";
            $statement = $pdo3->prepare($query);
            $statement->execute(
                array(
                    ':user_id'      => $user_id,
                    ':title'        => $_POST['title'],
                    ':start_event'  => $_POST['start'],
                    ':end_event'    => $_POST['end'],
                    ':event_cale_id'=> $_POST['calendar_id'],
                    ':usergroups'   => $usergroups,
                    ':invite_email' => $_POST['invite_email'],
                    ':location'     => $_POST['location'],
                    ':description'  => $_POST['description'],
                )
            );
    } catch (PDOException $e) {
        $error = 'Error insert events table: ' . $e->getMessage();
                echo $error;
                exit();
    }
    
}
$last_id = $pdo3->lastInsertId();

    if($insertValuesSQL != ''){
            $insertValuesSQL = str_replace("event_id", $last_id, $insertValuesSQL);
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


if ($last_id) {

    if (!empty($_POST['notifi_mini'])) {
        foreach ($_POST['notifi_mini'] as $key ) {
            if (!empty($key)) {
                try{
                    $query = "
                    INSERT INTO event_notification_time 
                    (en_minutes,en_event_id,en_event_start_date,en_event_end_date) 
                    VALUES (:en_minutes,:en_event_id,:en_event_start_date,:en_event_end_date) ";
                    $member = $pdo3->prepare($query);
                    $member->execute(
                        array(
                            ':en_minutes'          => $key,
                            ':en_event_id'         => $last_id,
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
                    ':event_id' => $last_id,
                )
            );
        }
        catch (PDOException $e)
        {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
        }
        // Send user bell notification
/*        try{
            $query = "
            INSERT INTO notification_contact_update 
            (cust_id,notification_type,is_read,created_at,reject_reason) 
            VALUES (:cust_id,:notification_type,:is_read,:created_at,:reject_reason) ";
            $member = $pdo->prepare($query);
            $member->execute(
                array(
                    ':cust_id'           => $key,
                    ':notification_type' => '4',
                    ':is_read'           => '0',
                    ':created_at'        => date("Y-m-d H:i:s"),
                    ':reject_reason'     => 'Title : '.$_POST['title'].' , Location : '.$_POST['location'].',Date : '.$_POST['start'].'',
                )
            );
        }
        catch (PDOException $e)
        {
                $error = 'Error Insert user notification: ' . $e->getMessage();
                echo $error;
                exit();
        }*/
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
                    ':notification'      => $_POST['title'],
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


// send notifications to the usergroups
    foreach ($_POST['usergroup_list'] as $key ) {
        try{
            $query = "
            INSERT INTO events_usergroups 
            (usergroup,event_id) 
            VALUES (:usergroup,:event_id) ";
            $group = $pdo3->prepare($query);
            $group->execute(
                array(
                    ':usergroup'  => $key,
                    ':event_id' => $last_id,
                )
            );
        }
        catch (PDOException $e)
        {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
        }

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
                    ':notification'      => $_POST['title'],
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
    // Send user mail notification 
   /* if (!empty($_POST['users_list'])) {
        
        $query = "SELECT * FROM users WHERE user_id IN (".implode(',',$_POST['users_list']).")";
      
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

        foreach($result as $row)
        {
            $file_to_attach = 'images/event_images/';

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
			$mail->addAddress("".$row['email']."", "".$row['first_name']."".$row['last_name']."");
            $mail->Subject = "Event Invitation!";
            if (!empty($_FILES['attachments']['name'])) {
                $mail->AddAttachment( "".$file_to_attach.$_FILES['attachments']['name']."" );
            }
			$mail->isHTML(true);
			$mail->Body = '<p> Invitation from '.$_SESSION['first_name'].'</p><p> Title : '.$_POST['title'].'  </p><p> Location : '.$_POST['location'].'  </p> <p>Description : '.$_POST['description'].' </p>';
			$mail->send();
        }
    }
*/
    // Send another user inviter mail notification
    if (!empty($_POST['invite_email'])) {
        $addresses = explode(',', $_POST['invite_email']);
        if (!empty($addresses)) {
           // $file_to_attach = 'images/event_images/';

            
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
            foreach ($addresses as $key) {
                $mail->addAddress("".$key."");
            }
            $mail->Subject = "Event Invitation!";
           /* if (!empty($_FILES['attachments']['name'])) {
                $mail->AddAttachment( "".$file_to_attach.$_FILES['attachments']['name']."" );
            }*/
            $mail->isHTML(true);
            $mail->Body = '<p> Invitation from '.$_SESSION['first_name'].'</p><p> Title : '.$_POST['title'].'  </p><p> Location : '.$_POST['location'].'  </p> <p>Description : '.$_POST['description'].' </p>';
            $mail->send();
        }
    }

}

header("Location:".$_SERVER['HTTP_REFERER']."");
die;

?>