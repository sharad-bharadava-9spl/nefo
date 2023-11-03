<?php

require_once '../cOnfig/connection.php';


    $query = "SELECT usergroups FROM events WHERE id=".$_POST['event_id'];
  
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

    $usergroups = [];
      

      if(!empty($result['usergroups']) && $result['usergroups'] != ''){
        $usergroups = explode(",", $result['usergroups']);
      }

   $query2 = "SELECT userGroup,groupName FROM usergroups";
  
    try {
        $statement2 = $pdo3->prepare($query2);
        $statement2->execute();
       // $result = $statement->fetch();
    }
    catch (PDOException $e){
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
    }

    while($result2 = $statement2->fetch()){
        $group_id = $result2['userGroup'];
                  ?>
         <option value="<?php echo $result2['userGroup'] ?>"  <?php if(in_array($group_id, $usergroups)){ echo 'selected';  } ?>><?php echo $result2['groupName']; ?></option>
<?php    }
 die;
 ?>
           
   