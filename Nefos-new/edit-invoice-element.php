<?php
	
	require_once 'cOnfig/connection.php';
	// require_once 'cOnfig/view.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['id'])) {
		$id = $_POST['id'];
		$element_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['element_name'])));
		$element_price = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['element_price'])));
		$other_option = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['other_option'])));
		$custom_option = 0;
		if(!empty($other_option) && $other_option != ''){
			$custom_option = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['custom_option'])));
		}
		// check duplicate tag
	  $checkElement = "SELECT element_en from invoice_elements where element_en = '$element_name'";  
		try
		{
			$chkResult = $pdo3->prepare("$checkElement");
			$chkResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	    $chkcount = $chkResult->rowCount();
	    /*if($chkcount>0){
			$_SESSION['errorMessage'] = "Element already exist !";
			header("Location: edit-invoice-element.php?id=".$id);
			exit();
		}*/
		// Query to update user - 28 arguments
		 $updateUser = "UPDATE invoice_elements SET element_en = '$element_name', element_price = '$element_price', custom_options = '$custom_option'  WHERE id = $id"; 
		try
		{
			$result = $pdo3->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}	

		// On success: redirect.
		$_SESSION['successMessage'] = "Element updated succesfully!";
		header("Location: invoice-elements.php");
		exit();
	}
	
	$validationScript = <<<EOD
    $(document).ready(function() {

	  $( "#datepicker" ).datepicker({
	  	   dateFormat: "yy-mm-dd"
	  	});	  
	  	$( "#deadline" ).datepicker({
	  	   dateFormat: "yy-mm-dd"
	  	});
    	    
	  $('#registerForm').validate({
		  rules: {
			  name: {
				  required: true
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			if (element.is("#savesig")){
				 error.appendTo("#errorBox1");
			} else if (element.is("#accept2")){
				 error.appendTo("#errorBox2");
			} else if (element.is("#accept3")){
				 error.appendTo("#errorBox3");
			} else if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
		 
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate


  }); // end ready
EOD;
	pageStart("Edit Element", NULL, $validationScript, "pprofile", NULL, "Edit Element", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$id = $_GET['id'];
	// Query to look up calls
	$selectUsers = "SELECT * FROM  invoice_elements WHERE id = $id";
	try
	{
		$results = $pdo3->prepare("$selectUsers");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $results->fetch();
		
		$name = $row['element_en'];
		$element_price = $row['element_price'];
		$custom_options = $row['custom_options'];

	


	?>
<center>
	<a href='invoice-section.php' class='cta1'>Invoice Section</a>
	<a href='invoice-elements.php' class='cta1'>Invoice Elements</a>
</center>
	<center>
		<form id="registerForm" action="" method="POST">
			<div id="mainbox-no-width">
				<div id="mainboxheader"> Add An Element </div>
				<div class='boxcontent'>
					<input type="hidden" name="id" value="<?php echo $id; ?>" />
					<table class="profileTable">
						<tr>
							<td><strong>Element Name</strong></td>
							<td>
								<input type="text" name="element_name" class="defaultinput" value="<?php echo $name; ?>" required=""> </td>
						</tr>
						<tr>
							<td><strong>Element Price</strong></td>
							<td>
								<input type="text" name="element_price" id="element_price" class="defaultinput" required="" value="<?php echo $element_price; ?>">
							</td>
						</tr>						
						<tr>
							<td><strong>Other Options</strong></td>
							<td>
								<div class="fakeboxholder customradio">
									<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="checkbox" name="other_option" value="1" <?php if($custom_options != 0){ echo "checked" ;} ?>>
										<div class="fakebox"></div>
									</label>
								</div>
							</td>
						</tr>						
						<tr id="other_items" <?php if($custom_options ==  0){ ?>  style="display: none;" <?php } ?>>
							<td>
								<div class="fakeboxholder customradio">
									<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="radio" name="custom_option" value="1" <?php if($custom_options ==1){ echo "checked"; } ?>>
										<div class="fakebox"></div>Allow Units
									</label>
								</div>
							</td>
							<td>
								<div class="fakeboxholder customradio">
									<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="radio" name="custom_option" value="2" <?php if($custom_options ==2){ echo "checked"; } ?> >
										<div class="fakebox"></div>Custom amount
									</label>
								</div>
							</td>
						</tr>	
					</table>
				</div>
			</div>
			<br>
			<button class='oneClick cta1' name='oneClick' type="submit">
				<?php echo $lang['global-savechanges']; ?>
			</button>
			</div>
		</form>
	</center>
<?php  displayFooter(); ?>
<script type="text/javascript">
	$(document).ready(function(){
		$("input[name='other_option']").change(function(){
			var this_val =$('input[name="other_option"]:checked').val();
			if(this_val == 1){
				$("#other_items").show();
			}else{
				$("#other_items").hide();
			}
		});
		$("input[name='custom_option']").change(function(){
			var this_option = $("input[name='custom_option']:checked").val();
			if(this_option == 2){
				$("#element_price").prop("required", false);
			}else{
				$("#element_price").prop("required", true);
			}
		});
	});
</script>