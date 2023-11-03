<?php
    header("Cache-control: no-store, no-cache, must-revalidate");
    header("Expires: Mon, 26 Jun 1997 05:00:00 GMT");
    header("Pragma: no-cache");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
?>

<html>
<head>
<meta http-equiv="x-ua-compatible" content="IE=10">
<title>User Fingerprint | Registration</title>


<?php
session_start();

$domain = $_SESSION['domain'];

include "_club/_$domain/db.php";

$jomutech = "254724482764";
$start_key = "254724482764";
$user_agent = $_SERVER['HTTP_USER_AGENT'];
if(preg_match('/msie/', $user_agent)){
	//INTERNET EXPLORER BROWSER
	$load_enroll_user = '<img id="NotActive" name="NotActive" src="../img/NotActivated.png" />';
    $load_veri = '<img id="NotActive" name="NotActive" src="../img/NotActivatedVer.png" />';
}else{
    
	//NON-INTERNET EXPLORER BROWSER
	$load_enroll_user = '<object id="DPFPEnrollmentUserRegn" classid="clsid:0B4409EF-FD2B-4680-9519-D18C528B265E" >
	<PARAM NAME="MaxEnrollFingerCount" VALUE="2">
	</object>';
}
?>


<?php
    include("jmu_create_user.js");
?>


</head>
<body>

<?php

    $user_id = $_GET['user_id'];

    echo "<div id='jmu_user_registration'>";    
    $reg_print1 = "<h3>User Registration Panel</h3>";
    $reg_print1 .= "<form name='jmu_userregnform' id='jmu_userregnform' action='' method='post' onsubmit='return valUSERREGN();'>";
    $reg_print1 .= "<br />";		
    $reg_print1 .= "<table>
    <tr>
    <td></td>
    <td><input type='hidden' name='funame' id='funame' value='aaaa' size='30'></td>
    </tr>
    <tr>
    <td></td>
    <td><input type='hidden' name='suname' id='suname' value='bbbb' size='30'></td>
    </tr>
    <tr>
    <td></td>
    <td><input type='text' name='empno' id='empno' value='$user_id' size='30' readonly></td>
    </tr>";    
    echo $reg_print1;


    $reg_print3 = "<tr><td>
    <input type='hidden' name='fu_no1' id='fu_no1' size='30'>
    <input type='hidden' name='fputemplate1' id='fputemplate1' value='' size='30'>
    <input type='hidden' name='fu_no2' id='fu_no2' size='30'>
    <input type='hidden' name='fputemplate2' id='fputemplate2' value='' size='30'>
    
    </td>
    <td><input class='button' type='submit' name='btnsubmit' value='SUBMIT DETAILS' size='30'></td>
    </tr>
    <tr>
    <td colspan=2>";    
    $reg_print4 = "</td></tr></table></form><br />";
    //REGISTRATION START | JOMUTECH | 31-JULY-2016
    echo $reg_print3."\n".$load_enroll_user."\n".$reg_print4."\n";
    //REGISTRATION END | JOMUTECH | 31-JULY-2016
    ?>

</body>

<script type="text/vbscript">    
<?php
    include("jmu_create_user.vbs");
?>
</script>

<?php
    echo "</div>";
?>

</html>