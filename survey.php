<?php
//Created by Konstant for Task-14935056 on 15/09/2021
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
		
	// Authenticate & authorize
	authorizeUser($accessLevel);
	

	$validateScript = <<<EOD
    $(document).ready(function() {
    		$("#registerForm").validate({
    				ignore: "",
    				rules:{
    					hwd: {
    						required: true,
    					},
    					cws: {
    						required: true
    					},
    					sue: {
    						required: true
    					},
    					gs: {
    						required: true
    					}
    				},
    				messages: {
    					hwd: "Please submit your feedback!",
    					cws: "Please submit your feedback!",
    					sue: "Please submit your feedback!",
    					gs: "Please submit your feedback!",
    				},
    				errorPlacement: function(error, element){
    					if ( element.is(":radio") || element.is(":checkbox")){
							 error.appendTo(element.parent());
						} else {
							return true;
						}
    				}
    			})
		});

EOD;

if(isset($_GET['surveyid'])){
	$surveyid  = $_GET['surveyid'];
	$survey_arr = explode(",", $surveyid);
	$customer_id = $survey_arr[0];
	$user_id = $survey_arr[1];
}else{
	handleError("No survey id specified!","");
}

	// check if user id or customer id exist

	$checkCustomer = "SELECT id FROM customers WHERE id=".$customer_id;

	try{
		$cust_results = $pdo2->prepare("$checkCustomer");
		$cust_results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$cust_count = $cust_results->rowCount();

	if($cust_count == 0){
		handleError("Please enter valid customer id!","");
	}	

	$checkUser = "SELECT user_id FROM users WHERE user_id=".$user_id;

	try{
		$user_results = $pdo3->prepare("$checkUser");
		$user_results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$user_count = $user_results->rowCount();

	if($user_count == 0){
		handleError("Please enter valid user id!","");
	}


  	pageStart('Customer satisfaction survey', NULL, $validateScript, "pprofile", "admin", 'Customer satisfaction survey', $_SESSION['successMessage'], $_SESSION['errorMessage']);
  	
?>
<center>
	<div id="productoverview">
		<p>Thank you for your confidence in Cannabis Club Systems. <br><br>
			The satisfaction of our clients is fundamental to us, and as such we ask you to dedicate 3 minutes to answer the short questionnaire below. Your feedback helps us improve our products and services moving forward. 
			<br><br>
			<strong>Keep in mind that 5 is the highest score and 1 the lowest.</strong>
		</p>
	</div>
</center><br>
<center>
	<div class="actionbox-np2" id="main_form">
		 <div class='mainboxheader'>
		 	Hardware & Delivery
		 </div>
		<form id="registerForm" action="" method="POST" >
			<input type="hidden" name="customer_id" value="<?php echo $customer_id ?>">
			<input type="hidden" name="user_id" value="<?php echo $user_id ?>">
			<div class="boxcontent" style="padding-bottom: 0; text-align: left;">
		 		<span>HW & DELIVERY</span><br><br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5
				  <input type="radio" name="hwd"  value="5">
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4
				  <input type="radio" name="hwd"  value="4">
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3
				  <input type="radio" name="hwd"  value="3">
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2
				  <input type="radio" name="hwd"  value="2">
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1
				  <input type="radio" name="hwd"  value="1">
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				PLEASE COMMENT ON YOUR EXPERIENCE WITH OUR HARDWARE AND DELIVERY DEPARTMENT<br>

				<textarea class="defaultinput" name="hwd_comment" style="width:600px;"></textarea>
			        
			    </div>

			   <div class='mainboxheader'>
			 	Customer support
			  </div>
			  	<div class="boxcontent" style="padding-bottom: 0; text-align: left;">
			  	<span>CUSTOMER SERVICE</span><br><br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5
				  <input type="radio" name="cws"  value="5" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4
				  <input type="radio" name="cws"  value="4" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3
				  <input type="radio" name="cws"  value="3" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2
				  <input type="radio" name="cws"  value="2" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1
				  <input type="radio" name="cws"  value="1" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				PLEASE COMMENT ON YOUR EXPERIENCE WITH OUR CUSTOMER SERVICE DEPARTMENTT<br>

				<textarea class="defaultinput" name="cws_comment" style="width:600px;"></textarea>
			        
			    </div>
			    <div class='mainboxheader'>
			 	Software user experience
			  </div>
			  	<div class="boxcontent" style="padding-bottom: 0; text-align: left;">
			  	<span>SOFTWARE USER EXPERIENCE</span><br><br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5
				  <input type="radio" name="sue"  value="5" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4
				  <input type="radio" name="sue"  value="4" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3
				  <input type="radio" name="sue"  value="3" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2
				  <input type="radio" name="sue"  value="2" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1
				  <input type="radio" name="sue"  value="1" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				PLEASE COMMENT ON THE SOFTWARE USER EXPERIENCE:<br>

				<textarea class="defaultinput" name="sue_comment" style="width:600px;"></textarea>
			        
			    </div>
			   <div class='mainboxheader'>
			 	General Service
			  </div>
			  	<div class="boxcontent" style="padding-bottom: 0; text-align: left;">
			  	<span>General Service</span><br><br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5
				  <input type="radio" name="gs"  value="5" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4
				  <input type="radio" name="gs"  value="4" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3
				  <input type="radio" name="gs"  value="3" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2
				  <input type="radio" name="gs"  value="2" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				<div class="fakeboxholder">	
				 <label class="control">
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1
				  <input type="radio" name="gs"  value="1" >
				  <div class="fakebox"></div>
				 </label>
				</div>
				<br>
				<br>
				PLEASE COMMENT ON YOUR EXPERIENCE WITH OUR GENERAL SERVICE:<br>

				<textarea class="defaultinput" name="gs_comment" style="width:600px;"></textarea>
			        
			    </div>
			    <input type="hidden" name="submitted" value="1">
			    <button class="cta1" type="submit">Submit</button>
			    <img src="images/loading-small.gif" id="spinner" style="vertical-align:middle; height: 20px; display: none;">
			</div>
		</form>	

	<div class="actionbox-np2" id="thanks_msg" style="display: none;">
		<div class="boxcontent">
			<p><img src="images/checkmark-new.png"  style="vertical-align:middle;">  Thank you for taking the customer satisfaction survey!</p>
		</div>
	</div>		
	<div class="actionbox-np2" id="error_msg" style="display: none;">
		<div class="boxcontent">
			<p><img src="images/exclamation-20.png"  style="vertical-align:middle;">  There is some issue in submitting feedback, please try again!</p>
		</div>
	</div>	
</center>	


<?php displayFooter(); ?>
<script type="text/javascript">
	$(document).ready(function(){
		$("#registerForm").submit(function(event){
			event.preventDefault();
			if($("#registerForm").valid()){
				$("#spinner").show();
				var form_data = $('#registerForm').serializeArray();
				console.log(form_data);
				$.ajax({
					url: "survey-process.php",
					type: "POST",
					data: form_data,
					dataType: "json",
					success: function(results){
						if(results.success == "yes"){
							$("#main_form").hide();
							$("#spinner").hide();
							$("#thanks_msg").fadeIn(800);
						}else{
							$("#main_form").hide();
							$("#spinner").hide();
							$("#error_msg").fadeIn(800);
						}
					}

				});
			}
		})
	});
</script>