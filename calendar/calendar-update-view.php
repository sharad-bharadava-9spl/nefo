<?php

require_once '../cOnfig/connection.php';

$user_id = '';
$view_type = 'dayGridMonth';
if(isset($_POST['user_id'])){
	$user_id = $_POST['user_id'];
	$view_type = $_POST['view'];
}
	$date = date("Y-m-d H:i:s");

    $query = "SELECT * from calendar_visits WHERE user_id = ".$user_id;
  
    try {
        $statement = $pdo3->prepare($query);
        $statement->execute();
        
    }
    catch (PDOException $e){
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
    }
    $resultCount = $statement->rowCount();

    if($resultCount == 0){

		   	try {
		        $insertData = "
		            INSERT INTO calendar_visits 
		            (user_id, view_type, last_visited_at) 
		            VALUES (:user_id, :view_type, :last_visited_at) ";
		            $statement = $pdo3->prepare($insertData);
		            $statement->execute(
		                array(
		                    ':user_id'          => $user_id,
		                    ':view_type'        => $view_type,
		                    ':last_visited_at'  => $date,
		                )
		            );
		    } catch (PDOException $e) {
		        $error = 'Error insert events table: ' . $e->getMessage();
		                echo $error;
		                exit();
		    }
    }else{

		 try {
		   $updateData = "
		    UPDATE calendar_visits 
		    SET view_type=:view_type, last_visited_at=:last_visited_at
		    WHERE user_id=:user_id
		    ";
		        $statement = $pdo3->prepare($updateData);
		        $statement->execute(
	                array(
	                    ':user_id'          => $user_id,
	                    ':view_type'        => $view_type,
	                    ':last_visited_at'  => $date,
	                )
		        );
		    }catch (PDOException $e){
					$error = 'Error update events: ' . $e->getMessage();
					echo $error;
					exit();
			}


    }
    
    ?>