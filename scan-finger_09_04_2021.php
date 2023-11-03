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
<script type="text/javascript" src="scripts/jquery-1.10.2.min.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
  $( function() {
   
	$("#tags").autocomplete({
    source: "backend-search.php",
	minlength:"1",
    select: function(event, ui) {
        event.preventDefault();
        $("#tags").val(ui.item.label);
        $("#select_scan").val(ui.item.value);
    },
    focus: function(event, ui) {
        event.preventDefault();
        $("#tags").val(ui.item.label);
    },
	formatNoMatches: function() {
        return '';
    },
    dropdownCssClass: 'select2-hidden'
});
  } );
  </script>
</head>
<body>
<a href="main.php" style="border: 1px solid white !important; color: white !important;"><img src="images/logo.png" style="margin-left: 50px;" /></a><br /><br />
<center>

<?php

session_start();
$base_path="https://ccsnube.com/ttt/fingerprint_scanner/";

include "db.php";

?>
	<div class="row">
			<div class="col-md-4">

			</div>
			<div class="col-md-4">
				<div class="form-group">
					<!-- <label for="user_name">Username</label>	 -->
					<input type="hidden" id='select_scan'>
					<!-- <select class="form-control" onchange="login_selectuser()" id='select_scan'>
						<option selected disabled="disabled"> -- Select Username -- </option>
							<?php				
								// $strSQL = "SELECT a.* FROM users AS a JOIN employees AS b ON a.user_id=b.empno";
								// $result = mysqli_query($con,$strSQL);
								
								// while($row = mysqli_fetch_array($result)){
									
								// 	$value = base64_encode($base_path."verification.php?user_id=".$row['user_id']);
								
								// 	echo "<option value=$value id='option' user_id='".$row['user_id']."' user_name='".$row['first_name']."".$row['last_name']."'>".$row['first_name']."".$row['last_name']."</option>";
								// }				
							?>
					</select> -->
				</div>
				<div class="ui-widget">
  					<label for="tags">Username: </label>
 					 <input id="tags" placeholder="Search Username">
				</div>
				<br>
				<a onclick="login_selectuser()" id="button_login" type="submit" style="cursor: default;text-decoration: none;background-color: black;border-radius: 5px;padding: 8px;padding-left: 21px;color: #fff;border-color: blue;padding-right: 21px;">Login</a>
			</div>
			<div class="col-md-4">

			</div>
		</div>

   
<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
<img src="images/scannow.jpg" />

</center>
<script type="text/javascript">

			$('title').html('Login');
			
			function login_selectuser(device_name, sn) {
			
				$("#button_login").attr("href","finspot:FingerspotVer;"+$('#select_scan').val())
				
			}

		</script>
</body>

</html>
