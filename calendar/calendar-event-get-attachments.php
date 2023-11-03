 <?php
    require_once '../cOnfig/connection.php';

            $getAttachments = "SELECT file_name from event_attachments where event_id = ".$_POST["event_id"];
                try
                {
                    $attach_results = $pdo3->prepare("$getAttachments");
                    $attach_results->execute();
                }
                catch (PDOException $e)
                {
                        $error = 'Error fetching attachment: ' . $e->getMessage();
                        echo $error;
                        exit();
                }
                $attachCount = $attach_results->rowCount();
                if($attachCount > 0){
                    $x =0;
                    while($attachRow = $attach_results->fetch()){
                        $attach_no = $x+1;
                        $attach_arr[] = "<a href='".$main_site.$attachRow['file_name']."' target='_blank'>Attachment $attach_no</a>"; 
                        $x++;   
                    }
                   echo $attachments = implode("<br>", $attach_arr);
                }else{
                   echo $attachments = '';
                }