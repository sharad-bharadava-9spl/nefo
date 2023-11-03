<html>
<head>
<meta http-equiv="x-ua-compatible" content="IE=10">
<link rel='stylesheet' type='text/css' media='screen' href='css/default.css' />
<link rel='stylesheet' type='text/css' media='print' href='css/print.css' />
<link rel='stylesheet' type='text/css' href='fingerprint_scanner/assets/css/bootstrap.min.css' />


<style>
input#focus {
	border: 0;
  	box-shadow: 0 0 0;
  	color : transparent;
  	outline: 0;
}
#example_length,#example_filter{
	display:none;
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
  <link rel="stylesheet" href="//cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
  <script  src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script src="//cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
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
//$base_path="http://192.168.0.41/ccs/fingerprint_scanner/";

require_once "fingerprint_scanner/include/global.php";
$sql = "SELECT user_id,first_name,last_name FROM users,employees WHERE employees.empno = users.user_id and users.fptemplate1=1 order by first_name ASC";
$result=mysql_query($sql);
?>
<div style="width:60%;margin:auto;">
<?php foreach (range('A', 'Z') as $column){ ?>
<button value="<?php echo $column; ?>" class="srch btn btn-sm btn-primary"><?php echo $column; ?></button>
<?php } ?>
<button class="clear btn btn-sm btn-danger">Reset</button><br><br>
<table class="table table-bordered table-hover"  id="example">
<thead>
<tr>
<th>S.No.</th>
<th>Name</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php 

$count=1; while($row = mysql_fetch_array($result)){
	$salt = base64_encode($base_path."verification.php?user_id=".$row['user_id']);
echo "<tr><td>".$count++."</td><td>".$row['first_name']." ".$row['last_name']."</td><td><a href='finspot:FingerspotVer;".$salt."' class=\"btn btn-xs btn-primary\">Login</a></td></tr>";	
}
?>
 </tbody>
</table>
</div>



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
				<!-- <div class="ui-widget">
  					<label for="tags">Username: </label>
 					 <input id="tags">
				</div>
				<br>
				<a onclick="login_selectuser()" id="button_login" type="submit" class="cta4">Login</a> -->
			</div>
		</div>

   
<br />
<img src="images/scannow.jpg" />

</center>
<script type="text/javascript">


			$('title').html('Login');
			
			function login_selectuser(device_name, sn) {
			
				$("#button_login").attr("href","finspot:FingerspotVer;"+$('#select_scan').val())
				
			}
			//$(document).ready(function() {
    var table=$('#example').DataTable();
	
//} );
$('.srch').on('click', function () {
	
		  table.columns(1).search('^'+this.value,true,true).draw();
	  });
	  $('.clear').on('click', function () {
	
		  table.columns().search('').draw();
	  });

		</script>

</body>

</html>
