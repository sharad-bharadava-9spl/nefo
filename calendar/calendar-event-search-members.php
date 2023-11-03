<?php

// Begin code by sagar
// Search members in event 
require_once '../cOnfig/connection.php';
    $phrase = $_POST['search'];
    if (!empty($_POST['group_id'])) {
        $query = "SELECT * FROM users  WHERE userGroup  IN (".implode(',',$_POST['group_id']).") AND (first_name LIKE ('%$phrase%') OR last_name LIKE ('%$phrase%'))  ORDER BY user_id";
    }else if (!empty($_POST['search'])){
        $query = "SELECT * FROM users  WHERE  first_name LIKE ('%$phrase%') OR last_name LIKE ('%$phrase%')  ORDER BY user_id ";
    }else{
        $query = "SELECT * FROM users  ORDER BY user_id ";
    }
  
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
    $data = array();
    foreach ($result as $row ) {
        $data[] = array(
            'id'    => $row['user_id'],
            'name'  => $row['first_name'].' '.$row['last_name'],
        );
    }

    echo json_encode(array('items' =>$data));
// End code by sagar
?>