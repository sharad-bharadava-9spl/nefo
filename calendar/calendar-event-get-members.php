<?php

require_once '../cOnfig/connection.php';


    $query = "SELECT users.*,users.user_id as uu_id,events_members.*,events_members.user_id as mem_id FROM users LEFT JOIN events_members ON events_members.user_id=users.user_id AND  events_members.event_id = ".$_POST['event_id']."   ORDER BY id ";
  
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
    <?php foreach ($result as $key) { if($key['mem_id']){?>
        <option value="<?php echo $key['uu_id'] ?>" <?php echo ($key['mem_id']) ? 'selected' : '' ?>><?php echo $key['first_name'].' '.$key['last_name'] ?> <?php echo !empty($key['ans']) ? '( '.$key['ans'].' )' : '(pending)' ?> </option>
    <?php } } ?>
           
    <?php
    
?>