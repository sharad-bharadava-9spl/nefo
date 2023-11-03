<?php

// Begin code by sagar
require_once '../cOnfig/connection.php';
require '../PHPMailerAutoload.php';
$todaysDate = date("Y-m-d");

    $domain = array('abuelitamaria','demo','irena');
    
    foreach ($domain as $key) {
        $db_name = 'ccs_'.$key;
        $db_user = 'root';
        $db_pwd = "";
        $host = "localhost";
        // Connect database
        try	{
            $pdo3 = new PDO('mysql:host='.$host.';dbname='.$db_name, $db_user, $db_pwd);
            $pdo3->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo3->exec('SET NAMES "utf8"');
        }
        catch (PDOException $e)	{
                $output = 'Unable to connect to the database server: ' . $e->getMessage();
                echo $output;
        }

        // GEt All notification  

        $notification = null;
        $query = "SELECT * FROM event_notification_time";
  
        try {
            $statement = $pdo3->prepare($query);
            $statement->execute();
            $notification = $statement->fetchAll();
        }
        catch (PDOException $e){
                $error = 'Error fetching event_notification_time: ' . $e->getMessage();
                echo $error;
                // exit();
        }

        // Get event for send notification 
        foreach ($notification as $key) {
            $date = new DateTime;
            $date->modify('+'.$key['en_minutes'].' minutes');
            $formatted_date = $date->format('Y-m-d H:i:s').'<br>';

            
            $query = " SELECT * FROM event_notification_time WHERE en_event_start_date < '".$formatted_date."' AND  en_read <> '1' ";

            try {
                $statement = $pdo3->prepare($query);
                $statement->execute();
                $event_list = $statement->fetchAll();
            }
            catch (PDOException $e){
                    $error = 'Error fetching event_notification_time: ' . $e->getMessage();
                    echo $error;
            }

            $event_id_list = array_column($event_list,'en_event_id');
            $noti_id = array_column($event_list,'en_id');

            if ($event_id_list) {
                $query = "  SELECT * FROM events_members 
                            LEFT JOIN users ON events_members.user_id = users.user_id
                            LEFT JOIN events ON events_members.event_id = events.id 
                            WHERE event_id IN (".implode(',',$event_id_list).")";
    
                try {
                    $statement = $pdo3->prepare($query);
                    $statement->execute();
                    $event_user_list = $statement->fetchAll();
                }
                catch (PDOException $e){
                        $error = 'Error fetching events_members AND user : ' . $e->getMessage();
                        echo $error;
                }
            }
            
            // Send notification (User)

            if ( $event_user_list ) {
                foreach ($event_user_list as $user) {
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
                    $mail->addAddress("".$user['email']."", "".$user['first_name']."".$user['last_name']."");
                    $mail->Subject = "Event reminder for ".$user['title']."";
                    $mail->isHTML(true);
                    $mail->Body = '<p>Hi '.$user['first_name'].'</p><p>Event '.$user['title'].' is starting in '.$key['en_minutes'].' minutes from now.</p><p> Title : '.$user['title'].'  </p><p> Location : '.$user['location'].'  </p> <p>Description : '.$user['description'].' </p>';
                    $mail->send();
                }
            }

            if ($noti_id) {
                $query = "
                        UPDATE event_notification_time 
                        SET en_read = 1 WHERE en_id IN (".implode(',',$noti_id).")";
                        try {
                            $statement = $pdo3->prepare($query);
                            $statement->execute();
                        }catch (PDOException $e){
                                $error = 'Error update event_notification_time: ' . $e->getMessage();
                                echo $error;
                        }
            }
        }

        echo "<pre>";
        print_r ($notificatin);
        echo "</pre>";
    }

    die('THE END');
// End code by sagar
?>