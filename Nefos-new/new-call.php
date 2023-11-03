<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);

	$validationScript = <<<EOD
    $(document).ready(function() {

	  	$( "#datepicker" ).datetimepicker({
	  			 dateFormat: "yy-mm-dd"
	  		});
	  	$( ".deadpicker" ).datetimepicker({
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
	// Query to look up users
	 $selectUsers = "SELECT number,shortName FROM customers order by id ASC"; 
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
	while($row = $results->fetch()){
		$customer_arr[$row['number']] = $row['shortName'];
	}
   //  query to look up issues
	$selectIssues = "SELECT id,issue FROM issues order by id ASC"; 
		try
		{
			$result_issue = $pdo3->prepare("$selectIssues");
			$result_issue->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	while($rowIssue = $result_issue->fetch()){
		$issue_arr[$rowIssue['id']] =  $rowIssue['issue'];
	}
	

	pageStart("New Call", NULL, $validationScript, "pprofile", NULL, "New Call", $_SESSION['successMessage'], $_SESSION['errorMessage']);


	// current_date

	$current_timestamp = date("Y-m-d H:i");



	// get departmets and categories

	 $getDepartment = "SELECT a.id AS department_id, b.id, a.name, b.category FROM departments a, department_cat b WHERE a.id = b.department_id";

		try
		{
			$results = $pdo3->prepare("$getDepartment");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
        $department_arr = [];
        $i = 0;
		while($depRow = $results->fetch()){
			$department_arr[$i]['cat_id'] = $depRow['id'];
			$department_arr[$i]['name'] =  $depRow['name'];
			$department_arr[$i]['department_id'] =  $depRow['department_id'];
			$department_arr[$i]['category'] = $depRow['category'];
			$department_names[$depRow['department_id']] = $depRow['name'];
			$i++;
		}

	$departments = array_unique($department_names);


  //  look up for users
	$selectUsers = "SELECT user_id, first_name, last_name FROM users order by first_name ASC";

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

		while($userRow = $results->fetch()){
			$user_arr[$userRow['user_id']] = $userRow['first_name']." ".$userRow['last_name'];
		}

	$query = "select max(number) from customers";
		try
		{
			$custom_result = $pdo3->prepare("$query");
			$custom_result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$cust_row = $custom_result->fetch();
		$nextMemberNo = $cust_row['0'] + 1;


		// select affiliate
		$selectAffiliate = "SELECT * from affiliations order by name ASC";
			try
			{
				$affiliate_result = $pdo3->prepare("$selectAffiliate");
				$affiliate_result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			
	if (isset($_GET['user_id'])) {
		
		$query = "SELECT shortName, number FROM customers WHERE id = '{$_GET['user_id']}'";
		try
		{
			$result = $pdo2->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$shortName = $row['shortName'];		
			$nextMemberNo = $row['number'];
				
	}


?>

   <form id="registerForm" action="call-process.php" method="POST">
    
 <div class="overview">
	<table class='profileTable' style='text-align: left; margin: 0;'>
		 <tr>
		  <td><strong>Timestamp</strong></td>
		  <td><input type="text" name="time_stamp" id="datepicker" value="<?php echo $current_timestamp; ?>" required /></td>
		 </tr>
		 <tr>
		 	<td></td>
		 	<td>
		 		<input type="radio" name="choose_call_type" value="0" checked>Club
		 		<input type="radio" name="choose_call_type" value="1">Affiliation
		 	</td>
		 </tr>
		 <tr>
		  <td><strong>Call type</strong></td>
		 	<td>
		 		<input type="radio" name="call_method" id="call_method1" value="1" required><label for="call_method1">Normal</label><br />
		 		<input type="radio" name="call_method" id="call_method2" value="2"><label for="call_method2">Skype</label><br />
		 		<input type="radio" name="call_method" id="call_method3" value="3"><label for="call_method3">Whatsapp</label><br />
		 		<input type="radio" name="call_method" id="call_method4" value="4"><label for="call_method4">Signal</label><br />
		 		<input type="radio" name="call_method" id="call_method5" value="5"><label for="call_method5">Telegram</label><br />
		 		<input type="radio" name="call_method" id="call_method6" value="6"><label for="call_method6">Wickr</label><br />
		 	</td>
		 </tr>
		 <tr>
		  <td><strong>Customer</strong></td>
		  <td>
		   <input type="text" name="customer_name" id="cust_name" value="<?php echo $shortName; ?>" placeholder="Customer Name" required />
		   <input type="text" name="customer_number" id="cust_num" class="fourDigit" value="<?php echo $nextMemberNo ?>" placeholder="Customer Number"  required/>
		  
		   <select name="affiliation_val" id="affiliation_drop" style="display: none;" required>
		   			<option value="">Select Affiliation</option>
		   	 	<?php   while($affiliate_val = $affiliate_result->fetch()){  ?>
		   				<option value="<?php echo $affiliate_val['id'] ?>"><?php echo $affiliate_val['name'] ?></option>
		   		<?php }?>
		   </select>
		  </td>
		 </tr>
		 <tr>
		  <td><strong>Customer Contact</strong></td>
		  <td>
		  	Add New Contact Details<input type="checkbox" name="check_contact" id="check_contact" value="1"><br>
		  	<select name="contact_name" id="select_contact" required="">
		  		<option value="">Select Contact Person</option>
<?php

	if (isset($_GET['user_id'])) {
		
		$getContacts = "SELECT * from contacts WHERE customer=".$nextMemberNo;
	
		try
		{
			$results = $pdo3->prepare("$getContacts");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		while($row = $results->fetch()){
			echo "<option value='".$row['id']."'>".$row['name']."</option>";
		}
		
	}
	
?>
		  	</select>
		  	<select name="contact_club" id="club_name" required="" style="display: none;">
		  		<option value="">Select Customer/club</option>
		  	</select>
		  	<input type="text" name="contact_name2" id="contact_new" style="display: none;" required="" placeholder="Contact Name">
		  	<input type="text" name="customer_contact" id="contact_num" value="" placeholder="Contact Number" readonly required="">
		  	<input type="email" name="customer_email" id="contact_email" value="" placeholder="Contact Email" readonly >
		  	<input type="text" name="contact_role" id="crole" style="display: none;" placeholder="Role">
		  </td>
		 </tr>
		</table>
		<br>
		<div class="issue_div">
			<table class='profileTable' style='text-align: left; margin: 0;'>
				<h4>Issue 1</h4> 
				<tr>
					<td>
					  <strong>Duration:</strong>
					 </td>
					 <td> 
					   <input type="number" name="timer[1]" id="clock1" class="twoDigit" readonly required="">(Seconds)
					   <div class='timer_btn' style="display: inline-flex;"><button id='stop_timer1' class="stop_btn">Stop</button>&nbsp;&nbsp;&nbsp;<button id='manual1' class="man_btn">Type Manually</button></div>
					   <input type="hidden" name="type_manual[1]" id='save_namual1' value="0">
					</td>					
				</tr>
			 <tr>
			  <td><strong>Issue</strong></td>
			  <td><input type="text" name="issue[1]" id="call_issue1" class="issue_text" placeholder="Enter Issue" required/></td>
			    <input type="hidden" name="issue_id[1]"  id="issue_id1">
			 </tr>
			 <tr>
			  <td><strong>Department</strong></td>
			  <td>
			  	<select name="department[1]" class="department_cls" id="dept_name1" required="">
			  	</select>
			  </td>
			 </tr>
			 <tr>
			  <td><strong>Department Category</strong></td>
			  <td>
			  	<select name="department_cat[1]" class="department_cat" id="dept_cat1" required="">
			  		<option value="">Select Category</option>
			  	</select>
			  	
			 </tr>
			 <tr>
			  <td><strong>Comment</strong></td>
			  <td><textarea name="comment[1]"></textarea></td>
			 </tr>
			 <tr>
			  <td><strong>Next Action</strong></td>
			  <td>
				  	<textarea name="next_desc[1]" placeholder="description"></textarea>
				  	<input type="text" name="deadline[1]" class="deadpicker" id="deadline1" placeholder="Deadline"><br>
				  	<input type="text" name="colleague[1]" id="userselect1"  class="multi_users" placeholder="select users">
				  	<input type="hidden" name="slected_users[1]" id="user_ids1">
				  	<select name="priority[1]">
				  		<option value="">Select Priority</option>
				  		<option value="low">Low</option>
				  		<option value="medium">Medium</option>
				  		<option value="high">High</option>
				  	</select>
			  </td>		  
			 </tr>		 
			 <tr>
			  <td><strong>Task Created ?</strong></td>
			  <td>
			  	<select name="task_created[1]" id="ctask1" class="task_change" required="">
			  		<option value="">Select Option</option>
			  		<option value="Yes">Yes</option>
			  		<option value="No">No</option>
			  	</select>
			  	<input type="text" name="task_url[1]"  id="task_url1" placeholder="Enter URL"  style="display: none;" required="">
			  </td>
			 </tr>
			</table>
		</div>
		<div id="issue_error" style="text-align: center;"></div>
		<button class="add_field_button cta">Add More Issues</button>
	 <br />
	<button class='oneClick' name='save_call' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</div>

<?php displayFooter(); ?>
<?php  $cust_num_arr = array_flip($customer_arr);


foreach($customer_arr as $cust_key => $cust_val){
	$customer_bind_arr[] = strval($cust_key)."--".$cust_val;
}


 ?>
 <script>

 	$("input[name='choose_call_type']").change(function(){
 		var call_type = $(this).val();
 		var check_contact = $("input[name='check_contact']:checked").val();
 		if(call_type == 0){
 			$("#affiliation_drop").val('').hide();
 			$("#cust_name").val('').show();
 			$("#cust_num").val('').show();
 			if(check_contact == 1){
 				$("#club_name").html("<option value=''>Select Customer/club</option>").hide();
 			}
 		}else{
 			$("#affiliation_drop").val('').show();
 			$("#cust_name").val('').hide();
 			$("#cust_num").val('').hide();
 			if(check_contact == 1){
 				$("#club_name").html("<option value=''>Select Customer/club</option>").show();
 			}
 		}
 	});

  	var customer_arr = <?php echo json_encode($customer_arr); ?>;
  	var customer_bind_arr = <?php echo json_encode($customer_bind_arr); ?>;
  
  	var customer_name =[];
  	for(var i in customer_arr){
  		customer_name.push(customer_arr[i]);
  	}
   
  
    $( "#cust_name" ).autocomplete({
       source: customer_name,
      	autoFocus: true,
       select: function( event, ui ) {
       	  var selected_value = ui.item.value;
       	  Object.keys(customer_arr).forEach(function(k){
			    console.log(k + ' - ' + customer_arr[k]);
			    if(selected_value == customer_arr[k]){
			    	$("#cust_num").val(k);
			    	    $.ajax({
					      type:"post",
					      url:"getContact.php?cust_num="+k,
					      datatype:"text",
					      success:function(data)
					      {
							$("#select_contact").html(data);
					      }
					    });
			    }
			});
       	}
    });

    // get affilaite contact list
    $("#affiliation_drop").change(function(){
    	var this_drop = $(this).val();
    	var checked_val = $("input[name='check_contact']:checked").val();
    	if(checked_val  == 1){
    		if(this_drop != ''){
	    		 $.ajax({
			      type:"post",
			      url:"getAffiContact.php?aff_id="+this_drop+"&checked=new",
			      datatype:"text",
			      success:function(data)
			      {
					$("#club_name").html(data);
			      }
			    });
    		}else{
    			$("#club_name").html("<option vaue=''>Select customer/club</option>");
    		}
    	}else{
    		if(this_drop != ''){
	    		 $.ajax({
			      type:"post",
			      url:"getAffiContact.php?aff_id="+this_drop,
			      datatype:"text",
			      success:function(data)
			      {
					$("#select_contact").html(data);
			      }
			    });
    		}else{
    			$("#select_contact").html("<option vaue=''>Select contact</option>");
    		}
    	}
    	
		  
		
    });
    $( "#cust_num" ).autocomplete({
       	source: customer_bind_arr,
      	autoFocus: true,
       	select: function( event, ui ) {
       	  var selected_value = ui.item.value;
       	 
       	  var selected_val_arr = selected_value.split("--");
       	 
       	 
       	  Object.keys(customer_arr).forEach(function(k){
			   // console.log(k + ' - ' + customer_arr[k]);
			    if(selected_val_arr[1] == customer_arr[k]){
			    	$("#cust_name").val(customer_arr[k]);
			    	    $.ajax({
					      type:"post",
					      url:"getContact.php?cust_num="+k,
					      datatype:"text",
					      success:function(data)
					      {
							$("#select_contact").html(data);
					      }
					    });
			    }
			});

       	},
       	change: function( event, ui ) {
       	  var selected_value = ui.item.value;
       	 
       	  var selected_val_arr = selected_value.split("--");
       	 
       	 $("#cust_num").val(selected_val_arr[0]);
       	  Object.keys(customer_arr).forEach(function(k){
			   // console.log(k + ' - ' + customer_arr[k]);
			    if(selected_val_arr[1] == customer_arr[k]){
			    	$("#cust_name").val(customer_arr[k]);
			    	    $.ajax({
					      type:"post",
					      url:"getContact.php?cust_num="+k,
					      datatype:"text",
					      success:function(data)
					      {
							$("#select_contact").html(data);
					      }
					    });
			    }
			});

       	}
    });

    var issue_arr = <?php echo json_encode($issue_arr); ?>;
	   var issue_name =[];
	  	for(var i in issue_arr){
	  		issue_name.push(issue_arr[i]);
	  	}
	if(issue_arr != null){  	
	    $( ".issue_text" ).autocomplete({
	       source: issue_name,
	      	autoFocus: true,
	       select: function( event, ui ) {
	       	  var this_id = $(event.target).attr('id');
	       	  var thisIdval = this_id.split('call_issue');
	       	  var selected_value = ui.item.value;
	       	  Object.keys(issue_arr).forEach(function(k){
				    console.log(k + ' - ' + issue_arr[k]);
				    if(selected_value == issue_arr[k]){
				    	$("#issue_id"+thisIdval[1]).val(k);
				    }
				});
	       	},
	       	change: function( event, ui){
	       		var this_id = $(event.target).attr('id');
	       	    var thisIdval = this_id.split('call_issue');
	       		if(ui.item == null){
	       			$("#issue_id"+thisIdval[1]).val('');
	       		}
	       	 	
				
	       	}
	    });
	}
  // change options dynamically
  var departments = <?php echo json_encode($departments);   ?>;
  var deptOptions = "<option value=''>Select Department</option>";
	  Object.keys(departments).forEach(function(key) {
	    deptOptions += "<option value="+key+">"+departments[key]+"</option>";
	});
  $("#dept_name1").html(deptOptions);
  var department_arr = <?php echo json_encode($department_arr); ?>;
	$(document).on("change",".department_cls",function(){
		var options = "<option value=''>Select category</option>";
		var dept_id = $(this).attr('id');
		var dept_id_val = $(this).val();
		for(var i in department_arr){
			var deptID = department_arr[i].department_id;
			if(dept_id_val == deptID){
				options += "<option value="+department_arr[i].cat_id+">"+department_arr[i].category+"</option>";
			}
		}
		$(this).parent().parent().next().find('.department_cat').html(options);
	});

	 // user multiselect autocomplete  
	 var user_arr = <?php echo json_encode($user_arr)  ?>;
	
	 	 var user_name =[];
	  	for(var i in user_arr){
	  		user_name.push(user_arr[i]);
	  	}
	
	$( function() {
		
		function split( val ) {
			return val.split( /,\s*/ );
		}
		function extractLast( term ) {
			return split( term ).pop();
		}

		$( ".multi_users" )
			// don't navigate away from the field on tab when selecting an item
			.on( "keydown", function( event ) {
				if ( event.keyCode === $.ui.keyCode.TAB &&
						$( this ).autocomplete( "instance" ).menu.active ) {
					event.preventDefault();
				}
			})
			.autocomplete({
				minLength: 0,
				source: function( request, response ) {
					// delegate back to autocomplete, but extract the last term
					response( $.ui.autocomplete.filter(
						user_name, extractLast( request.term ) ) );
				},
				focus: function(event, ui) {
					// prevent value inserted on focus
					return false;
				},
				select: function( event, ui ) {
					var terms = split( this.value );
					// remove the current input
					terms.pop();
					// add the selected item
					terms.push( ui.item.value );
					// add placeholder to get the comma-and-space at the end
					terms.push( "" );
					this.value = terms.join( ", " );
				  var this_id = $(event.target).attr('id');
		       	  var thisIdval = this_id.split('userselect');
					var user_ids = [];	
					Object.keys(user_arr).forEach(function(k){
						for(var i in terms){
							if(terms[i] != '' && terms[i] == user_arr[k]){
								user_ids.push(k);
							}
						}
					});
					
					$("#user_ids"+thisIdval[1]).val(user_ids.join());
					return false;
				},
				change: function( event, ui ) {
					var terms = split( this.value );
					console.log(terms);
					// remove the current input
					terms.pop();
					
					// add placeholder to get the comma-and-space at the end
					terms.push( "" );
					this.value = terms.join( ", " );
					var this_id = $(event.target).attr('id');
			       	  var thisIdval = this_id.split('user_ids');
					
					var user_ids = [];	
					Object.keys(user_arr).forEach(function(k){
						for(var i in terms){
							if(terms[i] != '' && terms[i] == user_arr[k]){
								user_ids.push(k);
							}
						}
					});
					$("#user_ids"+thisIdval[1]).val(user_ids.join());
					return false;
				}
			});
	} );
	// get name and email ld of customer
	$("#select_contact").change(function(){
		var this_id = $(this).val();
		if(this_id != ''){
			$.ajax({
				type: "POST",
				url: "getContactDetails.php",
				data: {"id": this_id},
				datatype: 'json',
				success: function(result){
					$("#contact_num").val(result.contact_number).attr('readonly', false);
					$("#contact_email").val(result.contact_email).attr('readonly', false);
				}

			});
		}
	});

 $('#check_contact').on('click',function () {
        if ($(this).is(':checked')) {
           $("#select_contact").val('').hide();
           $("#contact_new").show();
           $("#crole").show();
           $("#contact_num").attr('readonly', false);
           $("#contact_email").attr('readonly', false);
           var selected_call = $("input[name='choose_call_type']:checked").val();
           if(selected_call == 1){
           	  $("#club_name").show();
           	  var affliate = $("#affiliation_drop").val();
           	  if(affliate != ''){
		            $.ajax({
				      type:"post",
				      url:"getAffiContact.php?aff_id="+affliate+"&checked=new",
				      datatype:"text",
				      success:function(data)
				      {
						$("#club_name").html(data);
				      }
				    });
	        	}else{
	        		$("#club_name").html("<option value=''>Select Customer/Club</option>");
	        	}
           }else{
           		$("#club_name").val('').hide();
           }
        } else {
          $("#select_contact").show();
           $("#contact_new").hide();
            $("#crole").hide();
            $("#club_name").hide();
            $("#contact_num").attr('readonly', true);
            $("#contact_email").attr('readonly', true);
        }
    });
  </script>
  <script type="text/javascript" src="scripts/timer.jquery.min.js"></script>
  <script type="text/javascript" src="scripts/call-form.js?t='<?php echo time() ?>'"></script>
