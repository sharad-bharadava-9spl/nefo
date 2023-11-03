<?php
	// Begin code by sagar
	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view-calendar.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings(); 
	
	pageStart('Event', NULL, null, "calender", NULL, $lang['index-membersC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

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
	$user_id = $_SESSION['user_id'];
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
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.css" rel="stylesheet" />
<link href="assets/email.multiple.css" rel="stylesheet" />

<div class="caledar_list">
	<form action="" id="calendar_form">
		<?php  
		foreach ($calendar_list as $key ) {  
			$check = null;
			if (in_array($key['cale_id'],$_GET['calendar'])) {
				$check = 'checked';
			}
			?>
			<input type="checkbox" name="calendar[]" value="<?php echo $key['cale_id'] ?>" <?php echo $check ?> class="calendar_chkd" style="width:20px !important"><span class="edit_calendar" data-id="<?php echo $key['cale_id'] ?>" data-color="<?php echo $key['cale_color'] ?>"><?php echo $key['cale_name'] ?></span><span style="height: 17px;width: 17px;border-radius: 50%;display: inline-block; background-color:<?php echo $key['cale_color'] ?> "></span>
		<?php } if(isset($_GET['user_id'])){?>
			<input type="hidden" name="user_id" value="<?php echo $_GET['user_id'] ?>">
		<?php } ?>
	</form>
</div>
<br>
<br>
<br>
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
			<button type="submit" class="btn btn-success">Save</button>
		</form>
	</div>
</div>
<div class="edit_calendar" >
	<div id="edit_calendar_dialog" title="Update calendar details" style="display:none">
		<form method="post" action="calendar-edit.php" id="editCalenderForm">
			<label >Enter Name : </label>
			<input type="text" name="name" id="edit_cale_title" placeholder="Enter name" class="text calendar-input ui-widget-content ui-corner-all"><br>
			<input type="hidden" name="calendar_id" id="edit_cale_id" value="" required>
			<label >Select color: </label>
			<input type="color" name="color" id="edit_cale_color" value="#ff0000">
			<br>
			<button type="submit" class="btn btn-success">Update</button>
			<a href="" id="calendar_delete_link" onclick="return confirm('Are your sure want to remove event ?')"><button type="button" class="btn btn-success">Delete</button></a>
		</form>
	</div>
</div>

<center>
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
			<input type="hidden" name="start" id="start">
			<input type="hidden" name="end" id="end">
			<input type="hidden" name="user_id" value="<?php echo $_GET['user_id'] ?>">
			<br>
			<br>
			<label >Select Calendar :</label><br>
			<select name="calendar_id" required> 
				<?php foreach ($calendar_list as $key ) { ?>
					<option value="<?php echo $key['cale_id'] ?>"><?php echo $key['cale_name']?></option>
				<?php }?>
			</select>
			<br>
			<br>
			<label >Select Groups :</label><br>
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
			<input id="add_members" type="checkbox"> Add Members <br>
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
			<input type="email" name="invite_email" id="invite_email" placeholder="Enter Email" class="text calendar-input ui-widget-content ui-corner-all">  <button type="button" class="add_email">Add Email</button>
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
			<br>
			<br>
			<label >Select Groups :</label><br>
			<select name="usergroup_list[]" id="edit_select_user_groups" multiple class="chosen-select"> 
				
			</select>
			<input id="edit_chkall" type="checkbox" >Select All	
			
			<br>
			<br>
			<label >Select users :</label><br>
			<select id="edit_user_select2"  name="users_list[]" width="100%" multiple>
			</select>
			<br>
			<br>
			<label >Invite from Email :</label><br>
			<input type="email" name="edit_invite_email" id="edit_invite_email" placeholder="Enter Email" class="text calendar-input ui-widget-content ui-corner-all"> <button type="button" class="add_email">Add Email</button>
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
		<a href="" id="edit_delete_url" onclick="return confirm('Are your sure want to remove event ?')"><button type="button" style="margin-left: 168px !important;" class="btn btn-success">Delete event</button></a>
	</div>

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
			<br>
			<label >Attachments: </label>
			<a href="" target="_blank" id="reply_atta" rel="noopener noreferrer">View</a>
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
</center>
	 
<style>

   #calendar {
    max-width: 900px;
    margin: 0 auto;
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
// show members 


$("#invite_email").email_multiple({

      data: null

  });

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
var reply_user_id =<?php echo $user_id ?>;
var calendar;
var event_url = 'calendar-event-load.php?user_id=<?php echo $_GET["user_id"] ?>&caledar_id=<?php echo serialize($_GET["calendar"]) ?>';
  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
     calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: [ 'interaction', 'dayGrid', 'timeGrid' ],
      header: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      defaultDate: new Date(),
      navLinks: true, // can click day/week names to navigate views
      selectable: true,
      selectMirror: true,
	  selectHelper:true,
	  editable: true,
      eventLimit: true, // allow "more" link when too many events
	  events:event_url,
		select: function(arg) {
			var in_emails = null;
			$("#dialog").dialog({
                autoOpen: false,
				autoResize: true,
				width: 400,
			});
			 $('#dialog').on('dialogclose', function(event) {
			     var in_emails = null;
			 });
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
				data:{title:title, start:re_start, end:re_end, id:id,location:location,description:description},
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
				data:{title:title, start:re_start, end:re_end, id:id,location:location,description:description},
				success:function()
				{
					alert("Event Updated");
				}
			});
		},
		eventClick:function(info){
			console.log(info);
			if (info.event.extendedProps.cale_user_id != reply_user_id) {
				$("#reply_dialog").dialog({
					autoOpen: false,
					autoResize: true,
				});
				$('#reply_title').val(info.event.title);
				$('#reply_event_id').val(info.event.id);
				
				$('#reply_event_user_id').val(info.event.extendedProps.user_id);
				$('#reply_location').val(info.event.extendedProps.location);
				$('#reply_atta').attr('href','images/event_images/'+info.event.extendedProps.attachments);
				$('#reply_description').val(info.event.extendedProps.description);
				$('#reply_dialog').dialog('open').effect( "highlight", "slow" );
				return false;
			}
			$("#edit_dialog").dialog({
                autoOpen: false,
				autoResize: true,
			});
			$('#edit_dialog').on('dialogclose', function(event) {
			     var in_emails = null;
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
			var re_start =  moment(info.event.start).format('YYYY-MM-DD HH:mm:ss');;
			var re_end =  moment(info.event.end).format('YYYY-MM-DD HH:mm:ss');;
			var invite_email = info.event.extendedProps.invite_email;
			var in_emails = null;
			if(invite_email != '' && invite_email != null){
				var in_emails = info.event.extendedProps.invite_email.split(",");
			}
			$('#title').val(info.event.title);
			$('#edit_event_id').val(info.event.id);
			$('#edit_start').val(re_start);
			$('#edit_end').val(re_end);
			$('#edit_location').val(info.event.extendedProps.location);
			$('#description').val(info.event.extendedProps.description);
			//$('#edit_invite_email').val(info.event.extendedProps.invite_email);
			//$('#edit_attachment').val(info.event.extendedProps.attachments);
			//$('#edit_atta').attr('href','images/event_images/'+info.event.extendedProps.attachments);
			$('#edit_delete_url').attr('href','calendar-event-update.php?delete_id='+info.event.id);
			$('#edit_dialog').dialog('open').effect( "highlight", "slow" );
			
			$("#edit_invite_email").email_multiple({

      				data: in_emails

  				});

		},

    });

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

$(document).ready(function() {
    $(".caledar_list input[type=checkbox]").click(function() {
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

		$('#edit_calendar_dialog').dialog('open').effect( "highlight", "slow" );
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
});

$(document).on("click",".remove_btn",function() {
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
