<?php

// Begin code by sagar
require_once '../cOnfig/connection.php';


    $query = "SELECT * FROM event_notification_time  WHERE en_event_id = ".$_POST['event_id']." ORDER BY en_id ";
  
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
    
    ?>
    <?php foreach ($result as $key) { ?>
        <div class="edit_remove_notificatino">
            <input type="number" value="<?php echo $key['en_minutes'] ?>" name="edit_notifi_mini[]" placeholder="Enter minutes" class="text calendar-input ui-widget-content ui-corner-all">
            <button type="button" class="remove_btn">X</button>
        </div>
    <?php  }
    
// End code by sagar
?>