<html>
<head>
<meta http-equiv="x-ua-compatible" content="IE=10">
<link rel='stylesheet' type='text/css' media='screen' href='css/default.css' />
<link rel='stylesheet' type='text/css' media='print' href='css/print.css' />
<style>
input#focus {
	border: 0;
  	box-shadow: 0 0 0;
  	color : transparent;
  	outline: 0;
}
</style>

<!-- After 5 minutes check if there are any new entries in main database and fetch -->
<meta http-equiv='refresh' content="500;">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8">
<meta http-equiv="X-UA-Compatible" content="IE=8">
<script language="javascript" src="scripts/pnguin_timeclock.js"></script>
<script type="text/javascript"> 
var _app = navigator.appName;
var rtvregval = -1;
var gtftyp;

if (_app == "Netscape"){
    alert("This Application should be run on Microsoft Internet Explorer only !!!");
}else if(_app=="Microsoft Internet Explorer"){
    //Continue
}

function endVeri(){
	var chkvstat = document.getElementById("authstat").value;
	//alert ("My value is " +chkvstat);
	if(chkvstat == 1){
		//alert("You have been SUCCESSFULY VERIFIED !");
		//window.location = "post_auth.php";
	}else if(chkvstat == 0){
		//alert ("Use CORRECT finger print for purpose of verification or Log in with your TRUE identity if this is not YOU !");
		sayUserNOTFound();
	}
    return chkvstat;
}

function getIDVal(aval){
    var arrayval = aval;
    alert("value from vbscript is " + arrayval);
    var arraystring;
    arraystring = document.getElementById("dbhexstr"+arrayval).value;
    return arraystring;
}

function getFPType(swara){
    var ftyp = swara;
    gtftyp = ftyp;
}

function getREGID(ridval){
    var regftyp = gtftyp;
    var regval = ridval;
    var regid = document.getElementById("dbreg_id"+regval).value;
    rtvregval = regid;
    var vidstart = document.getElementById("authstat").value;
    var empno = document.getElementById("dbfpempid"+regval).value;
	var empfname = document.getElementById("dbfpempfname"+regval).value;
	var empsname = document.getElementById("dbfpempsname"+regval).value;
    var fp1no = document.getElementById("dbfpno1"+regval).value;
    var fp2no = document.getElementById("dbfpno2"+regval).value;
    
    if (regftyp == 1){
        var fpno = fp1no;
        alert("fingerprint 1");
    }
    if (regftyp == 2){
        var fpno = fp2no;
        alert("fingerprint 2");
    }
	if(vidstart == 1){
	   // alert("SUCCESSFULY VERIFIED 1 !");
	   window.location = "mini-profile.php?finger=yes&user_id="+empno; //MUITHI | STATUS IN/OUT NOT NEEDED | 29-JAN-2014 | 10-SEPT-2015 | 04-JULY-2016
	}
	
    return regid;
}

function sayUserNOTFound(){
	window.location = "index.php";
}

function getContraBio(xbioval){
    var xval = xbioval;
    var payloadval = document.getElementById("cntstaff").value;
    if(xval == payloadval){
        alert("Invalid / UnAuthorised Staff. Access DENIED !!!");
        alert("Use Correct Finger for Identification or contact Administrator for Registration !!!");        
    }else{
        //Do Nothing: Relevant part of code already executed. Application should not find itself here!!!
        //alert("Do Nothing: Relevant part of code already executed. Application should not find itself here !!!");
        //AUDIT for this incidence
    }
}


</script>
</head>
<body>
<a href="main.php" style="border: 1px solid white !important; color: white !important;"><img src="images/logo.png" style="margin-left: 50px;" /></a><br /><br />
<center>

<?php

session_start();

$domain = $_SESSION['domain'];

include "_club/_$domain/db.php";


$load_veri = '<object id="DPFPVerControl" classid="clsid:F4AD5526-3497-4B8C-873A-A108EA777493"></object>';

echo "        <tr><td height=4 align=left valign=middle class=misc_items bgcolor='#F0F0F0'  align='center' style='border:1px solid #999999;'>\n".$load_veri."\n</td></tr>\n";




    $query = "select reg_id, empfname, empno, empsname, f_no1 as fp1_id, f_no2 as fp2_id, fptemplate1 as fp_data1, fptemplate2 as fp_data2 from employees"; //MUITHI | 11-SEPT-2015
    $emp_name_result = mysqli_query($con, $query);
    
	$staff_count = 0;
    while ($row = mysqli_fetch_assoc($emp_name_result)) {

        $fpdata1[] = $row['fp_data1'];
		$fpdata2[] = $row['fp_data2'];
		$fpno1[] = $row['fp1_id'];
		$fpno2[] = $row['fp2_id'];
		$fpempno[] = $row['empno'];
		$fpreg_id[] = $row['reg_id'];
		$fpempfname[] = $row['empfname'];
		$fpempsname[] = $row['empsname'];
		
		$staff_count++;

    }
    
	
	
if($staff_count >= 1){

	for($i=0; $i<sizeof($fpempno); $i++){
        $k = $i+1;
	}

    echo "        <tr><td height=7>";
	$fpno1_count = 1;
	foreach($fpno1 as $fpno1_val){
		echo '<input type="hidden" name="dbfpno1[]" id="dbfpno1'.$fpno1_count.'" value="'.$fpno1_val.'">';
		$fpno1_count++;
	}
	$fpno2_count = 1;
	foreach($fpno2 as $fpno2_val){
		echo '<input type="hidden" name="dbfpno2[]" id="dbfpno2'.$fpno2_count.'" value="'.$fpno2_val.'">';
		$fpno2_count++;
	}
	$fpdata1_count = 1;
	foreach($fpdata1 as $fpdata1_val){
		echo '<input type="hidden" name="dbhexstr1[]" id="dbhexstr1'.$fpdata1_count.'" value="'.$fpdata1_val.'">';
		$fpdata1_count++;
	}
	$fpdata2_count = 1;
	foreach($fpdata2 as $fpdata2_val){
		echo '<input type="hidden" name="dbhexstr2[]" id="dbhexstr2'.$fpdata2_count.'" value="'.$fpdata2_val.'">';
		$fpdata2_count++;
	}
	$fpreg_id_count = 1;
	foreach($fpreg_id as $fpreg_id_val){
		echo '<input type="hidden" name="dbreg_id[]" id="dbreg_id'.$fpreg_id_count.'" value="'.$fpreg_id_val.'">';
		$fpreg_id_count++;
	}
	$fpempid_count = 1;
	foreach($fpempno as $fpempid_val){
		echo '<input type="hidden" name="dbfpempid[]" id="dbfpempid'.$fpempid_count.'" value="'.$fpempid_val.'">';
		$fpempid_count++;
	}
	
	$fpempfname_count = 1;
	foreach($fpempfname as $fpempid_val){
		echo '<input type="hidden" name="dbfpempfname[]" id="dbfpempfname'.$fpempfname_count.'" value="'.$fpempid_val.'">';
		$fpempfname_count++;
	}
	
	$fpempsname_count = 1;
	foreach($fpempsname as $fpempid_val){
		echo '<input type="hidden" name="dbfpempsname[]" id="dbfpempsname'.$fpempsname_count.'" value="'.$fpempid_val.'">';
		$fpempsname_count++;
	}
	
	
	echo '<input type="hidden" name="cntstaff" id="cntstaff" class="cntstaff" value="'.sizeof($fpempno).'">';
	echo '<input type="hidden" name="authstat" id="authstat" class="authstat" value="-1" onchange="endVeri()">';
	echo "</td></tr>\n";


}	

?>

    </td>
  </tr>
</table>
<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
<img src="images/scannow.jpg" />

</center>

</body>
<script type='text/vbscript'>
'Format a byte array to a hex string to be sent to the server.
Function OctetToHexStr(ByVal arrbytOctet)
    Dim k
    For k = 1 To Lenb(arrbytOctet)
        OctetToHexStr = OctetToHexStr _
          & Right("0" & Hex(Ascb(Midb(arrbytOctet, k, 1))), 2)
    Next
End Function

Function encodeBase64(bytes)
	dim DM,EL
	Set DM = CreateObject("Microsoft.XMLDOM")
	'Create temporary node with Base64 data type
	Set EL = DM.CreateElement("tmp")
	EL.DataType = "bin.base64"
	'Set bytes,get encoded String
	EL.NodeTypedValue = bytes
	encodeBase64 = EL.Text
End Function

sub DPFPEnrollment_OnEnroll(finger, template, status)
    Dim fpe1
    Dim fpe2
    fpe1 = document.getElementById("f_no1").value
    fpe2 = document.getElementById("f_no2").value
    
    if fpe1 = "" Or fpe1 = null then
    	MsgBox "Finger print 1 successfully captured  "
    	document.getElementById("fptemplate1").value = encodeBase64(template.Serialize())
    	document.getElementById("f_no1").value = finger
    else
    	MsgBox "Finger print 2 successfully captured  "
    	document.getElementById("fptemplate2").value = encodeBase64(template.Serialize())
    	document.getElementById("f_no2").value = finger
    end if 
end sub

sub DPFPEnrollment_OnDelete(delfp,status)
    Dim getfpd
    getfpd = delfp
    truncateFP(getfpd)
end sub

sub DPFPVerControl_OnComplete(pFtrs,pStatus)
	Dim a
	Dim arraysize
	Dim fpdata12
	Dim dbHexdata1
	Dim dbHexdata2
	Dim tellme
	Dim valdeny
	valdeny = -5
	arraysize = document.getElementById("cntstaff").value
	
	For a = 1 To arraysize
		'NB:CAUTION --> I JOSEPH WILL ALWAYS REM TO START THE LOOP FOR THIS ARRAY FROM 1. REASONS WELL KNOWN TO ME!
		dbHexdata1 = document.getElementByID("dbhexstr1"&a).value
		dbHexdata2 = document.getElementByID("dbhexstr2"&a).value
		Dim templ1 : Set templ1 = CreateObject("DPFPShrX.DPFPTemplate")
		Dim templ2 : Set templ2 = CreateObject("DPFPShrX.DPFPTemplate")
		templ1.Deserialize(decodeBase64(dbHexdata1))
		templ2.Deserialize(decodeBase64(dbHexdata2))
		Dim give1 : Set give1 = CreateObject("DPFPEngx.DPFPVerificationResult")
		Dim give2 : Set give2 = CreateObject("DPFPEngx.DPFPVerificationResult")
		Dim Ver1 : Set Ver1 = CreateObject("DPFPEngx.DPFPVerification")
		Dim Ver2 : Set Ver2 = CreateObject("DPFPEngx.DPFPVerification")
		set give1 = Ver1.Verify(pFtrs,templ1)
		set give2 = Ver2.Verify(pFtrs,templ2)
		If give1.Verified = True Then
			document.getElementById("authstat").value = 1
			// MsgBox("You have successfully clocked in FP 1!")'--INITIAL SUCCESS MSG--
			getREGID(a)
			Exit For
		ElseIf give2.Verified = True Then
			document.getElementById("authstat").value = 1
			// MsgBox("You have successfully clocked in FP 2!")'--INITIAL SUCCESS MSG--
			getREGID(a)
			Exit For
		Else
			document.getElementById("authstat").value = 0
			valdeny = a			
		End If
	Next
	getContraBio(valdeny)
	call endVeri()
	
end sub

Function decodeBase64(base64)
	dim DM,EL
	Set DM = CreateObject("Microsoft.XMLDOM")
	Set EL = DM.CreateElement("tmp")
	EL.DataType = "bin.base64"
	EL.Text = base64
	decodeBase64 = EL.NodeTypedValue
End Function

</script>
</html>
