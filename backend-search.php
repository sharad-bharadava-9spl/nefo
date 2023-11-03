<?php
require_once "fingerprint_scanner/include/global.php";
/* Attempt MySQL server connection. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
$link = $conn;

// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$base_path="https://ccsnube.com/ttt/fingerprint_scanner/";
if(isset($_REQUEST["term"])){
    // Prepare a select statement
    $sql = "SELECT * FROM users,employees WHERE ((first_name LIKE  ?) or (last_name LIKE ?)) AND employees.empno = users.user_id";
   
    
    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "ss", $param_term,$param_term);
        
        // Set parameters
        $param_term = $_REQUEST["term"] . '%';
       
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
          
            $result = mysqli_stmt_get_result($stmt);
            $data=[];
            // Check number of rows in the result set
            if(mysqli_num_rows($result) > 0){
                // Fetch result rows as an associative array
                while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
                    $value = base64_encode($base_path."verification.php?user_id=".$row['user_id']);
                   $data[]=['label'=>$row['first_name']." ".$row['last_name'],'value'=>$value];
                }
               
                echo json_encode($data);
               
            } else{
                echo json_encode([]);
               // echo json_encode(['label'=>'No matches found','id'=>'']);
            }
        } else{
            echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
        }
    }
     
    // Close statement
    mysqli_stmt_close($stmt);
}
 
// close connection
mysqli_close($link);

die;
?>