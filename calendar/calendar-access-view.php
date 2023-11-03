<?php
	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view-calendar.php';
	//require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	// Authenticate & authorize
	//authorizeUser($accessLevel);
	
	getSettings(); 
	$access_user = $_SESSION['user_id'];


	  if($_REQUEST['user_id'] == '' || empty($_REQUEST['calendar']) || $_REQUEST['access'] == '' || !isset($_SESSION['user_id'])){
        die("You are not authorised to access this page !");
    }
	
	pageStart('Calendar Access', NULL, null, "calender", NULL, 'Calendar Access', $_SESSION['successMessage'], $_SESSION['errorMessage']);

$calendar_arr = $_GET['calendar'];
$calendar_ids = implode(",", $_GET['calendar']);
   $query = "SELECT * from calendar where cale_id  IN (".$calendar_ids.") AND access_code ='".$_REQUEST['access']."' AND cale_user_id = ".$_REQUEST['user_id']; 

  try {
        $statement = $pdo3->prepare("$query");
        $statement->execute();
        $result = $statement->fetch();
    }
    catch (PDOException $e){
      $error = 'Error fetching user: ' . $e->getMessage();
      echo $error;
      exit();
  }

$eventCount = count($result);
 
if(empty($result)){
     echo "You are not authorised to access this page !";
    die();
}

// het usergroup of current user

 $checkUsergroup = "SELECT * from users where user_id =".$access_user; 

try{
	$user_stmt = $pdo3->prepare("$checkUsergroup");
	$user_stmt->execute();
}
catch(PDOException $e){
      $error = 'Error fetching user: ' . $e->getMessage();
      echo $error;
      exit();
  }
$user_row = $user_stmt->fetch();
	$access_usergroup = $user_row['userGroup'];

// check for event access level

  $checkAccess = "SELECT * from calendar_access WHERE calendar_id IN (".$calendar_ids.") AND user_id =".$_REQUEST['user_id']." AND (LOCATE('".$access_user."',member_id) OR usergroups = '".$access_usergroup."')";

  try {
        $acc_statement = $pdo3->prepare("$checkAccess");
        $acc_statement->execute();
    }
    catch (PDOException $e){
      $error = 'Error fetching user: ' . $e->getMessage();
      echo $error;
      exit();
  }
  $accessResult = $acc_statement->fetch();
 	$accessCount = $acc_statement->rowCount();

 	if($accessCount > 0){
 		$access_level = $accessResult['access_level'];

 	}else{
 		    echo "You are not authorised to access this page !";
    		die();
 	}



	$selectUsers = "SELECT * FROM users";
		
	try
	{
		$results = $pdo3->prepare("$selectUsers");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching users list : ' . $e->getMessage();
			echo $error;
			exit();
	}

	// get users for visibility access

	$selectVisiblityUsers = "SELECT * FROM users";
		
	try
	{
		$userresults = $pdo3->prepare("$selectVisiblityUsers");
		$userresults->execute();
		$fetchVisibleUsers = $userresults->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching users list : ' . $e->getMessage();
			echo $error;
			exit();
	}

	$selectgroups = "SELECT * FROM `usergroups`";
	try
	{
		$selectgroups = $pdo3->prepare("$selectgroups");
		$selectgroups->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching Groups: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$editselectgroups = "SELECT * FROM `usergroups`";
	try
	{
		$editselectgroups = $pdo3->prepare("$editselectgroups");
		$editselectgroups->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching Groups: ' . $e->getMessage();
			echo $error;
			exit();
	}
	//$user_id = $_SESSION['user_id'];
	if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	}
	$calendars = "SELECT * FROM `calendar` WHERE cale_user_id= ".$user_id." ";
	try
	{
		$calendars = $pdo3->prepare("$calendars");
		$calendars->execute();
		$calendar_list = $calendars->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching calendars: ' . $e->getMessage();
			echo $error;
			exit();
	}
  $maximum_files = 5; 


// get the calendar visits by current user

  $getCalendarVisits = "SELECT * from calendar_visits WHERE user_id =".$_SESSION['user_id'];

	try
	{
		$calendar_visits = $pdo3->prepare("$getCalendarVisits");
		$calendar_visits->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching calendars: ' . $e->getMessage();
			echo $error;
			exit();
	}

	 $calendarVisitCount = $calendar_visits->rowCount();
	 $calendarVisitRow = $calendar_visits->fetch();

	if(!empty($_GET['calendar'])){

			$calendar_ids = implode(",", $_GET['calendar']);

			if($calendarVisitCount == 0){

				  try {
			        $InsertCalendarVisit = "
			            INSERT INTO calendar_visits 
			            (user_id, calendar_ids, last_visited_at) 
			            VALUES (:user_id, :calendar_ids, :last_visited_at) ";

			            $statement = $pdo3->prepare($InsertCalendarVisit);
			            $statement->execute(
			                array(
			                    ':user_id'          => $_SESSION['user_id'],
			                    ':calendar_ids'     => $calendar_ids,
			                    ':last_visited_at'  => date("Y-m-d h:i:s")
			                )
			            );
			    } catch (PDOException $e) {
			        $error = 'Error insert events table: ' . $e->getMessage();
			                echo $error;
			                exit();
			    }
			}else{

				 $updateCalendarVisit = "
				    UPDATE calendar_visits 
				    SET calendar_ids=:calendar_ids, last_visited_at=:last_visited_at
				    WHERE user_id=:user_id
				    ";
				    try {
				        $statement = $pdo3->prepare($updateCalendarVisit);
				        $statement->execute(
				            array(
			                    ':user_id'          => $_SESSION['user_id'],
			                    ':calendar_ids'     => $calendar_ids,
			                    ':last_visited_at'  => date("Y-m-d H:i:s")
				            )
				        );
				    }catch (PDOException $e){
							$error = 'Error update events: ' . $e->getMessage();
							echo $error;
							exit();
					}


			}



	}

$calendar_id_arr = [];

if(!empty($_GET['calendar'])){
	
	$calendar_id_arr = $_GET['calendar'];	
}
else if($calendarVisitCount > 0){

 	$visted_id = $calendarVisitRow['calendar_ids'];

 	$calendar_id_arr = explode(",", $visted_id);

 }

 $view_type = $calendarVisitRow['view_type'];
 	
if($view_type == '' || empty($view_type)){
 	  $view_type = 'dayGridMonth';
 	}
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.css" rel="stylesheet" />
<link href="assets/email.multiple.css" rel="stylesheet" />
<div class="calendar_content">
	<div class="calendar_left">
	 <div class="caledar_list">
		<form action="" id="calendar_form">
			<?php  
			foreach ($calendar_list as $key ) {  
				$check = null;
				if (in_array($key['cale_id'],$calendar_id_arr)) {
					$check = 'checked';
				}

				// get calendar access levels

					$access_level = '';
			        $access_member_id = '';
			        $access_usergroups = '';
			       
			         $getCalendarAccess = "SELECT * from calendar_access where calendar_id=".$key['cale_id'];

			            try {
			                    $statement_dom = $pdo3->prepare($getCalendarAccess);
			                    $statement_dom->execute();
			                   // $result_dom = $statement->fetchAll();
			                }
			                catch (PDOException $e){
			                        $error = 'Error fetching user: ' . $e->getMessage();
			                        echo $error;
			                        exit();
			                }
			            $resultCount = $statement_dom->rowCount();
			            if($resultCount > 0){
			                $result_dom = $statement_dom->fetch();

			                    if($result_dom['access_level'] != ''){
			                        $access_level = $result_dom['access_level'];
			                    }
			                    if($result_dom['member_id'] != ''){
			                        $access_member_id = $result_dom['member_id'];
			                    }
			                    if($result_dom['usergroups'] != ''){
			                        $access_usergroups = $result_dom['usergroups'];
			                    }
			            }
				?>
				<div class="checkbox">
					<input type="checkbox" name="calendar[]" value="<?php echo $key['cale_id'] ?>" <?php echo $check ?> class="calendar_chkd" style="width:20px !important">
					<span class="edit_calendar" data-id="<?php echo $key['cale_id'] ?>" data-color="<?php echo $key['cale_color'] ?>" data-level = "<?php echo $access_level ?>" data-members = "<?php echo $access_member_id ?>" data-usergroup = "<?php echo $access_usergroups;  ?>"><?php echo $key['cale_name'] ?></span>
					<span style="height: 17px;width: 17px;border-radius: 50%; margin-bottom:-4px; display: inline-block; background-color:<?php echo $key['cale_color'] ?> "></span>
				</div>
			<?php } if(isset($_GET['user_id'])){?>
				<input type="hidden" name="user_id" value="<?php echo $_GET['user_id'] ?>">
				
			<?php } ?>
		</form>
	</div>
	<?php  if($access_level < 3){ ?>
		<div class="add_new_calendar">
			<button type="button" class="new_calendar_btn">New calendar</button>
			<div id="new_calendar_dialog" title="Add new calendar" style="display:none">
				<form method="post" action="calendar-add-new.php" id="addCalenderForm">
					<label >Enter Name : </label>
					<input type="text" name="name" placeholder="Enter name" class="text calendar-input ui-widget-content ui-corner-all"><br>
					<input type="hidden" name="user_id" value="<?php echo $_GET['user_id'] ?>">
					<label >Select color: </label>
					<input type="color" name="color" value="#ff0000">
					<br>
					<label>Add visibility to members/uergroups</label><br>
						<select name="choose_access">
							<option value="">Select Access Level</option>
							<option value="1">Admin</option>
							<option value="2">Editor</option>
							<option value="3">View only</option>
						</select>
						<br>
						<br>
						<label >Select Groups for visibility :</label><br>
						<select name="usergroup_list_visibility" id="select_user_groups_visible" class="chosen-select"> 
							<option value="">Select Usrgroups</option>
							<option value="1">Admin</option>
							<option value="2">Workers</option>
							<option value="3">Volunteers</option>
						</select>
						<br>
						<br>
						<label >Add users for visibolity:</label><br>
						<select id="add_user_select_visible"  name="users_list_visiblity[]" width="100%" multiple>
								<?php 
									foreach($fetchVisibleUsers as $visible_user){
											$visible_user_row = sprintf("<option value='%d'>%s - %s %s</option>",
																   $visible_user['user_id'], $visible_user['memberno'], $visible_user['first_name'], $visible_user['last_name']);
											  echo $visible_user_row;
									}
								?>
						</select>
						<br>
						<br>
					<button type="submit" class="btn btn-success">Save</button>
				</form>
			</div>
		</div>
		<div class="edit_calendar">
			<div id="edit_calendar_dialog" title="Update calendar details" style="display:none">
				<form method="post" action="calendar-edit.php" id="editCalenderForm">
					<label >Enter Name : </label>
					<input type="text" name="name" id="edit_cale_title" placeholder="Enter name" class="text calendar-input ui-widget-content ui-corner-all"><br>
					<input type="hidden" name="calendar_id" id="edit_cale_id" value="" required>
					<label >Select color: </label>
					<input type="color" name="color" id="edit_cale_color" value="#ff0000">
					<br>
					<label>Add visibility to members/uergroups</label><br>
					<select name="choose_access_edit" id="edit_access_level">
						<option value="">Select Access Level</option>
						<option value="1">Admin</option>
						<option value="2">Editor</option>
						<option value="3">View only</option>
					</select>
					<br>
					<br>
					<label >Select Groups for visibility :</label><br>
					<select name="edit_usergroup_list_visibility" id="edit_select_user_groups_visible" class="chosen-select"> 
						<option value="">Select Usrgroups</option>
						<option value="1">Admin</option>
						<option value="2">Workers</option>
						<option value="3">Volunteers</option>
					</select>
					<br>
					<br>
					<label >Add users for visibolity:</label><br>
					<select id="edit_user_select_visible"  name="edit_users_list_visiblity[]" width="100%" multiple>
							<?php 
								foreach($fetchVisibleUsers as $visible_user){
										$edit_visible_user_row = sprintf("<option value='%d'>%s - %s %s</option>",
															   $visible_user['user_id'], $visible_user['memberno'], $visible_user['first_name'], $visible_user['last_name']);
										  echo $edit_visible_user_row;
								}
							?>
					</select>
					<br>
					<br>
					<button type="submit" class="btn btn-success">Update</button>
					<?php if($access_level == 1){ ?>
						<a href="" id="calendar_delete_link" onclick="return confirm('Are your sure want to remove event ?')"><button type="button" class="btn btn-success">Delete</button></a>
					<?php }?>
				</form>
			</div>
		</div>
	<?php } ?>
	</div>
	<div class="calendar_right">
		<div id='calendar'></div>
		<div id="dialog" title="Add event" style="display:none" style="width: 359px !important;">
			<form method="post" action="calendar-event-add.php" enctype="multipart/form-data" id="addEventForm">
				<label >Title : </label>
				<input type="text" name="title"  placeholder="Enter title" class="text calendar-input ui-widget-content ui-corner-all" required=""><br>
				<label >Location : </label>
				<input type="text" name="location" placeholder="Enter location" class="text calendar-input ui-widget-content ui-corner-all"><br>
				<label >Attachments : </label>
				<input type="file" name="attachments[]" class="text calendar-input ui-widget-content ui-corner-all" multiple>
				<input type="hidden" name="max_files" value="<?php echo $maximum_files; ?>">
				<br>
				<span>(you can select maximum <?php echo $maximum_files; ?> files to upload)</span>
				<br>
				<br>
				<label >Description : </label>
				<textarea name="description" cols="32" rows="5"></textarea>
				<br><br>
				<input type="hidden" name="start" id="start">
				<input type="hidden" name="end" id="end">
				<input type="hidden" name="user_id" value="<?php echo $_GET['user_id'] ?>">
				<label>Repeat at:</label>
				<select name="recurring_time" id="add_select_recurr">
					<option value="">Select Interval</option>
					<option value="daily">Daily</option>
					<option value="weekly">Weekly</option>
					<option value="monthly">Monthly</option>
					<option value="quarterly">Quarterly</option>
					<option value="yearly">Annualy</option>
				</select>
				<div id="add_end_recurr_div" style="display: none;">
					<br>
					<br>
					<label>End Event:</label>
					<input type="text" name="add_end_recurr" id="add_end_recurring" class="text calendar-input ui-widget-content ui-corner-all" placeholder="Enter End Date" required="">
				</div>
				<br>
				<br>
				<label >Select Calendar :</label><br>
				<select name="calendar_id" required> 
						<option value="">Select Calendar</option>
					<?php foreach ($calendar_list as $key ) { ?>
						<option value="<?php echo $key['cale_id'] ?>"><?php echo $key['cale_name']?></option>
					<?php }?>
				</select>
				<br>
				<br>
				<label >Select Groups for invite :</label><br>
				<select name="usergroup_list[]" id="select_user_groups" multiple class="chosen-select"> 
					<?php 
						while ($group = $selectgroups->fetch()) {
								$usergroup_row = sprintf("<option value='%d'>%s %s</option>",
													   $group['userGroup'], $group['groupName'], $group['last_name']);
								  echo $usergroup_row;
						}
					?>
				</select>
				<input id="add_chkall" type="checkbox" >Select All
				<br>
				<br>
				<input id="add_members" type="checkbox"> Add Members to invite <br>
				<div id="members_options" style="display: none;">
					<label >Select users :</label><br>
					<select id="add_user_select2"  name="users_list[]" width="100%" multiple>
						<?php 
							while ($users = $results->fetch()) {
									$user_row = sprintf("<option value='%d'>%s %s</option>",
														   $users['user_id'], $users['first_name'], $users['last_name']);
									  echo $user_row;
							}
						?>
					</select>
				</div>
				<br>
				<br>
				<label >Invite from Email :</label><br>
				<div class="div_add_new_email">
					<div class="remove_email">
						<input type="email" name="invite_email[]"  placeholder="Enter Email" class="text calendar-input ui-widget-content ui-corner-all"> 
						<button type="button" class="remove_btn_email">X</button>
					</div>
				</div>
				 <button type="button" class="add_new_email">Add Email</button>
				<br>
				<br>
				<fieldset>
				<legend>Notification:</legend>
					<div class="div_add_new_notification">
						<div class="remove_notificatino">
							<input type="number" name="notifi_mini[]" placeholder="Enter minutes" class="text calendar-input ui-widget-content ui-corner-all">
							<button type="button" class="remove_btn">X</button>
						</div>
					</div>
					<button type="button" class="add_new_notification">Add notification</button>
				</fieldset>
				<br>
				<br>
				<button type="submit" class="btn btn-success">Save</button>
			</form>
		</div>
		<?php if($access_level < 3){ ?>
			<div id="edit_dialog" title="Edit event" style="display:none">
				<form method="post" action="calendar-event-update.php" enctype="multipart/form-data" id="editEventForm">
					<label for="Id">Title : </label>
					<input type="text" name="edit_title" id="title" placeholder="Enter title" class="text calendar-input ui-widget-content ui-corner-all" required=""><br>
					<label for="Id">Location : </label>
					<input type="text" name="location" id="edit_location" placeholder="Enter location" class="text calendar-input ui-widget-content ui-corner-all"><br>
					<label >Description : </label>
					<textarea name="description" id="description" cols="32" rows="5"></textarea><br>
					<label >Attachments: </label>
					<!-- <a href="" target="_blank" id="edit_atta" rel="noopener noreferrer">View</a><br> -->
					<div id="edit_atta"></div><br>
					<input type="radio" name="remove_attachments" >Remove Attachments
					<input type="file" name="attachments[]" class="text calendar-input ui-widget-content ui-corner-all" multiple><br>
					<input type="hidden" name="max_files" value="<?php echo $maximum_files; ?>">
					<br>
					<span>(you can select maximum <?php echo $maximum_files; ?> files to upload)</span>
					<input type="hidden" name="attachment_old_name" id="edit_attachment" >
					<input type="hidden" name="start" id="edit_start" >
					<input type="hidden" name="end" id="edit_end" >
					<input type="hidden" name="id" value="" id="edit_event_id" >
					<input type="hidden" name="edit_calendar_id" value="" id="edit_calendar_id" >
					<br>
					<br>
					<label>Repeat at:</label>
					<select name="recurring_time" id="edit_recurr">
						<option value="">Select Interval</option>
						<option value="daily">Daily</option>
						<option value="weekly">Weekly</option>
						<option value="monthly">Monthly</option>
						<option value="quarterly">Quarterly</option>
						<option value="yearly">Annualy</option>
					</select>
					<div id="edit_end_recurr_div" style="display: none;">
						<br>
						<br>
						<label>End Event:</label>
						<input type="text" name="edit_end_recurr" id="edit_end_recurring" class="text calendar-input ui-widget-content ui-corner-all" placeholder="Enter End Date" required="">
					</div>
					<br>
					<br>
					<label >Select Groups to invite :</label><br>
					<select name="usergroup_list[]" id="edit_select_user_groups" multiple class="chosen-select"> 
						
					</select>
					<input id="edit_chkall" type="checkbox" >Select All	
					
					<br>
					<br>
					<label >Select users to invite :</label><br>
					<select id="edit_user_select2"  name="users_list[]" width="100%" multiple>
					</select>
					<br>
					<br>
					<label >Invite from Email :</label><br>
					<div class="edit_div_add_new_email">
					</div>
					 <button type="button" class="edit_add_new_email">Add Email</button>
					<br>
					<br>
					<fieldset>
						<legend>Notification:</legend>
							<div class="edit_div_add_new_notification">
							</div>
							<button type="button" class="edit_add_new_notification">Add notification</button>
						</fieldset>
					<br>
					<br>
					<button type="submit" class="btn btn-success">Update</button>
				</form>
				<br>
				<a href="" id="view_event_url" target="_blank"><button type="button"  class="btn btn-success">View event Details</button></a>
				<a href="" id="edit_delete_url" onclick="return confirm('Are your sure want to remove event ?')"><button type="button" style="margin-left: 168px !important;" class="btn btn-success">Delete event</button></a>
			</div>	
		<?php }else{ ?>	
			<div id="edit_dialog" title="View event" style="display:none; height: 100px;">
				
				<a href="" id="view_event_url" target="_blank"><button type="button"  class="btn btn-success">View event Details</button></a>
				
			</div>
		<?php } ?>

		<div id="reply_dialog" title="Event" style="display:none">
			<form method="post" action="calendar-event-user-ans.php">
				<label for="Id">Title : </label>
				<input type="text" name="title" id="reply_title" readonly class="text calendar-input ui-widget-content ui-corner-all"><br>
				<label for="Id">Location : </label>
				<input type="text" id="reply_location" readonly class="text calendar-input ui-widget-content ui-corner-all"><br>
				<label >Description : </label>
				<textarea readonly id="reply_description" cols="32" rows="5"></textarea><br>
				<input type="hidden" name="reply_user" value="<?php echo $user_id ?>" >
				<input type="hidden" name="reply_event" id="reply_event_id" >
				<input type="hidden" name="reply_event_user_id" id="reply_event_user_id">
				<input type="hidden" name="reply_event_cal_id" id="reply_event_cal_id">
				<br>
				<label >Attachments: </label>
				<div id="reply_atta"></div>
				<br>
				<label >Are you Going/Attend? :</label><br>
				<select name="reply_ans" >
					<option value="yes">Yes</option>
					<option value="no">No</option>
					<option value="maybe">May be</option>
				</select>
				<br>
				<br>
				<button type="submit" class="btn btn-success">Save</button>
			</form>
		</div>
	</div>
</div>	 
<style>

   #calendar {
    max-width: 100%;
    margin: 0 auto;
    height: auto;
  }

  .calendar-input {
	width: 200px;
    height: 25px;
    margin-bottom: 10px;
  }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>
<script src="assets/jquery.email.multiple.js"></script>
<script>
var access_level = "<?php echo $access_level ?>";

$('#add_user_select_visible').select2({
    width: '100%',
    allowClear: false,
    placeholder: 'Select members',
    escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
});


// add end time in recurring event
$("#add_end_recurring, #edit_end_recurring").datepicker({
	dateFormat: 'yy-mm-dd'
});


// end event date text 
$("#add_select_recurr").change(function(){
	var select_val = $(this).val();

	if(select_val != '' && select_val != null){

		$("#add_end_recurr_div").show();

	}else{

		$("#add_end_recurr_div").hide();
		$("#add_end_recurring").val('');
	}
});


// edit event date text 
$("#edit_recurr").change(function(){
	var edit_select_val = $(this).val();

	if(edit_select_val != '' && edit_select_val != null){

		$("#edit_end_recurr_div").show();

	}else{

		$("#edit_end_recurr_div").hide();
		$("#edit_end_recurring").val('');
	}
});


// show members 
$("#add_members").click(function(){
	if($(this).prop("checked") == true){

		$("#members_options").show();
	}else{
		$('#add_user_select2').val(null).trigger('change');
		$("#members_options").hide();
	}
})


$(".chosen-select").select2({
	    width: '100%',
    allowClear: false,
    placeholder: 'Select usergroups',
    escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
   // minimumInputLength: 1,
   /* templateResult: formatRepo,
    templateSelection: formatRepoSelection*/
});

    $("#add_chkall").click(function(){
        if($("#add_chkall").is(':checked')){
            $("#select_user_groups > option").prop("selected", "selected");
            $("#select_user_groups").trigger("change");
        } else {
            $("#select_user_groups > option").removeAttr("selected");
            $("#select_user_groups").trigger("change");
        }
    });    

    $("#edit_chkall").click(function(){
        if($("#edit_chkall").is(':checked')){
            $("#edit_select_user_groups > option").prop("selected", "selected");
            $("#edit_select_user_groups").trigger("change");
        } else {
            $("#edit_select_user_groups > option").removeAttr("selected");
            $("#edit_select_user_groups").trigger("change");
        }
    });
// form validation

          /*   */
$("#addEventForm").validate({
	rules:{
		title: {
			required: true
		}
		
	},
	errorPlacement: function(error, element) {
			  
			if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		}
});

$("#editEventForm").validate({
	rules:{
		edit_title: {
			required: true
		}
		
	},
	errorPlacement: function(error, element) {
			  
			if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		}
});

$("#addCalenderForm").validate({
	rules:{
		name: {
			required: true
		}
	},
	errorPlacement: function(error, element) {
			  
			if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		}
});

$("#editCalenderForm").validate({
	rules:{
		name: {
			required: true
		}
	},
	errorPlacement: function(error, element) {
			  
			if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		}
});

var calendar_ids = <?php echo json_encode($calendar_id_arr);  ?>;
var reply_user_id =<?php echo $user_id ?>;
var calendar;
var event_url;
if(calendar_ids.length > 0){
 	event_url = 'calendar-event-load.php?user_id=<?php echo $_GET["user_id"] ?>&caledar_id=<?php echo serialize($calendar_id_arr) ?>&event_id=<?php echo $_GET['event_id'] ?>';
}else{
	event_url = null;
}
console.log(event_url);
  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
     calendar = new FullCalendar.Calendar(calendarEl, {
     default: true,
      plugins: [ 'interaction', 'dayGrid', 'timeGrid' ,'rrule'],
      header: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      height: ($( window ).height()-260),
      contentHeight: ($( window ).height()-260),
      defaultDate: new Date(),
      defaultView: "<?php echo $view_type; ?>",
      navLinks: true, // can click day/week names to navigate views
      selectable: true,
      selectMirror: true,
	  selectHelper:true,
	  editable: true,
      eventLimit: true, // allow "more" link when too many events
	  events:event_url,
	  datesRender: function( info ){
	 	var current_view = info.view.type;
	 	var current_date = moment(info.view.currentStart).format('YYYY-MM-DD HH:mm:ss');
	 	console.log(current_date);
	 	var current_user = "<?php echo $_SESSION['user_id'] ?>";
	 	$.ajax({
				url:"calendar-update-view.php",
				type:"POST",
				data:{user_id:current_user, view: current_view},
				success:function(res)
				{
					console.log(current_view);
				}
			});	

	  	//console.log(info.view.type);
	  },
	  windowResize: function(arg) {
	    	calendar.setOption('height', $(window).height()-260);
			calendar.setOption('contentHeight', $(window).height()-260);
			var cheight= $(window).height()-260;
			var ctheight= calendar.getOption('contentHeight');
			var ctwidth= $(window).width()- 320;
			var ratio = ctwidth / cheight;
			//calendar.setOption('aspectRatio', ratio.toFixed(2));
			//console.log(ratio.toFixed(2));
  		},
		select: function(arg) {
			//console.log(arg);
			if(access_level < 3){
				$("#dialog").dialog({
	                autoOpen: false,
					autoResize: true,
					width: 400,
					height: ($(window).height() - 150)
				});
			}
			var start = moment(arg.start).format('YYYY-MM-DD HH:mm:ss');
			var end = moment(arg.end).format('YYYY-MM-DD HH:mm:ss');
			$("#start").val(start);
			$("#end").val(end);
			$('#dialog').dialog('open').effect( "highlight", "slow" );
			calendar.unselect()
		},
	  	eventResize:function(info, delta, revertFunc){
			var re_start =  moment(info.event.start).format('YYYY-MM-DD HH:mm:ss');;
			var re_end =  moment(info.event.end).format('YYYY-MM-DD HH:mm:ss');;
			var title = info.event.title;
			var id = info.event.id;
			var location = info.event.extendedProps.location;
			var description = info.event.extendedProps.description;
			$.ajax({
				url:"calendar-event-update.php",
				type:"POST",
				data:{edit_title:title, start:re_start, end:re_end, id:id,location:location,description:description},
					success:function(){
						alert('Event Updated');
					}
			})
		},
		eventDrop:function(info){
			var re_start =  moment(info.event.start).format('YYYY-MM-DD HH:mm:ss');;
			var re_end =  moment(info.event.end).format('YYYY-MM-DD HH:mm:ss');;
			var title = info.event.title;
			var id = info.event.id;
			var location = info.event.extendedProps.location;
			var description = info.event.extendedProps.description;
			$.ajax({
				url:"calendar-event-update.php",
				type:"POST",
				data:{edit_title:title, start:re_start, end:re_end, id:id,location:location,description:description},
				success:function()
				{
					alert("Event Updated");
				}
			});
		},
		eventClick:function(info){
/*			console.log(info);
			if (info.event.extendedProps.cale_user_id != reply_user_id) {
				$("#reply_dialog").dialog({
					autoOpen: false,
					autoResize: true,
				});
				$('#reply_title').val(info.event.title);
				$('#reply_event_id').val(info.event.id);
				
				$('#reply_event_user_id').val(info.event.extendedProps.user_id);
				$('#reply_event_cal_id').val(info.event.extendedProps.event_cale_id);
				$('#reply_location').val(info.event.extendedProps.location);
				//$('#reply_atta').attr('href','images/event_images/'+info.event.extendedProps.attachments);
				$('#reply_description').val(info.event.extendedProps.description);
				$('#reply_dialog').dialog('open').effect( "highlight", "slow" );
				return false;
			}*/
			if(access_level <3){
				var dial_height = $(window).height() - 150;
			}else{
				var dial_height = 100;
			}
			$("#edit_dialog").dialog({
                autoOpen: false,
				autoResize: true,
				height: dial_height
			});
			$('#edit_user_select2').empty();
			
			$.ajax({
				url:"calendar-event-get-members.php",
				type:"POST",
				data:{event_id:info.event.id},
				success:function(res)
				{
					$('#edit_user_select2').append(res);
				}
			});		

			
			
			$.ajax({
				url:"calendar-event-get-attachments.php",
				type:"POST",
				data:{event_id:info.event.id},
				success:function(res)
				{
					$('#edit_atta').html(res);
					$('#reply_atta').html(res);
				}
			});		

			$("#edit_select_user_groups").empty();

			$.ajax({
				url:"calendar-event-get-groups.php",
				type:"POST",
				data:{event_id:info.event.id},
				success:function(res)
				{
					$('#edit_select_user_groups').append(res);
				}
			});
				
			$('.edit_div_add_new_notification').empty();
			$.ajax({
				url:"calendar-event-notification-time.php",
				type:"POST",
				data:{event_id:info.event.id},
				success:function(res)
				{	
					$('.edit_div_add_new_notification').html(res);
				}
			});

			$(".edit_div_add_new_email").empty();
			var invite_email_str = info.event.extendedProps.invite_email;
			if(invite_email_str != '' && invite_email_str != null){
				
				var invite_email_arr = invite_email_str.split(",");
				
				var output_email = '';
				for(var i= 0; i< invite_email_arr.length; i++){
					output_email += '<div class="edit_remove_email"><input type="email" value="'+invite_email_arr[i]+'" name="edit_invite_email[]" placeholder="Enter email" class="text calendar-input ui-widget-content ui-corner-all"><button type="button" class="remove_btn_email">X</button></div>';
				}
				$(".edit_div_add_new_email").html(output_email);
			}
			var re_start =  moment(info.event.start).format('YYYY-MM-DD HH:mm:ss');;
			var re_end =  moment(info.event.end).format('YYYY-MM-DD HH:mm:ss');;
			$('#title').val(info.event.title);
			$('#edit_event_id').val(info.event.id);
			$('#edit_calendar_id').val(info.event.extendedProps.event_cale_id);
			$('#edit_start').val(re_start);
			$('#edit_end').val(re_end);
			$('#edit_location').val(info.event.extendedProps.location);
			$('#description').val(info.event.extendedProps.description);
			$('#edit_recurr').val(info.event.extendedProps.recurring_time);
			if(info.event.extendedProps.recurring_time != '' && info.event.extendedProps.recurring_time != null){
				var re_end_recurr =  moment(info.event.extendedProps.end).format('YYYY-MM-DD');;
				$('#edit_end_recurr_div').show();
				$('#edit_end_recurring').val(re_end_recurr);
			}
			
			$('#view_event_url').attr('href', info.event.extendedProps.event_url);
			$('#edit_delete_url').attr('href','calendar-event-update.php?delete_id='+info.event.id);
			$('#edit_dialog').dialog('open').effect( "highlight", "slow" );
			


		},

    });

   
/*	$(window).resize(function(){
		calendar.setOption('height', $(window).height()-260);
		calendar.setOption('contentHeight', $(window).height()-260);
		var cheight= calendar.getOption('height');
		var ctheight= calendar.getOption('contentHeight');
		var ctwidth= $(window).width()- 320;
		var ratio = ctwidth / cheight;
		//calendar.setOption('aspectRatio', ratio.toFixed(2));
		console.log(cheight, ctheight, ctwidth, ratio.toFixed(2));
	});*/

    calendar.updateSize();
    calendar.render();
  });



/*  $('#select_user_groups').on('change', function (e) {
		$('#add_user_select2').empty();
  });
  $('#edit_select_user_groups').on('change', function (e) {
		$('#edit_user_select2').empty();
  });*/

  $('.new_calendar_btn').on('click', function () {
	$("#new_calendar_dialog").dialog({
                autoOpen: false,
				autoResize: true,
	});
	$('#new_calendar_dialog').dialog('open').effect( "highlight", "slow" );
  });


function getQueryString() {
	var result = {};
	if(!window.location.search.length) return result;
	var qs = window.location.search.slice(1);
	var parts = qs.split("&");
	for(var i=0, len=parts.length; i<len; i++) {
		var tokens = parts[i].split("=");
		result[tokens[0]] = decodeURIComponent(tokens[1]);
	}
	return result;
}

$(document).ready(function() {

	$('#edit_user_select_visible').select2({
	    width: '100%',
	    allowClear: false,
	    placeholder: 'Select members',
	    escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
	});

    $(".caledar_list input[type=checkbox]").click(function() {
		$("#calendar_form").submit(function(e) {
			//var that = this;
			var qs = getQueryString();
			for(var key in qs) {
				var field = $(document.createElement("input"));
				field.attr("name", key).attr("type","hidden");
				field.val(qs[key]);
				$(this).append(field);
			}
		});
		$('#calendar_form').submit();
    });



    $(".edit_calendar").click(function() {
		$("#edit_calendar_dialog").dialog({
                autoOpen: false,
				autoResize: true,
		});
		$('#edit_cale_title').val($(this).html());
		$('#edit_cale_color').val($(this).attr('data-color'));
		$('#edit_cale_id').val($(this).attr('data-id'));
		$('#calendar_delete_link').attr('href','calendar-edit.php?calendar_delete_id='+$(this).attr('data-id'));

		$('#edit_access_level').val($(this).data('level'));
			$('#edit_select_user_groups_visible').val($(this).data('usergroup')).trigger("change");
			var access_members = $(this).data('members');
			$('#edit_user_select_visible').val('').trigger("change");
			if(access_members != ''){
				if(access_members.length > 1){
					var access_members_arr = access_members.split(",");
				}else{
					var access_members_arr = access_members;
				} 
				$('#edit_user_select_visible').val(access_members_arr);
				$('#edit_user_select_visible').trigger('change');
			}
			

		$('#edit_calendar_dialog').dialog('open').effect( "highlight", "slow" );
    });

	$(".add_new_email").click(function() {
		$('.div_add_new_email').append('<div class="remove_email">'+
													'<input type="email" name="invite_email[]" placeholder="Enter Email" class="text calendar-input ui-widget-content ui-corner-all">'+
													'<button type="button" class="remove_btn_email">X</button>'+
												'</div>'
		);
    });

    $(".add_new_notification").click(function() {
		$('.div_add_new_notification').append('<div class="remove_notificatin">'+
													'<input type="number" name="notifi_mini[]" placeholder="Enter minutes" class="text calendar-input ui-widget-content ui-corner-all">'+
													'<button type="button" class="remove_btn">X</button>'+
												'</div>'
		);
    });

    $(".edit_add_new_notification").click(function() {
		$('.edit_div_add_new_notification').append('<div class="edit_remove_notificatin">'+
													'<input type="number" name="edit_notifi_mini[]" placeholder="Enter minutes" class="text calendar-input ui-widget-content ui-corner-all">'+
													'<button type="button" class="remove_btn">X</button>'+
												'</div>'
		);
    });    

    $(".edit_add_new_email").click(function() {
		$('.edit_div_add_new_email').append('<div class="edit_remove_email">'+
													'<input type="email" name="edit_invite_email[]" placeholder="Enter email" class="text calendar-input ui-widget-content ui-corner-all">'+
													'<button type="button" class="remove_btn_email">X</button>'+
												'</div>'
		);
    });
});

$(document).on("click",".remove_btn",function() {
	$(this).parent('div').remove();
});

$(document).on("click",".remove_btn_email",function() {
	$(this).parent('div').remove();
});

$('#edit_user_select2').select2({
    ajax: {
        url: 'calendar-event-search-members.php',
        dataType: 'json',
		method:'post',
        delay: 250,
        data: function (params) {
          return { search: params.term,group_id:$('#select_user_groups').val()};
        },
        processResults: function (data, params) {
            return {
                results: data.items,
            };
        },
        cache: true
    },
    width: '100%',
    allowClear: false,
    placeholder: 'Select members',
    escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
   // minimumInputLength: 1,
    templateResult: formatRepo,
    templateSelection: formatRepoSelection
});

$('#add_user_select2').select2({
  /*  ajax: {
        url: 'calendar-event-search-members.php',
        dataType: 'json',
		method:'post',
        delay: 250,
        data: function (params) {
          return { search: params.term,group_id:$('#select_user_groups').val()};
        },
        processResults: function (data, params) {
            return {
                results: data.items,
            };
        },
        cache: true
    },*/
    width: '100%',
    allowClear: false,
    placeholder: 'Select members',
    escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
   // minimumInputLength: 1,
    //templateResult: formatRepo,
    //templateSelection: formatRepoSelection
});

function formatRepo (repo) {
    if (repo.loading) {
    return repo.text;
    }
    
    if (repo.name == undefined) {
        var markup = "<div class='select2-result-repository clearfix'>" +
        "<div class='select2-result-repository__meta'>" +
        "<div class='select2-result-repository__title'>" + repo.text + "</div>";
        return markup;    
    }else{
        var markup = "<div class='select2-result-repository clearfix'>" +
        "<div class='select2-result-repository__meta'>" +
        "<div class='select2-result-repository__title'>" + repo.name + "</div>";
        return markup;
    }
}

function formatRepoSelection (repo) {
    return repo.name || repo.text;
}

</script>
<!-- Calendar code end -->
<!-- End code by sagar -->
<?php  displayFooter(); ?>