<script type="text/javascript">

//VALIDATE REGN BEFORE POST | START | MUITHI | 09-AUGUST-2016
function valUSERREGN(){
    var fn = document.getElementById("funame").value;
    var sn = document.getElementById("suname").value;
    var cp = document.getElementById("empno").value;
    var f1 = document.getElementById("fu_no1").value;
    var f2 = document.getElementById("fu_no2").value;
    var t1 = document.getElementById("fputemplate1").value;
    var t2 = document.getElementById("fputemplate2").value;
    
    if(fn == "" || fn == null){
        document.jmu_userregnform.action = "";
        alert("Capture First Name !");
        document.jmu_userregnform.funame.style.background = '#FF99FF';
        document.jmu_userregnform.funame.focus();
        return false;
    }else if(sn == "" || sn == null){
        document.getElementById("jmu_userregnform").action = "";
        alert("Capture Second Name !");
        document.jmu_userregnform.suname.style.background = '#FF99FF';
        document.jmu_userregnform.suname.focus();
        return false;
    }else if(cp == "" || cp == null){
        document.getElementById("jmu_userregnform").action = "";
        alert("Capture User NO !");
        document.jmu_userregnform.empno.style.background = '#FF99FF';
        document.jmu_userregnform.empno.focus();//selcatg//seldept
        return false;
    }else if(t1 == "" || t1 == null){
        document.getElementById("jmu_userregnform").action = "";
        alert("Finger 1 NOT Captured !");
        document.jmu_userregnform.fputemplate1.style.background = '#FF99FF';
        return false;
    }else if(t2 == "" || t2 == null){
        document.getElementById("jmu_userregnform").action = "";
        alert("Finger 2 NOT Captured !");
        document.jmu_userregnform.fputemplate2.style.background = '#FF99FF';
        return false;
    }else if(f1 == "" || f1 == null){
        document.getElementById("jmu_userregnform").action = "";
        alert("Finger 1 Index NOT Captured !");
        document.jmu_userregnform.fu_no1.style.background = '#FF99FF';
        return false;
    }else if(f2 == "" || f2 == null){
        document.getElementById("jmu_userregnform").action = "";
        alert("Finger 2 Index NOT Captured !");
        document.jmu_userregnform.fu_no2.style.background = '#FF99FF';
        return false;
    }else{
        //location.reload();
        //document.getElementById("jmu_userregnform").action = "fp_processing.php";//jmu_fpuser_processing.php
		document.getElementById("jmu_userregnform").action = "jmu_fpuser_processing.php";
        return true;
    }
}
//VALIDATE REGN BEFORE POST | END

//REMOVE FP | START | MUITHI | 09-AUGUST-2016
function truncateUserFP(rmvfp){
    var fpd1;
    var fpd2;
    var getfpd;
    var getfpd;
    getfpd = rmvfp;
    fpd1 = document.getElementById("fu_no1").value;
    fpd2 = document.getElementById("fu_no2").value;
    var fpdelmsg = "Fingerprint Deleted";
    if(fpd1==getfpd){
        alert (fpdelmsg);
        document.getElementById("fputemplate1").value = "";
        document.getElementById("fu_no1").value = "";       
    }else if(fpd2==getfpd){
        alert(fpdelmsg);
        document.getElementById("fputemplate2").value = "";
        document.getElementById("fu_no2").value = "";
    }else{
        alert (" All Fingerprints have been removed !!");        
    }
}
//END REMOVE FP
</script>