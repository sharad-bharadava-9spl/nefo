<?php
// Begin code by sagar
require_once '../cOnfig/connection.php';

if(isset($_POST["id"]))
{
    $query = "
    UPDATE events 
    SET start_event=:start_event, end_event=:end_event
    WHERE id=:id
    ";
    try {
        $statement = $pdo3->prepare($query);
        $statement->execute(
            array(
                ':start_event'   => $_POST['start'],
                ':end_event'     => $_POST['end'],
                ':id'            => $_POST['id']
            )
        );
    }catch (PDOException $e){
			$error = 'Error update events: ' . $e->getMessage();
			echo $error;
			exit();
	}
    
}
// End code by sagar
?>