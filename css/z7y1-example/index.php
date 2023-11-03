<?php
	
	require_once 'cOnnW88x.php';

	// Get the card ID
	if (isset($_POST['cardid'])) {
		
		$cardid = $_POST['cardid'];
		$type = 1;
		
		// Write to newscan
		$userDetails = "INSERT INTO newscan (chip, type) VALUES ('$cardid', $type)";
		try
		{
			$result = $pdo3->prepare("$userDetails")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		header("Location: index.php");

} else {
	
?>
<html>
 <head>
  <title>Acceso</title>
  <script src="jquery-1.10.2.min.js"></script>
 </head>  
 <body>
  <center>
   <div id="wrapper" >
    <div id="header">
    </div> <!-- end HEADER -->
    <div id='main'>
     <br /><br /><br /><br />
	 <form id="registerForm" action="" autocomplete="off" method="POST">
      <input type="text" name="cardid" id="focus" maxlength="10" autofocus value="" /><br /><br />
      <button name='oneClick' type="submit">Accept</button>
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

<?php }