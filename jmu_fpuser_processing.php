<?php
session_start();

$domain = $_SESSION['domain'];

include "_club/_$domain/db.php";

//START | MUITHI | 11-SEPT-2015
//FIRST NAME AND SURNAME SHOULD YOU NEED THEM

$fname = mysqli_real_escape_string($con, $_POST['funame']);
$sname = mysqli_real_escape_string($con, $_POST['suname']);

//END | MUITHI | 11-SEPT-2015


$empno = mysqli_real_escape_string($con, $_POST['empno']);
$f_no1 = mysqli_real_escape_string($con, $_POST['fu_no1']);
$fptemplate1 = mysqli_real_escape_string($con, $_POST['fputemplate1']);
$f_no2 = mysqli_real_escape_string($con, $_POST['fu_no2']);
$fptemplate2 = mysqli_real_escape_string($con, $_POST['fputemplate2']);
$reg_names = $fname." ".$sname;


?>

<?php

//FP EXISTS CHECK
$qry_existsfp = "SELECT COUNT(*)count_chkfp FROM employees WHERE empno = '".$empno."'";  //MUITHI | 11-SEPT-2015
$chk_exists_stmtfp = mysqli_query($con, $qry_existsfp);
while($res_chkfp = mysqli_fetch_assoc($chk_exists_stmtfp)){
	$count_entryfp = $res_chkfp['count_chkfp'];
}

 
$ins_stmtfp = "INSERT INTO employees (empfname, empsname, empno, fptemplate1, fptemplate2, f_no1, f_no2) 
            VALUES ('$fname','$sname','$empno','$fptemplate1','$fptemplate2','$f_no1','$f_no2')"; 
			
//END | MUITHI | 11-SEPT-2015

            
if($count_entryfp == 0){
	mysqli_query($con, $ins_stmtfp); //09-AUG-2013
	
	$updateorig = "UPDATE users SET fptemplate1 = 'abc', fptemplate2 = 'abc', f_no1 = '0', f_no2 = '0' WHERE user_id = $empno";
	mysqli_query($con, $updateorig);
	
	//SUCCESS
    header("location:profile.php?user_id=$empno");  //MUITHI | 11-SEPT-2015
}else if($count_entryfp >= 1){
    
	//FAIL
    header("location:registration_fail.php");   //MUITHI | 11-SEPT-2015
}else{
    //DO A LOT OF NOTHING HERE
}
?>
