<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	
	// "Scan card to continue"
	// Profile pops up, showing credit and expiry
	// Big buttons: Donar, Pagar cuota, Bar
	
	$_SESSION['lang'] = 'es';
	
?>

<html>
 <head>
  <title>Acceso</title>
  <script src="scripts/jquery-1.10.2.min.js"></script>
  <link href="css/styles11.css" rel="stylesheet" type="text/css" />
 </head>  
 <body id="userlookup" class="security">
  <center>
   <div id="wrapper" >
    <div id="header">
     <br /><br /><a href="index.php"><img src="images/logo.png" /></a>
    </div> <!-- end HEADER -->
    <div id='main'>
     <br /><br /><br /><br />
     <h2 style="font-size: 20px;">Aportar con TARJETA? Estas seguro?</h2>
     <br />
     <img src="images/llavero.png" />
	 <form onsubmit="oneClick.disabled = true; return true;" id="registerForm" action="" autocomplete="off" method="POST">
      <input type="text" name="cardid" id="focus" maxlength="10" autofocus value="" style="border: 0; outline: 0; box-shadow: 0 0 0 0; color: #ffffff" /><br /><br />
      <button name='oneClick' type="submit" style="display: none;" >Accept</button>
     </form>
    </div> <!-- END MAIN -->
   </div> <!-- END WRAPPER-->
 </center>
<script>
$(document).ready(function() {
    $("#focus").focus().bind('blur', function() {
        $(this).focus();
    }); 

    $("html").click(function() {
        $("#focus").val($("#focus").val()).focus();
    });

    //disable the tab key
    $(document).keydown(function(objEvent) {
        if (objEvent.keyCode == 9) {  //tab pressed
            objEvent.preventDefault(); // stops its action
       }
    })      
});
</script>
 </body>
</html>

<?php displayFooter(); ?>