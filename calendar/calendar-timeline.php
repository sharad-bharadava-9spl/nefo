<?php
	// Begin code by jadeep
	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings(); 
	
	pageStart('Time line', NULL, null, "pmembership", NULL, $lang['index-membersC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
<center>
  <div id='calendar'></div>
  <div id="dialog" title="Add ideal time" style="display:none">
		<form method="post" action="calendar-event-ideal-add.php" enctype="multipart/form-data" >
			<label >Title : </label>
      <input type="text" name="title" required placeholder="Enter title" class="text calendar-input ui-widget-content ui-corner-all"><br>
      <br>
      <label >Start : </label>
      <input type="datetime" name="start" id="start">
      <br>
      <br>
      <label >End : </label>
			<input type="datetime" name="end" id="end">
			<input type="hidden" name="user_id" id="add_user_id">
			<br>
			<br>
			<button type="submit" class="btn btn-success">Save</button>
		</form>
	</div>
</center>
	 
<!-- Calendar code BEGIN -->
<style>

  body {
    margin: 40px 10px;
    padding: 0;
    font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
    font-size: 14px;
  }

  #calendar {
    max-width: 100%;
    margin: 0 auto;
  }

</style>

<script>

document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');

  var calendar = new FullCalendar.Calendar(calendarEl, {
    // schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
    plugins: [ 'interaction', 'resourceTimeline' ],
    header: {
      left: 'today prev,next',
      center: 'title',
      right: 'resourceTimelineDay,resourceTimelineTenDay,resourceTimelineMonth,resourceTimelineYear'
    },
    defaultView: 'resourceTimelineDay',
    scrollTime: '08:00',
    aspectRatio: 1.5,
    views: {
      resourceTimelineDay: {
        buttonText: ':15 slots',
        slotDuration: '00:15'
      },
      resourceTimelineTenDay: {
        type: 'resourceTimeline',
        duration: { days: 10 },
        buttonText: '10 days'
      }
    },
    selectable: true,
    selectMirror: true,
    selectHelper:true,
    editable: true,
    eventLimit: 2,
    resourceLabelText: 'Members',
    resources: 'calendar-event-timeline-members.php',
    events: 'calendar-event-timeline-data.php?single-day&for-resource-timeline',
    select: function(arg) {
			$("#dialog").dialog({
                autoOpen: false,
				autoResize: true,
			});
			var start = moment(arg.startStr).format('YYYY-MM-DD HH:mm:ss');
			var end = moment(arg.endStr).format('YYYY-MM-DD HH:mm:ss');
			$("#start").val(start);
			$("#end").val(end);
			$("#add_user_id").val(arg.resource.id);
			$('#dialog').dialog('open').effect( "highlight", "slow" );
			calendar.unselect()
	},

	eventResize:function(info, delta, revertFunc){
			var re_start =  moment(info.event.start).format('YYYY-MM-DD HH:mm:ss');;
			var re_end =  moment(info.event.end).format('YYYY-MM-DD HH:mm:ss');;
			var id = info.event.id;
			
			$.ajax({
				url:"calendar-event-ideal-resize.php",
				type:"POST",
				data:{start:re_start, end:re_end, id:id},
					success:function(){
						calendar.refetchEvents();
						alert('Time Update succefully!!');
					}
			})
	},

	eventDrop:function(info){
		return false;		
	},
  });
  calendar.render();
});

</script>
<!-- Calendar code END -->
<!-- End code by sagar -->
<?php  displayFooter(); ?>
